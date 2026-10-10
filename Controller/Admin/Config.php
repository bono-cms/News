<?php

/**
 * This file is part of the Bono CMS
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

namespace News\Controller\Admin;

use Krystal\Validation\Validator;
use Cms\Controller\Admin\AbstractConfigController;

final class Config extends AbstractConfigController
{
    /**
     * {@inheritDoc}
     */
    protected $parent = 'News:Admin:Post@indexAction';

    /**
     * {@inheritDoc}
     */
    protected function configureValidator(Validator $validator)
    {
        // Pagination
        $validator->field('config.per_page_count', 'Per page count')
                  ->required()
                  ->addRule('numeric')
                  ->addRule('greaterthan', null, ['min' => 0]);

        $validator->field('config.block_per_page_count', 'Block per page count')
                  ->required()
                  ->addRule('numeric')
                  ->addRule('greaterthan', null, ['min' => 0]);

        // Cover image dimensions and quality
        $validator->field('config.cover_quality', 'Cover quality')
                  ->required()
                  ->addRule('numeric')
                  ->addRule('between', null, ['min' => 1, 'max' => 100]);

        $validator->field('config.cover_height', 'Cover height')
                  ->required()
                  ->addRule('numeric');

        $validator->field('config.cover_width', 'Cover width')
                  ->required()
                  ->addRule('numeric');

        // Thumbnail dimensions
        $validator->field('config.thumb_height', 'Thumb height')
                  ->required()
                  ->addRule('numeric');

        $validator->field('config.thumb_width', 'Thumb width')
                  ->required()
                  ->addRule('numeric');
    }
}