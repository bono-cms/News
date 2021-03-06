<?php

/**
 * This file is part of the Bono CMS
 * 
 * Copyright (c) No Global State Lab
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

namespace News\Storage;

interface PostMapperInterface
{
    const PARAM_COLUMN_ATTACHED = 'attached';

    /**
     * Find sequential (i.e previous and next) posts between provided ID
     * 
     * @param string $id Post ID
     * @return array
     */
    public function findSequential($id);

    /**
     * Find a collection of post IDs attached to category ID
     * 
     * @param string $categoryId
     * @return array
     */
    public function findPostIdsByCategoryId($categoryId);

    /**
     * Removes all web pages by associated category id
     * 
     * @param string $categoryId
     * @return boolean
     */
    public function fetchAllWebPageIdsByCategoryId($categoryId);

    /**
     * Fetches all post ids associated with provided category id
     * 
     * @param string $categoryId
     * @return boolean
     */
    public function fetchAllIdsWithImagesByCategoryId($categoryId);

    /**
     * Increments view count by post id
     * 
     * @param string $id
     * @return boolean
     */
    public function incrementViewCount($id);

    /**
     * Attach relational IDs if provided
     * 
     * @param int $id Current post ID
     * @param array $attachedIds Attached post IDs to current one
     * @return boolean
     */
    public function insertAttached($id, array $attachedIds);

    /**
     * Updates attached relation
     * 
     * @param int $id Current post ID
     * @param array $attachedIds Raw input data
     * @return boolean
     */
    public function updateAttached($id, array $attachedIds);

    /**
     * Deletes a post by its associated id
     * 
     * @param string $id Post id
     * @return boolean
     */
    public function deleteById($id);

    /**
     * Deletes all posts associated with given category id
     * 
     * @param string $categoryId
     * @return boolean
     */
    public function deleteAllByCategoryId($categoryId);

    /**
     * Fetches posts ordering by view count
     * 
     * @param integer $limit Limit of records to be fetched
     * @param int $categoryId Optional category ID filter
     * @param bool $rand Whether to order in random order
     * @param bool $front Whether to fetch only front ones
     * @param int $views Minimal view count in order to be considered as mostly viewed
     * @return array
     */
    public function fetchMostlyViewed($limit, $categoryId, $rand, $front, $views);

    /**
     * Fetches all posts
     * 
     * @return array
     */
    public function fetchAll();

    /**
     * Fetches random published posts
     * 
     * @param integer $amount
     * @param string $categoryId Optionally can be filtered by category id
     * @return array
     */
    public function fetchRandomPublished($amount, $categoryId = null);

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
    public function fetchAllByPage($categoryId, $published, $front, $page, $itemsPerPage);

    /**
     * Fetch recent news post
     * 
     * @param string $categoryId Optional category ID filter
     * @param integer $limit Limit of rows to be returned
     * @return array
     */
    public function fetchRecent($limit, $categoryId = null);

    /**
     * Fetches post name by its associated id
     * 
     * @param string $id Post id
     * @return string
     */
    public function fetchNameById($id);

    /**
     * Fetches post data by associated IDs
     * 
     * @param array $ids A collection of post IDs
     * @param boolean $relational Whether to include relational data
     * @param boolean $withTranslations Whether to include translations as well
     * @return array
     */
    public function fetchByIds(array $ids, $relational = false, $withTranslations = false);

    /**
     * Fetches post data by its associated id
     * 
     * @param string $id
     * @param boolean $withTranslations Whether to include translations as well
     * @return array
     */
    public function fetchById($id, $withTranslations = false);
}
