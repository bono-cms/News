<?php

/**
 * This file is part of the Bono CMS
 * 
 * Copyright (c) No Global State Lab
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

namespace News\Service;

/**
 * API for PostManager
 */
interface PostManagerInterface
{
    /**
     * Find sequential (i.e previous and next) posts between provided ID
     * 
     * @param string $id Post ID
     * @return array
     */
    public function findSequential($id);

    /**
     * Returns a collection of switching URLs
     * 
     * @param string $id Post ID
     * @return array
     */
    public function getSwitchUrls($id);

    /**
     * Returns post breadcrumb collection
     * 
     * @param \News\Service\PostEntity $post
     * @return array
     */
    public function getBreadcrumbs(PostEntity $post);

    /**
     * Increments view count by post id
     * 
     * @param string $id
     * @return boolean
     */
    public function incrementViewCount($id);

    /**
     * Update settings
     * 
     * @param array $settings
     * @return boolean
     */
    public function updateSettings(array $settings);

    /**
     * Delete posts by their associated ids
     * 
     * @param array $ids
     * @return boolean
     */
    public function deleteByIds(array $ids);

    /**
     * Returns prepared paginator's instance
     * 
     * @return \Krystal\Paginate\Paginator
     */
    public function getPaginator();

    /**
     * Returns an id of latest post
     * 
     * @return integer
     */
    public function getLastId();

    /**
     * Adds a post
     * 
     * @param array $form Form data
     * @return boolean Depending on success
     */
    public function add(array $form);

    /**
     * Updates a post
     * 
     * @param array $form Form data
     * @return boolean Depending on success
     */
    public function update(array $form);

    /**
     * Deletes a post by its associated id
     * 
     * @param string $id
     * @return boolean
     */
    public function deleteById($id);

    /**
     * Fetches post entity by its associated id
     * 
     * @param string $id Post ID
     * @param boolean $withAttached Whether to grab attached entities
     * @param boolean $withTranslations Whether to include translations as well
     * @return \News\Service\PostEntity|boolean|array
     */
    public function fetchById($id, $withAttached, $withTranslations);

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
    public function fetchMostlyViewed($limit, $categoryId = null, $rand = false, $front = false, $views = 50);

    /**
     * Fetches all posts filtered by pagination
     * 
     * @param string $categoryId Filtering category ID
     * @param boolean $published Whether to filter by 'publihsed' attribute
     * @param boolean $front Whether to filter by 'front' attribute
     * @param integer $page Current page number
     * @param integer $itemsPerPage Items per page count
     * @return array
     */
    public function fetchAllByPage($categoryId, $published, $front, $page, $itemsPerPage);

    /**
     * Fetch recent news post entities
     * 
     * @param string $categoryId Optional category ID filter
     * @param integer $limit Limit of rows to be returned
     * @return array
     */
    public function fetchRecent($limit, $categoryId = null);

    /**
     * Fetches all published posts associated with category id
     * 
     * @param string $categoryId
     * @param integer $limit Amount of returned posts
     * @return array
     */
    public function fetchAllPublishedByCategoryId($categoryId, $limit);

    /**
     * Fetches random published posts
     * 
     * @param integer $amount
     * @param string $categoryId Optionally can be filtered by category id
     * @return array
     */
    public function fetchRandomPublished($amount, $categoryId = null);

    /**
     * Fetches all posts
     * 
     * @return array
     */
    public function fetchAll();
}
