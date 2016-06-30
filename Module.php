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
use News\Service\TaskManager;
use News\Service\PostImageManagerFactory;
use News\Service\TimeBagFactory;
use News\Service\SiteService;

final class Module extends AbstractCmsModule
{
    /**
     * {@inheritDoc}
     */
    public function getServiceProviders()
    {
        $categoryMapper = $this->getMapper('/News/Storage/MySQL/CategoryMapper');
        $postMapper = $this->getMapper('/News/Storage/MySQL/PostMapper');

        $webPageManager = $this->getWebPageManager();
        $historyManager = $this->getHistoryManager();

        $configManager = $this->createConfigService();
        $config = $configManager->getEntity();

        $imageManager = $this->getImageManager($config);
        $postManager = new PostManager($postMapper, $categoryMapper, $this->getTimeBag($config), $webPageManager, $imageManager, $historyManager);

        return array(
            'siteService' => new SiteService($postManager, $config),
            'configManager' => $configManager,
            'taskManager' => new TaskManager($postMapper),
            'categoryManager' => new CategoryManager($categoryMapper, $postMapper, $webPageManager, $historyManager, $imageManager, $this->getMenuWidget()),
            'postManager' => $postManager
        );
    }

    /**
     * Returns time bag
     * 
     * @param \Krystal\Stdlib\VirtualEntity $config
     * @return \News\Service\TimeBag
     */
    private function getTimeBag(VirtualEntity $config)
    {
        $factory = new TimeBagFactory($config);
        return $factory->build();
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
