<?php

/**
 * This file is part of the Bono CMS
 * 
 * Copyright (c) No Global State Lab
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

namespace News\Controller;

use Site\Controller\AbstractController;

final class Home extends AbstractController
{
    /**
     * Shows all recent posts
     * 
     * @param integer $pageNumber Current page number
     * @return string
     */
    public function indexAction($pageNumber = 1)
    {
        $this->loadSitePlugins();

        // No breadcrumbs on home
        $this->view->getBreadcrumbBag()
                   ->clear();

        $postManager = $this->getModuleService('postManager');
        $posts = $postManager->fetchAllByPage(true, $pageNumber, $this->getModuleService('configManager')->getEntity()->getPerPageCount());

        // Tweak pagination
        $paginator = $postManager->getPaginator();

        // The pattern /(:var)/page/(:var) is reserved, so another one should be used instead
        $paginator->setUrl('/news/pg/(:var)');

        $page = $this->getService('Pages', 'pageManager')->fetchDefault();

        return $this->view->render('news-category', array(
            'paginator' => $paginator,
            'page' => $page,
            'posts' => $posts
        ));
    }
}
