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
     * Post manager
     * 
     * @var \News\Service\PostManagerInterface
     */
    private $postManager;

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
     * @param \Krystal\Stdlib\VirtualEntity $config
     * @return void
     */
    public function __construct(PostManagerInterface $postManager, VirtualEntity $config)
    {
        $this->postManager = $postManager;
        $this->config = $config;
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
