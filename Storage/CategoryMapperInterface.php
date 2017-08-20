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

interface CategoryMapperInterface
{
    /**
     * Fetch all categories with their attached post names
     * 
     * @return array
     */
    public function fetchAllWithPosts();

    /**
     * Fetches as a list
     * 
     * @return array
     */
    public function fetchList();

    /**
     * Deletes a category by its associated id
     * 
     * @param string $id Category id
     * @return boolean
     */
    public function deleteById($id);

    /**
     * Fetches all categories
     * 
     * @return array
     */
    public function fetchAll();

    /**
     * Fetches category name by its associated id
     * 
     * @param string $id Category id
     * @return string
     */
    public function fetchNameById($id);

    /**
     * Fetches category data by its associated id
     * 
     * @param string $id Category id
     * @param boolean $withTranslations Whether to fetch translations or not
     * @return array
     */
    public function fetchById($id, $withTranslations);
}
