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
use Krystal\Stdlib\ArrayUtils;
use Cms\Storage\MySQL\WebPageMapper;
use Cms\Storage\MySQL\AbstractMapper;
use News\Storage\PostMapperInterface;
use Closure;

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
     * {@inheritDoc}
     */
    public static function getJunctionTableName()
    {
        return self::getWithPrefix('bono_module_news_posts_attached');
    }

    /**
     * Returns a collection of shared columns to be selected
     * 
     * @param array $all Whether to return all columns or not
     * @return array
     */
    private function getSharedColumns($all)
    {
        // Columns to be selected
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('web_page_id'),
            self::getFullColumnName('lang_id'),
            self::getFullColumnName('name'),
            self::getFullColumnName('timestamp'),
            self::getFullColumnName('published'),
            self::getFullColumnName('intro'),
            self::getFullColumnName('cover'),
            self::getFullColumnName('seo'),
            WebPageMapper::getFullColumnName('slug'),
            CategoryMapper::getFullColumnName('name') => 'category_name'
        );

        if ($all) {
            $columns = array_merge($columns, array(
                self::getFullColumnName('category_id'),
                self::getFullColumnName('name'),
                self::getFullColumnName('title'),
                self::getFullColumnName('full'),
                self::getFullColumnName('keywords'),
                self::getFullColumnName('meta_description'),
                self::getFullColumnName('views'),
                CategoryMapper::getFullColumnName('name') => 'category_name',
                WebPageMapper::getFullColumnName('slug')
            ));
        }

        return $columns;
    }

    /**
     * Fetches all posts filtered by pagination
     * 
     * @param string $categoryId Category ID
     * @param boolean $published Whether to fetch only published records
     * @param integer $page Current page
     * @param integer $itemsPerPage Per page count
     * @param \Closure $orderCallback Callback to generate ORDER BY condition
     * @return array
     */
    private function findRecords($categoryId, $published, $page, $itemsPerPage, Closure $orderCallback)
    {
        $db = $this->db->select($this->getSharedColumns(false))
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

        if ($published) {
            $db->andWhereEquals(self::getFullColumnName('published'), '1');
        }

        // Apply order callback
        $orderCallback($db);

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
     * Find a collection of post IDs attached to category ID
     * 
     * @param string $categoryId
     * @return array
     */
    private function findPostIdsByCategoryId($categoryId)
    {
        return $this->db->select($this->getPk())
                        ->from(self::getTableName())
                        ->whereEquals('category_id', $categoryId)
                        ->queryAll($this->getPk());
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
                        ->from(self::getTableName())
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
     * Update settings
     * 
     * @param array $settings
     * @return boolean
     */
    public function updateSettings(array $settings)
    {
        return $this->updateColumns($settings, array('seo', 'published'));
    }

    /**
     * Adds a post
     * 
     * @param array $input Raw input data
     * @return boolean
     */
    public function insert(array $input)
    {
        $this->persist($this->getWithLang(ArrayUtils::arrayWithout($input, array(self::PARAM_COLUMN_ATTACHED))));
        $id = $this->getLastId();

        // Insert relational posts if provided
        if (isset($input[self::PARAM_COLUMN_ATTACHED])) {
            $this->insertIntoJunction(self::getJunctionTableName(), $id, $input[self::PARAM_COLUMN_ATTACHED]);
        }

        return true;
    }

    /**
     * Updates a post
     * 
     * @param array $input Raw input data
     * @return boolean
     */
    public function update(array $input)
    {
        // Synchronize relations if provided
        if (isset($input[self::PARAM_COLUMN_ATTACHED])) {
            $this->syncWithJunction(self::getJunctionTableName(), $input[$this->getPk()], $input[self::PARAM_COLUMN_ATTACHED]);
        } else {
            $this->removeFromJunction(self::getJunctionTableName(), $input[$this->getPk()]);
        }

        return $this->persist(ArrayUtils::arrayWithout($input, array(self::PARAM_COLUMN_ATTACHED)));
    }

    /**
     * Deletes a post by its associated id
     * 
     * @param string $id Post id
     * @return boolean
     */
    public function deleteById($id)
    {
        $this->removeFromJunction(self::getJunctionTableName(), $id);
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
        $this->removeFromJunction(self::getJunctionTableName(), $this->findPostIdsByCategoryId($categoryId));
        return $this->deleteByColumn('category_id', $categoryId);
    }

    /**
     * Fetches post data by associated IDs
     * 
     * @param array $ids A collection of post IDs
     * @param boolean $relational Whether to include relational data
     * @return array
     */
    public function fetchByIds(array $ids, $relational = false)
    {
        $db = $this->db->select($this->getSharedColumns(true))
                        ->from(self::getTableName())
                        ->innerJoin(CategoryMapper::getTableName())
                        ->on()
                        ->equals(CategoryMapper::getFullColumnName('id'), new RawSqlFragment(self::getFullColumnName('category_id')))
                        ->leftJoin(WebPageMapper::getTableName())
                        ->on()
                        ->equals(self::getFullColumnName('web_page_id'), new RawSqlFragment(WebPageMapper::getFullColumnName('id')))
                        ->whereIn(self::getFullColumnName('id'), $ids);

        if ($relational === true) {
            $db->asManyToMany(self::PARAM_COLUMN_ATTACHED, self::getJunctionTableName(), self::PARAM_JUNCTION_MASTER_COLUMN, self::getTableName(), 'id', 'id');
        }

        return $db->queryAll();
    }

    /**
     * Fetches post data by its associated id
     * 
     * @param string $id
     * @return array
     */
    public function fetchById($id)
    {
        $row = $this->fetchByIds(array($id), true);

        if (isset($row[0])) {
            return $row[0];
        } else {
            return array();
        }
    }

    /**
     * Fetches posts ordering by view count
     * 
     * @param integer $limit Limit of records to be fetched
     * @return array
     */
    public function fetchMostlyViewed($limit)
    {
        return $this->findRecords(null, true, null, $limit, function($db){
            $db->orderBy('views')
               ->desc();
        });
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
     * @param integer $limit
     * @param string $categoryId Optionally can be filtered by category id
     * @return array
     */
    public function fetchRandomPublished($limit, $categoryId = null)
    {
        return $this->findRecords($categoryId, true, null, $limit, function($db){
            $db->orderBy()
               ->rand();
        });
    }

    /**
     * Fetch recent news post
     * 
     * @param string $categoryId Optional category ID filter
     * @param integer $limit Limit of rows to be returned
     * @return array
     */
    public function fetchRecent($limit, $categoryId = null)
    {
        return $this->fetchAllByPage($categoryId, true, null, $limit);
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
        return $this->findRecords($categoryId, $published, $page, $itemsPerPage, function($db) use ($published){
            // If needed to fetch by published, then sort by time
            if ($published) {
                $db->orderBy(array(
                    self::getFullColumnName('timestamp') => 'DESC', 
                    self::getFullColumnName('id') => 'DESC'
                ));
            } else {
                $db->orderBy(self::getFullColumnName('id'))
                   ->desc();
            }
        });
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
