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

final class PostEntity extends VirtualEntity
{
    /**
     * Checks whether current post has image gallery (i.e at least one attached image)
     * 
     * @return boolean
     */
    public function hasGallery()
    {
        $gallery = $this->getGallery();

        return is_array($gallery) && !empty($gallery);
    }

    /**
     * Returns image URL
     * 
     * @param string $size
     * @return string
     */
    public function getImageUrl($size)
    {
        return $this->getImageBag()->getUrl($size);
    }

    /**
     * Tells whether current bag has a cover
     * 
     * @return boolean
     */
    public function hasCover()
    {
        return $this->getCover() != '';
    }
}
