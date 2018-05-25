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
use Krystal\Db\Sql\QueryBuilder;
use Krystal\Db\Filter\InputDecorator;
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
    public static function getTranslationTable()
    {
        return PostTranslationMapper::getTableName();
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
            self::column('id'),
            self::column('category_id'),
            self::column('timestamp'),
            self::column('published'),
            self::column('cover'),
            self::column('seo'),
            self::column('front'),
            PostTranslationMapper::column('web_page_id'),
            PostTranslationMapper::column('lang_id'),
            PostTranslationMapper::column('name'),
            PostTranslationMapper::column('intro'),
            WebPageMapper::column('slug'),
            CategoryTranslationMapper::column('name') => 'category_name'
        );

        if ($all) {
            $columns = array_merge($columns, array(
                PostTranslationMapper::column('title'),
                PostTranslationMapper::column('full'),
                PostTranslationMapper::column('keywords'),
                PostTranslationMapper::column('meta_description'),
                self::column('views')
            ));
        }

        return $columns;
    }

    /**
     * Fetches all posts filtered by pagination
     * 
     * @param array $filter
     * @param integer $page Current page
     * @param integer $itemsPerPage Per page count
     * @param \Closure $orderCallback Callback to generate ORDER BY condition
     * @return array
     */
    private function findRecords($filter, $page, $itemsPerPage, Closure $orderCallback)
    {
        if (!($filter instanceof InputDecorator)) {
            $filter = new InputDecorator($filter);
        }

        $db = $this->db->select($this->getSharedColumns(false))
                       ->from(self::getTableName())
                       // Translation relation
                       ->innerJoin(PostTranslationMapper::getTableName())
                       ->on()
                       ->equals(
                            PostTranslationMapper::column('id'),
                            new RawSqlFragment(self::column('id'))
                        )
                        // Category translation
                        ->innerJoin(CategoryTranslationMapper::getTableName())
                        ->on()
                        ->equals(
                            self::column('category_id'),
                            new RawSqlFragment(CategoryTranslationMapper::column('id'))
                        )
                        ->rawAnd()
                        ->equals(
                            CategoryTranslationMapper::column('lang_id'),
                            new RawSqlFragment(PostTranslationMapper::column('lang_id'))
                        )
                        // Category relation
                        ->innerJoin(CategoryMapper::getTableName())
                        ->on()
                        ->equals(
                            CategoryMapper::column('id'),
                            new RawSqlFragment(CategoryTranslationMapper::column('id'))
                        )
                        // Web page relation
                        ->innerJoin(WebPageMapper::getTableName())
                        ->on()
                        ->equals(
                            WebPageMapper::column('id'),
                            new RawSqlFragment(PostTranslationMapper::column('web_page_id'))
                        )
                        // Filtering condition
                        ->whereEquals(
                            PostTranslationMapper::column('lang_id'), 
                            $this->getLangId()
                        );

        // Filter by attributes on demand
        $db->andWhereEquals(self::column('category_id'), $filter['categoryname'], true)
           ->andWhereEquals(self::column('published'), $filter['published'], true)
           ->andWhereEquals(self::column('front'), $filter['front'], true)
           ->andWhereEquals(self::column('seo'), $filter['seo'], true)
           ->andWhereEquals(PostTranslationMapper::column('name'), $filter['name'], true);

        // Apply order callback
        $orderCallback($db);

        // If page number and per page count provided, apply pagination
        if ($page != null && $itemsPerPage != null) {
            $db->paginate($page, $itemsPerPage);
        }

        // If only per page count provided, apply limit only
        if ($page == null && $itemsPerPage != null) {
            $db->limit($itemsPerPage);
        }

        return $db->queryAll();
    }

    /**
     * {@inheritDoc}
     */
    public function filter($input, $page, $itemsPerPage, $sortingColumn, $desc)
    {
        if (!$sortingColumn) {
            $sortingColumn = 'id';
        }

        // Support columns
        $columns = array(
            'id' => self::column('id'),
            'name' => PostTranslationMapper::column('name'),
            'categoryname' => self::column('category_id'),
            'published' => self::column('published'),
            'seo' => self::column('seo'),
            'front' => self::column('front')
        );

        // Check if valid column provided
        if (isset($columns[$sortingColumn])) {
            $sortingColumn = $columns[$sortingColumn];
        }

        return $this->findRecords($input, $page, $itemsPerPage, function($db) use ($desc, $sortingColumn){
            $db->orderBy($sortingColumn);

            if ($desc == true) {
                $db->desc();
            }
        });
    }

    /**
     * Find sequential (i.e previous and next) posts between provided ID
     * 
     * @param string $id Post ID
     * @return array
     */
    public function findSequential($id)
    {
        // Safe casting
        $id = intval($id);

        // Reference to PK column
        $pk = self::column('id');

        // Columns to be selected
        $columns = array(
            self::column('id'),
            PostTranslationMapper::column('lang_id'),
            PostTranslationMapper::column('name'),
            WebPageMapper::column('slug')
        );

        // Inner comparison generator
        $inner = function($func, $operator) use ($id, $pk){
            $qb = new QueryBuilder();
            $qb->select()
               ->func($func, $pk)
               ->from(self::getTableName())
               ->where($pk, $operator, $id);

            return sprintf('IFNULL((%s), 0)', $qb->getQueryString());
        };

        // Build query
        $db = $this->db->select($columns)
                       ->from(PostTranslationMapper::getTableName())
                       // Translation relation
                       ->innerJoin(self::getTableName())
                       ->on()
                       ->equals(PostTranslationMapper::column('id'), self::getRawColumn('id'))
                       // Web page relation
                       ->innerJoin(WebPageMapper::getTableName())
                       ->on()
                       ->equals(WebPageMapper::column('id'), PostTranslationMapper::getRawColumn('web_page_id'))
                       ->rawAnd()
                       ->equals(WebPageMapper::column('lang_id'), PostTranslationMapper::getRawColumn('lang_id'))
                       // Language ID constraint
                       ->whereEquals(PostTranslationMapper::column('lang_id'), $this->getLangId())
                       ->rawAnd()
                       // Start
                       ->openBracket()
                       ->equals($pk, new RawSqlFragment($inner('min', '>')))
                       ->rawOr()
                       ->equals($pk, new RawSqlFragment($inner('max', '<')))
                       // End
                       ->closeBracket();

        return $db->queryAll();
    }

    /**
     * Find a collection of post IDs attached to category ID
     * 
     * @param string $categoryId
     * @return array
     */
    public function findPostIdsByCategoryId($categoryId)
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
        return $this->updateColumns($settings, array('seo', 'published', 'front'));
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
     * @param boolean $withTranslations Whether to include translations as well
     * @return array
     */
    public function fetchByIds(array $ids, $relational = false, $withTranslations = false)
    {
        $db = $this->createWebPageSelect($this->getSharedColumns(true))
                    // Category relation
                    ->innerJoin(CategoryMapper::getTableName())
                    ->on()
                    ->equals(
                        CategoryMapper::column('id'), 
                        new RawSqlFragment(self::column('category_id'))
                    )
                   // Category translating relation
                   ->innerJoin(CategoryTranslationMapper::getTableName())
                   ->on()
                   ->equals(
                        CategoryTranslationMapper::column('id'), 
                        new RawSqlFragment(self::column('category_id'))
                    );

        if ($withTranslations === false) {
            $db->rawAnd()
               ->equals(
                  CategoryTranslationMapper::column('lang_id'), 
                  new RawSqlFragment(PostTranslationMapper::column('lang_id'))
                );
        }

        $db->whereIn(self::column('id'), $ids);

        if ($withTranslations === false) {
			$db->andWhereEquals(
				PostTranslationMapper::column('lang_id'), 
				$this->getLangId()
			);
		}

        if ($relational === true) {
            $db->asManyToMany(self::PARAM_COLUMN_ATTACHED, self::getJunctionTableName(), self::PARAM_JUNCTION_MASTER_COLUMN, self::getTableName(), 'id', 'id');
        }

        return $db->queryAll();
    }

    /**
     * Fetches post data by its associated id
     * 
     * @param string $id
     * @param boolean $withTranslations Whether to include translations as well
     * @return array
     */
    public function fetchById($id, $withTranslations = false)
    {
        $row = $this->fetchByIds(array($id), true, $withTranslations);

        if ($withTranslations == true) {
            return $row;
        }

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
        return $this->findRecords(array('published' => true), null, $limit, function($db){
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
        $filter = array(
            'categoryname' => $categoryId, 
            'published' => true
        );

        return $this->findRecords($filter, null, $limit, function($db){
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
     * @param boolean $front Whether to fetch only front records
     * @param integer $page Current page
     * @param integer $itemsPerPage Per page count
     * @return array
     */
    public function fetchAllByPage($categoryId, $published, $front, $page, $itemsPerPage)
    {
        // Configure sorting way depending on published state
        if ($published) {
            $sortings = array(
                self::column('timestamp') => 'DESC', 
                self::column('id') => 'DESC'
            );
        } else {
            $sortings = array(
                self::column('id') => 'DESC'
            );
        }

        // Purely for older PHP versions. Old versions don't let static methods to be called in Closures
        $timestampColumn = self::column('timestamp');

        $filter = array(
            'categoryname' => $categoryId,
            'published' => $published,
            'front' => $front
        );

        return $this->findRecords($filter, $page, $itemsPerPage, function($db) use ($sortings, $filter, $timestampColumn){
            // Don't let future posts to be returned
            if ($filter['published'] == true) {
                // Avoid returning future posts
                $db->andWhere($timestampColumn, '<=', time());
            }

            $db->orderBy($sortings);
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
