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

use Krystal\Db\Sql\RawSqlFragment;
use Cms\Storage\MySQL\WebPageMapper;
use Cms\Storage\MySQL\AbstractMapper;
use News\Storage\PostMapperInterface;

final class PostMapper extends AbstractMapper implements PostMapperInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('bono_module_news_posts');
    }

    /**
     * Builds shared select query
     * 
     * @param boolean $published
     * @param string $categoryId Optionally can be filtered by category id
     * @param string $sort Column name to sort by
     * @return \Krystal\Db\Sql\Db
     */
    private function getSelectQuery($published, $categoryId = null, $sort = 'id')
    {
        $db = $this->db->select('*')
                       ->from(static::getTableName())
                       ->whereEquals('lang_id', $this->getLangId());

        if ($published === true) {
            $db->andWhereEquals('published', '1');
        }

        if ($categoryId !== null) {
            $db->andWhereEquals('category_id', $categoryId);
        }

        if ($sort == 'rand') {
            $db->orderBy()
               ->rand();

        } else {
            $db->orderBy($sort);
        }

        return $db;
    }

    /**
     * Removes all web pages by associated category id
     * 
     * @param string $categoryId
     * @return boolean
     */
    public function fetchAllWebPageIdsByCategoryId($categoryId)
    {
        return $this->fetchColumns('web_page_id', 'category_id', $categoryId);
    }

    /**
     * Fetches all post ids associated with provided category id
     * 
     * @param string $categoryId
     * @return array
     */
    public function fetchAllIdsWithImagesByCategoryId($categoryId)
    {
        return $this->db->select('id')
                        ->from(static::getTableName())
                        ->whereEquals('category_id', $categoryId)
                        ->andWhereNotEquals('cover', '')
                        ->queryAll('id');
    }

    /**
     * Increments view count by post id
     * 
     * @param string $id
     * @return boolean
     */
    public function incrementViewCount($id)
    {
        return $this->incrementColumnByPk($id, 'views');
    }

    /**
     * Update post's published state by its associated id
     * 
     * @param string $id Post id
     * @param string $published Either 0 or 1
     * @return boolean
     */
    public function updatePublishedById($id, $published)
    {
        return $this->updateColumnByPk($id, 'published', $published);
    }

    /**
     * Updates whether post's SEO is enabled or not by its associated id
     * 
     * @param string $id Post id
     * @param string $published Either 0 or 1
     * @return boolean
     */
    public function updateSeoById($id, $seo)
    {
        return $this->updateColumnByPk($id, 'seo', $seo);
    }

    /**
     * Adds a post
     * 
     * @param array $input Raw input data
     * @return boolean
     */
    public function insert(array $input)
    {
        return $this->persist($this->getWithLang($input));
    }

    /**
     * Updates a post
     * 
     * @param array $input Raw input data
     * @return boolean
     */
    public function update(array $input)
    {
        return $this->persist($input);
    }

    /**
     * Deletes a post by its associated id
     * 
     * @param string $id Post id
     * @return boolean
     */
    public function deleteById($id)
    {
        return $this->deleteByPk($id);
    }

    /**
     * Deletes all posts associated with given category id
     * 
     * @param string $categoryId
     * @return boolean
     */
    public function deleteAllByCategoryId($categoryId)
    {
        return $this->deleteByColumn('category_id', $categoryId);
    }

    /**
     * Fetches post data by its associated id
     * 
     * @param string $id
     * @return array
     */
    public function fetchById($id)
    {
        // Columns to be selected
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('lang_id'),
            self::getFullColumnName('web_page_id'),
            self::getFullColumnName('category_id'),
            self::getFullColumnName('published'),
            self::getFullColumnName('seo'),
            self::getFullColumnName('name'),
            self::getFullColumnName('title'),
            self::getFullColumnName('intro'),
            self::getFullColumnName('full'),
            self::getFullColumnName('timestamp'),
            self::getFullColumnName('keywords'),
            self::getFullColumnName('meta_description'),
            self::getFullColumnName('cover'),
            self::getFullColumnName('views'),
            CategoryMapper::getFullColumnName('name') => 'category_name',
            WebPageMapper::getFullColumnName('slug')
        );

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        ->innerJoin(CategoryMapper::getTableName())
                        ->on()
                        ->equals(CategoryMapper::getFullColumnName('id'), new RawSqlFragment(self::getFullColumnName('category_id')))
                        ->leftJoin(WebPageMapper::getTableName())
                        ->on()
                        ->equals(self::getFullColumnName('web_page_id'), new RawSqlFragment(WebPageMapper::getFullColumnName('id')))
                        ->whereEquals(self::getFullColumnName('id'), $id)
                        ->query();
    }

    /**
     * Fetches posts ordering by view count
     * 
     * @param integer $limit Limit of records to be fetched
     * @return array
     */
    public function fetchMostlyViewed($limit)
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->whereEquals('lang_id', $this->getLangId())
                        ->andWhereEquals('published', '1')
                        ->orderBy('views')
                        ->desc()
                        ->limit($limit)
                        ->queryAll();
    }

    /**
     * Fetches all posts
     * 
     * @return array
     */
    public function fetchAll()
    {
        return $this->findAllByColumn('lang_id', $this->getLangId());
    }

    /**
     * Fetches random published posts
     * 
     * @param integer $amount
     * @param string $categoryId Optionally can be filtered by category id
     * @return array
     */
    public function fetchRandomPublished($amount, $categoryId = null)
    {
        return $this->getSelectQuery(true, $categoryId, 'rand')
                    ->limit($amount)
                    ->queryAll();
    }

    /**
     * Fetches all posts filtered by pagination
     * 
     * @param string $categoryId Category ID
     * @param boolean $published Whether to fetch only published records
     * @param integer $page Current page
     * @param integer $itemsPerPage Per page count
     * @return array
     */
    public function fetchAllByPage($categoryId, $published, $page, $itemsPerPage)
    {
        // Columns to be selected
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('web_page_id'),
            self::getFullColumnName('lang_id'),
            self::getFullColumnName('name'),
            self::getFullColumnName('timestamp'),
            self::getFullColumnName('published'),
            self::getFullColumnName('seo'),
            WebPageMapper::getFullColumnName('slug'),
            CategoryMapper::getFullColumnName('name') => 'category_name',
        );

        $db = $this->db->select($columns)
                       ->from(self::getTableName())
                       ->innerJoin(CategoryMapper::getTableName())
                       ->on()
                       ->equals(CategoryMapper::getFullColumnName('id'), new RawSqlFragment(self::getFullColumnName('category_id')))
                       ->leftJoin(WebPageMapper::getTableName())
                       ->on()
                       ->equals(WebPageMapper::getFullColumnName('id'), new RawSqlFragment(self::getFullColumnName('web_page_id')))
                       ->whereEquals(self::getFullColumnName('lang_id'), $this->getLangId());

        // Append category ID if provided
        if ($categoryId !== null) {
            $db->andWhereEquals(self::getFullColumnName('category_id'), $categoryId);
        }

        // If needed to fetch by published, then sort by time
        if ($published) {
            $db->andWhereEquals(self::getFullColumnName('published'), '1')
               ->orderBy(array(
                    self::getFullColumnName('timestamp') => 'DESC', 
                    self::getFullColumnName('id') => 'DESC'
                ));
        } else {
            $db->orderBy(self::getFullColumnName('id'))
               ->desc();
        }

        // If page number and per page count provided, apply pagination
        if ($page !== null && $itemsPerPage !== null) {
            $db->paginate($page, $itemsPerPage);
        }

        // If only per page count provided, apply limit only
        if ($page === null && $itemsPerPage !== null) {
            $db->limit($itemsPerPage);
        }

        return $db->queryAll();
    }

    /**
     * Fetches post name by its associated id
     * 
     * @param string $id Post id
     * @return string
     */
    public function fetchNameById($id)
    {
        return $this->findColumnByPk($id, 'name');
    }
}
