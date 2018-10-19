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

interface PostGalleryMapperInterface
{
    /**
     * Fetches post image by its ID
     * 
     * @param int $id Image ID
     * @return array
     */
    public function fetchById($id);
}
