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

use Krystal\Stdlib\VirtualEntity;

final class SiteService implements SiteServiceInterface
{
    /**
     * Post service
     * 
     * @var \News\Service\PostManagerInterface
     */
    private $postManager;

    /**
     * Category service
     * 
     * @var \News\Service\CategoryManagerInterface
     */
    private $categoryManager;

    /**
     * Configuration entity
     * 
     * @var \Krystal\Stdlib\VirtualEntity
     */
    private $config;

    /**
     * State initialization
     * 
     * @param \News\Service\PostManagerInterface $postManager
     * @param \News\Service\CategoryManagerInterface $categoryManager
     * @param \Krystal\Stdlib\VirtualEntity $config
     * @return void
     */
    public function __construct(PostManagerInterface $postManager, CategoryManagerInterface $categoryManager, VirtualEntity $config)
    {
        $this->postManager = $postManager;
        $this->categoryManager = $categoryManager;
        $this->config = $config;
    }

    /**
     * Find sequential (i.e previous and next) posts between provided ID
     * 
     * @param string $id Post ID
     * @return array
     */
    public function getSequential($id)
    {
        return $this->postManager->findSequential($id);
    }

    /**
     * Returns a collection of category entities
     * 
     * @return array
     */
    public function getCategories()
    {
        return $this->categoryManager->fetchAll();
    }

    /**
     * Returns all entities filtered by category id
     * 
     * @param string $id Category id
     * @param integer $limit Optional limit. If null, then the value is taken from configuration
     * @return array
     */
    public function getAllByCategoryId($id, $limit = null)
    {
        if (is_null($limit)) {
            $limit = $this->config->getBlockPerPageCount();
        }

        return $this->postManager->fetchAllPublishedByCategoryId($id, $limit);
    }

    /**
     * Returns recent post entities
     * 
     * @param integer $limit Limit of rows to be returned
     * @param string $categoryId Optional category ID filter
     * @return array
     */
    public function getRecent($limit, $categoryId = null)
    {
        return $this->postManager->fetchRecent($limit, $categoryId);
    }

    /**
     * Returns a collection of mostly viewed article entities
     * 
     * @param integer $limit
     * @return array
     */
    public function getMostlyViewed($limit)
    {
        return $this->postManager->fetchMostlyViewed($limit);
    }

    /**
     * Returns random posts
     * 
     * @param integer $amount
     * @param string $categoryId Optionally can be filtered by category id
     * @return array
     */
    public function getRandom($amount, $categoryId = null)
    {
        return $this->postManager->fetchRandomPublished($amount, $categoryId);
    }
}
