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
     * Fetch all categories with their attached post names
     * 
     * @return array
     */
    public function fetchAllWithPosts()
    {
        // Shared columns to be selected
        $columns = array(
            PostMapper::getFullColumnName('id'),
            PostMapper::getFullColumnName('name') => 'post',
            self::getFullColumnName('name') => 'category'
        );

        return $this->db->select($columns)
                        ->from(PostMapper::getTableName())
                        ->innerJoin(self::getTableName())
                        ->on()
                        ->equals(PostMapper::getFullColumnName('category_id'), new RawSqlFragment(self::getFullColumnName('id')))
                        ->whereEquals(self::getFullColumnName('lang_id'), $this->getLangId())
                        ->queryAll();
    }

    /**
     * Fetches as a list
     * 
     * @return array
     */
    public function fetchList()
    {
        return $this->db->select(array('id', 'name'))
                        ->from(static::getTableName())
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
        return $this->findColumnByPk($id, 'name');
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
        // Columns to be selected
        $columns = array(
            self::getFullColumnName('name'),
            self::getFullColumnName('id'),
            self::getFullColumnName('lang_id'),
            self::getFullColumnName('web_page_id'),
            WebPageMapper::getFullColumnName('slug')
        );

        return $this->db->select($columns)
                        ->append(', ')
                        ->count(PostMapper::getFullColumnName('id'), 'post_count')
                        ->from(self::getTableName())
                        ->leftJoin(PostMapper::getTableName())
                        ->on()
                        ->equals(PostMapper::getFullColumnName('category_id'), new RawSqlFragment(self::getFullColumnName('id')))
                        ->leftJoin(WebPageMapper::getTableName())
                        ->on()
                        ->equals(WebPageMapper::getFullColumnName('id'), new RawSqlFragment(CategoryMapper::getFullColumnName('web_page_id')))
                        ->groupBy(self::getFullColumnName('id'))
                        ->orderBy(self::getFullColumnName('id'))
                        ->desc()
                        ->queryAll();
    }

    /**
     * Fetches category data by its associated id
     * 
     * @param string $id Category id
     * @return array
     */
    public function fetchById($id)
    {
        // Columns to be selected
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('lang_id'),
            self::getFullColumnName('web_page_id'),
            self::getFullColumnName('name'),
            self::getFullColumnName('title'),
            self::getFullColumnName('description'),
            self::getFullColumnName('seo'),
            self::getFullColumnName('keywords'),
            self::getFullColumnName('meta_description'),
            WebPageMapper::getFullColumnName('slug'),
        );

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        ->leftJoin(WebPageMapper::getTableName())
                        ->on()
                        ->equals(WebPageMapper::getFullColumnName('id'), new RawSqlFragment(self::getFullColumnName('web_page_id')))
                        ->whereEquals(self::getFullColumnName('id'), $id)
                        ->query();
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
