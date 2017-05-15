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
     * Returns a collection of mostly viewed article entities
     * 
     * @param integer $limit
     * @return array
     */
    public function getMostlyViewed($limit);

    /**
     * Returns random posts
     * 
     * @param integer $amount
     * @param string $categoryId Optionally can be filtered by category id
     * @return array
     */
    public function getRandom($amount, $categoryId = null);
}
