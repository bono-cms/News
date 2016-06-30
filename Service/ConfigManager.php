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

use Krystal\Config\ConfigModuleService;
use Krystal\Stdlib\VirtualEntity;

final class ConfigManager extends ConfigModuleService
{
    /**
     * {@inheritDoc}
     */
    public function getEntity()
    {
        $entity = new VirtualEntity();
        $entity->setCoverHeight($this->get('cover_height', 300), VirtualEntity::FILTER_FLOAT)
               ->setCoverWidth($this->get('cover_width', 300), VirtualEntity::FILTER_FLOAT)
               ->setThumbHeight($this->get('thumb_height', 30), VirtualEntity::FILTER_FLOAT)
               ->setThumbWidth($this->get('thumb_width', 30), VirtualEntity::FILTER_FLOAT)
               ->setCoverQuality($this->get('cover_quality', 75), VirtualEntity::FILTER_FLOAT)
               ->setTimeFormatInList($this->get('time_format_in_list', 'm/d/Y'), VirtualEntity::FILTER_TAGS)
               ->setTimeFormatInPost($this->get('time_format_in_post', 'm/d/Y'), VirtualEntity::FILTER_TAGS)
               ->setBlockPerPageCount($this->get('block_per_page_count', 3), VirtualEntity::FILTER_INT)
               ->setPerPageCount($this->get('per_page_count', 5), VirtualEntity::FILTER_INT);

        return $entity;
    }
}
