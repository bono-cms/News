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

interface PostGalleryManagerInterface
{
    /**
     * Deletes an image by its associated ID
     * 
     * @param int $id Image ID
     * @return boolean
     */
    public function deleteById($id);

    /**
     * Returns last ID
     * 
     * @return integer
     */
    public function getLastId();

    /**
     * Finds image by its associated id
     * 
     * @param int $id Image ID
     * @return mixed
     */
    public function fetchById($id);

    /**
     * Adds an image
     * 
     * @param array $input
     * @return boolean
     */
    public function add(array $input);

    /**
     * Updates an image
     * 
     * @param array $input
     * @return boolean
     */
    public function update(array $input);
}
