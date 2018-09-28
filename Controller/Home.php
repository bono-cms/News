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
     * @return string
     */
    public function indexAction()
    {
        // Current page number
        $pageNumber = $this->request->getQuery('page', 1);

        // Sorting method
        $sort = $this->request->getQuery('sort', null);

        $this->loadSitePlugins();

        // No breadcrumbs on home
        $this->view->getBreadcrumbBag()
                   ->clear();

        $postManager = $this->getModuleService('postManager');
        $posts = $postManager->fetchAllByPage(null, true, true, $pageNumber, $this->getModuleService('configManager')->getEntity()->getPerPageCount(), $sort);

        // Tweak pagination
        $paginator = $postManager->getPaginator();

        // The pattern /(:var)/page/(:var) is reserved, so another one should be used instead
        $paginator->setUrl($this->createUrl('News:Home@indexAction', array('page' => '(:var)'), 0));

        $page = $this->getService('Pages', 'pageManager')->fetchDefault();

        return $this->view->render('news-category', array(
            'paginator' => $paginator,
            'page' => $page,
            'posts' => $posts,
            'languages' => $this->getService('Pages', 'pageManager')->getSwitchUrls(null)
        ));
    }
}
