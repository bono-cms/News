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
        return self::getWithPrefix('bono_module_news_categories_translations');
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
            self::getFullColumnName('id'),
            self::getFullColumnName('web_page_id', self::getTranslationTable()),
            self::getFullColumnName('lang_id', self::getTranslationTable()),
            self::getFullColumnName('name', self::getTranslationTable()),
            self::getFullColumnName('seo'),
            WebPageMapper::getFullColumnName('slug'),
        );

        if ($all) {
            $columns = array_merge($columns, array(
                self::getFullColumnName('title', self::getTranslationTable()),
                self::getFullColumnName('description', self::getTranslationTable()),
                self::getFullColumnName('keywords', self::getTranslationTable()),
                self::getFullColumnName('meta_description', self::getTranslationTable()),
            ));
        }

        return $columns;
    }

    /**
     * Fetch all categories with their attached post names
     * 
     * @return array
     */
    public function fetchAllWithPosts()
    {
        return $this->db->select(array(
                            PostMapper::getFullColumnName('id'),
                            PostMapper::getFullColumnName('name', PostMapper::getTranslationTable()) => 'post',
                            self::getFullColumnName('name', self::getTranslationTable()) => 'category'
                        ))
                        ->from(PostMapper::getTableName())
                        // Category relation
                        ->innerJoin(self::getTableName())
                        ->on()
                        ->equals(
                            PostMapper::getFullColumnName('category_id'), 
                            new RawSqlFragment(self::getFullColumnName('id'))
                        )
                        // Post translation relation
                        ->innerJoin(PostMapper::getTranslationTable())
                        ->on()
                        ->equals(
                            PostMapper::getFullColumnName('id'), 
                            new RawSqlFragment(self::getFullColumnName('id', PostMapper::getTranslationTable()))
                        )
                        // Category translation relation
                        ->innerJoin(self::getTranslationTable())
                        ->on()
                        ->equals(
                            self::getFullColumnName('id'), 
                            new RawSqlFragment(self::getFullColumnName('id', self::getTranslationTable()))
                        )
                        // Filtering condition
                        ->whereEquals(self::getFullColumnName('lang_id', self::getTranslationTable()), $this->getLangId())
                        ->queryAll();
    }

    /**
     * Fetches as a list
     * 
     * @return array
     */
    public function fetchList()
    {
        $columns = array(
            self::getFullColumnName('id'), 
            self::getFullColumnName('name', self::getTranslationTable())
        );

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        ->innerJoin(self::getTranslationTable())
                        ->on()
                        ->equals(
                            self::getFullColumnName('id'),
                            new RawSqlFragment(self::getFullColumnName('id', self::getTranslationTable()))
                        )
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
                        ->count(PostMapper::getFullColumnName('id'), 'post_count')
                        ->from(self::getTableName())

                        // Category relation
                        ->leftJoin(PostMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('id'), 
                            new RawSqlFragment(PostMapper::getFullColumnName('category_id'))
                        )
                        // Translation relation
                        ->innerJoin(self::getTranslationTable())
                        ->on()
                        ->equals(
                            self::getFullColumnName('id', self::getTranslationTable()),
                            new RawSqlFragment(self::getFullColumnName('id'))
                        )
                        ->rawAnd()
                        ->equals(
                            self::getFullColumnName('lang_id', self::getTranslationTable()),
                            $this->getLangId()
                        )
                        // Web page relation
                        ->innerJoin(WebPageMapper::getTableName())
                        ->on()
                        ->equals(
                            WebPageMapper::getFullColumnName('id'),
                            new RawSqlFragment(self::getFullColumnName('web_page_id', self::getTranslationTable()))
                        )
                        ->rawAnd()
                        ->equals(
                            WebPageMapper::getFullColumnName('lang_id'),
                            new RawSqlFragment(self::getFullColumnName('lang_id', self::getTranslationTable()))
                        );

        if ($countOnlyPublished == true) {
            $db->whereEquals(PostMapper::getFullColumnName('published'), '1');
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

    /**
     * Inserts a category
     * 
     * @param array $input Raw input data
     * @return boolean
     */
    public function insert(array $input)
    {
        return $this->persist($this->getWithLang($input));
    }

    /**
     * Updates a category
     * 
     * @param array $input Raw input data
     * @return boolean
     */
    public function update(array $input)
    {
        return $this->persist($input);
    }
}
