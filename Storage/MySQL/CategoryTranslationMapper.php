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

use Krystal\Db\Sql\RawSqlFragment;
use Cms\Storage\MySQL\AbstractMapper;

final class CategoryTranslationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('bono_module_news_categories_translations');
    }
}
