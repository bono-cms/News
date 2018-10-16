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

/* API for Category manager */
interface CategoryManagerInterface
{
    /**
     * Returns a collection of switching URLs
     * 
     * @param string $id Category ID
     * @return array
     */
    public function getSwitchUrls($id);

    /**
     * Fetch all categories with their attached post IDs and names
     * 
     * @param int $excludedId Excluded post IDs
     * @return array
     */
    public function fetchAllWithPosts($excludedId = null);

    /**
     * Returns category's last id
     * 
     * @return string
     */
    public function getLastId();

    /**
     * Fetches categories as a list
     * 
     * @return array
     */
    public function fetchList();

    /**
     * Fetches all category bags
     * 
     * @return array|boolean
     */
    public function fetchAll();

    /**
     * Adds a category
     * 
     * @param array $input Raw form data
     * @return boolean Depending on success
     */
    public function add(array $input);

    /**
     * Updates a category
     * 
     * @param array $input Raw form data
     * @return boolean Depending on success
     */
    public function update(array $form);

    /**
     * Fetches category's entity by its associated id
     * 
     * @param string $id
     * @param boolean $withTranslations Whether to fetch translations or not
     * @return \Krystal\Stdlib\VirtualEntity|boolean|array
     */
    public function fetchById($id, $withTranslations);

    /**
     * Deletes a category by its associated id
     * 
     * @param string $id Category id
     * @return boolean Depending on success
     */
    public function deleteById($id);
}
