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

interface SiteServiceInterface
{
    /**
     * Find sequential (i.e previous and next) posts between provided ID
     * 
     * @param string $id Post ID
     * @return array
     */
    public function getSequential($id);

    /**
     * Returns a collection of category entities
     * 
     * @return array
     */
    public function getCategories();

    /**
     * Returns all entities filtered by category id
     * 
     * @param string $id Category id
     * @param integer $limit Optional limit. If null, then the value is taken from configuration
     * @return array
     */
    public function getAllByCategoryId($id, $limit = null);

    /**
     * Returns recent post entities
     * 
     * @param integer $limit Limit of rows to be returned
     * @param string $categoryId Optional category ID filter
     * @return array
     */
    public function getRecent($limit, $categoryId = null);

    /**
     * Returns a collection of mostly viewed article entities
     * 
     * @param integer $limit Limit of records to be fetched
     * @param int $categoryId Optional category ID filter
     * @param int $views Minimal view count in order to be considered as mostly viewed
     * @param bool $rand Whether to order in random order
     * @return array
     */
    public function getMostlyViewed($limit, $categoryId = null, $rand = false, $views = 50);

    /**
     * Returns random posts
     * 
     * @param integer $amount
     * @param string $categoryId Optionally can be filtered by category id
     * @return array
     */
    public function getRandom($amount, $categoryId = null);
}
