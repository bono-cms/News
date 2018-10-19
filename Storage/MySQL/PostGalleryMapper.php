<?php

/**
 * This file is part of the Bono CMS
 * 
 * Copyright (c) No Global State Lab
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

namespace News\Storage\MySQL;

use Cms\Storage\MySQL\AbstractMapper;
use News\Storage\PostGalleryMapperInterface;

final class PostGalleryMapper extends AbstractMapper implements PostGalleryMapperInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('bono_module_news_posts_gallery');
    }

    /**
     * Fetches post image by its ID
     * 
     * @param int $id Image ID
     * @return array
     */
    public function fetchById($id)
    {
        // Columns to be selected
        $columns = array(
            self::column('id'),
            self::column('post_id'),
            PostTranslationMapper::column('name') => 'post',
            self::column('order'),
            self::column('image'),
        );

        $db = $this->db->select($columns)
                       ->from(self::getTableName())
                       // Post relation
                       ->innerJoin(PostMapper::getTableName(), array(
                            PostMapper::column('id') => self::getRawColumn('post_id')
                       ))
                       // Post translation relation
                       ->leftJoin(PostTranslationMapper::getTableName(), array(
                            PostTranslationMapper::column('id') => PostMapper::getRawColumn('id')
                       ))
                       // Constraints
                       ->whereEquals(self::column('id'), $id)
                       ->andWhereEquals(PostTranslationMapper::column('lang_id'), $this->getLangId());

        return $db->query();
    }
}
