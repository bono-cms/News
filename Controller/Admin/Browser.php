<?php

/**
 * This file is part of the Bono CMS
 * 
 * Copyright (c) No Global State Lab
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

namespace News\Controller\Admin;

final class Browser extends AbstractAdminController
{
    /**
     * Renders a grid
     * 
     * @return string
     */
    public function indexAction()
    {
        $postManager = $this->getModuleService('postManager');

        $posts = $this->getFilter($postManager);

        // Append a breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('News');

        return $this->view->render('index', array(
            'paginator' => $postManager->getPaginator(),
            'posts' => $posts,
            'categories' => $this->getCategoryManager()->fetchAll(),
            'categoryList' => $this->getCategoryManager()->fetchList()
        ));
    }
}
