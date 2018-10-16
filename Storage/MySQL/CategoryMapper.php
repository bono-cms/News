<?php

/**
 * This file is part of the Bono CMS
 * 
 * Copyright (c) No Global State Lab
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

namespace News\Storage\MySQL;

use Cms\Storage\MySQL\WebPageMapper;
use Cms\Storage\MySQL\AbstractMapper;
use News\Storage\CategoryMapperInterface;
use Krystal\Db\Sql\RawSqlFragment;

final class CategoryMapper extends AbstractMapper implements CategoryMapperInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('bono_module_news_categories');
    }

    /**
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return CategoryTranslationMapper::getTableName();
    }

    /**
     * Returns a collection of shared columns to be selected
     * 
     * @param boolean $all Whether to select all columns or not
     * @return array
     */
    private function getSharedColumns($all)
    {
        // Basic columns to be selected
        $columns = array(
            self::column('id'),
            self::column('seo'),
            CategoryTranslationMapper::column('web_page_id'),
            CategoryTranslationMapper::column('lang_id'),
            CategoryTranslationMapper::column('name'),
            WebPageMapper::column('slug')
        );

        if ($all) {
            $columns = array_merge($columns, array(
                CategoryTranslationMapper::column('title'),
                CategoryTranslationMapper::column('description'),
                CategoryTranslationMapper::column('keywords'),
                CategoryTranslationMapper::column('meta_description'),
            ));
        }

        return $columns;
    }

    /**
     * Fetch all categories with their attached post IDs and names
     * 
     * @param int $excludedId Excluded post IDs
     * @return array
     */
    public function fetchAllWithPosts($excludedId = null)
    {
        $db = $this->db->select(array(
                            PostMapper::column('id'),
                            PostTranslationMapper::column('name') => 'post',
                            CategoryTranslationMapper::column('name') => 'category'
                        ))
                        ->from(PostMapper::getTableName())
                        // Category relation
                        ->innerJoin(self::getTableName(), array(
                            PostMapper::column('category_id') => self::getRawColumn('id')
                        ))
                        // Post translation relation
                        ->innerJoin(PostTranslationMapper::getTableName(), array(
                            PostMapper::column('id') => PostTranslationMapper::getRawColumn('id')
                        ))
                        // Category translation relation
                        ->innerJoin(CategoryTranslationMapper::getTableName(), array(
                            self::column('id') => CategoryTranslationMapper::getRawColumn('id')
                        ))
                        // Filtering condition
                        ->whereEquals(
                            CategoryTranslationMapper::column('lang_id'), 
                            $this->getLangId()
                        );

        // If excluded post ID provided explicitly, then append it on condition
        if ($excludedId !== null && is_numeric($excludedId)) {
            $db->andWhereNotEquals(PostMapper::column('id'), $excludedId);
        }

        return $db->queryAll();
    }

    /**
     * Fetches as a list
     * 
     * @return array
     */
    public function fetchList()
    {
        $columns = array(
            self::column('id'), 
            CategoryTranslationMapper::column('name')
        );

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Translation relation
                        ->innerJoin(CategoryTranslationMapper::getTableName(), array(
                            self::column('id') => CategoryTranslationMapper::getRawColumn('id')
                        ))
                        ->whereEquals('lang_id', $this->getLangId())
                        ->queryAll();
    }

    /**
     * Fetches category name by its associated id
     * 
     * @param string $id Category id
     * @return string
     */
    public function fetchNameById($id)
    {
    }

    /**
     * Deletes a category by its associated id
     * 
     * @param string $id Category id
     * @return boolean
     */
    public function deleteById($id)
    {
        return $this->deleteByPk($id);
    }

    /**
     * Fetch all categories
     * 
     * @return array
     */
    public function fetchAll()
    {
        $countOnlyPublished = false;
        $columns = $this->getSharedColumns(false);

        $db = $this->db->select($columns)
                        ->count(PostMapper::column('id'), 'post_count')
                        ->from(self::getTableName())

                        // Category relation
                        ->leftJoin(PostMapper::getTableName(), array(
                            self::column('id') => PostMapper::getRawColumn('category_id')
                        ))
                        // Translation relation
                        ->innerJoin(CategoryTranslationMapper::getTableName(), array(
                            CategoryTranslationMapper::column('id') => self::getRawColumn('id'),
                            CategoryTranslationMapper::column('lang_id') => $this->getLangId()
                        ))
                        // Web page relation
                        ->innerJoin(WebPageMapper::getTableName(), array(
                            WebPageMapper::column('id') => CategoryTranslationMapper::getRawColumn('web_page_id'),
                            WebPageMapper::column('lang_id') => CategoryTranslationMapper::getRawColumn('lang_id')
                        ));

        if ($countOnlyPublished == true) {
            $db->whereEquals(PostMapper::column('published'), '1');
        }

        // Aggregate grouping
        return $db->groupBy($columns)
                  ->queryAll();
    }

    /**
     * Fetches category data by its associated id
     * 
     * @param string $id Category id
     * @param boolean $withTranslations Whether to fetch translations or not
     * @return array
     */
    public function fetchById($id, $withTranslations)
    {
        return $this->findWebPage($this->getSharedColumns(true), $id, $withTranslations);
    }
}
