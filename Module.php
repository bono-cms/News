<?php

/**
 * This file is part of the Bono CMS
 * 
 * Copyright (c) No Global State Lab
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

namespace News;

use Cms\AbstractCmsModule;
use Krystal\Image\Tool\ImageManager;
use Krystal\Stdlib\VirtualEntity;
use News\Service\CategoryManager;
use News\Service\PostManager;
use News\Service\PostImageManagerFactory;
use News\Service\TimeBag;
use News\Service\SiteService;
use News\Service\PostGalleryManager;

final class Module extends AbstractCmsModule
{
    /**
     * {@inheritDoc}
     */
    public function getServiceProviders()
    {
        $categoryMapper = $this->getMapper('/News/Storage/MySQL/CategoryMapper');
        $postMapper = $this->getMapper('/News/Storage/MySQL/PostMapper');
        $postGalleryMapper = $this->getMapper('/News/Storage/MySQL/PostGalleryMapper');

        $webPageManager = $this->getWebPageManager();
        $historyManager = $this->getHistoryManager();

        $configManager = $this->createConfigService();
        $config = $configManager->getEntity();

        $imageManager = $this->getImageManager($config);
        $postManager = new PostManager($postMapper, $categoryMapper, TimeBag::factory($config), $webPageManager, $imageManager, $historyManager);
        $categoryManager = new CategoryManager($categoryMapper, $postMapper, $webPageManager, $historyManager, $imageManager);

        return array(
            'siteService' => new SiteService($postManager, $categoryManager, $config),
            'configManager' => $configManager,
            'categoryManager' => $categoryManager,
            'postManager' => $postManager,
            'postGalleryManager' => new PostGalleryManager($postGalleryMapper, $this->createGalleryImageManager($config))
        );
    }

    /**
     * Builds gallery image manager service
     * 
     * @param \Krystal\Stdlib\VirtualEntity $config
     * @return \Krystal\Image\Tool\ImageManager
     */
    private function createGalleryImageManager(VirtualEntity $config)
    {
        $plugins = array(
            'thumb' => array(
                'quality' => $config->getCoverQuality(),
                'dimensions' => array(
                    // For administration panel
                    array(400, 400),

                    // Dimensions for the site
                    array($config->getCoverWidth(), $config->getCoverHeight()),
                    array($config->getThumbWidth(), $config->getThumbHeight()),
                )
            )
        );

        return new ImageManager(
            '/data/uploads/module/news/gallery/',
            $this->appConfig->getRootDir(),
            $this->appConfig->getRootUrl(),
            $plugins
        );
    }
    
    /**
     * Returns prepared and configured image manager service
     * 
     * @param \Krystal\Stdlib\VirtualEntity $config
     * @return \Krystal\Image\Tool\ImageManager
     */
    private function getImageManager(VirtualEntity $config)
    {
        $plugins = array(
            'thumb' => array(
                'quality' => $config->getCoverQuality(),
                'dimensions' => array(
                    // For administration panel
                    array(200, 200),

                    // Dimensions for the site
                    array($config->getCoverWidth(), $config->getCoverHeight()),
                    array($config->getThumbWidth(), $config->getThumbHeight()),
                )
            )
        );

        return new ImageManager(
            '/data/uploads/module/news/posts/',
            $this->appConfig->getRootDir(),
            $this->appConfig->getRootUrl(),
            $plugins
        );
    }
}
