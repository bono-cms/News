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
     * Creates a grid
     * 
     * @param array $posts
     * @param string $url
     * @param int $categoryId
     * @return string
     */
    private function createGrid(array $posts, $url, $categoryId)
    {
        // Configure pagination
        $paginator = $this->getPostManager()->getPaginator();
        $paginator->setUrl($url);

        // Append a breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('News');

        return $this->view->render('browser', array(
            'query' => $this->request->getQuery(),
            'route' => $this->createUrl('News:Admin:Browser@indexAction', array(null)),
            'categoryId' => $categoryId,
            'paginator' => $paginator,
            'posts' => $posts,
            'categories' => $this->getCategoryManager()->fetchAll(),
            'categoryList' => $this->getCategoryManager()->fetchList()
        ));
    }

    /**
     * Returns current page number
     * 
     * @return integer
     */
    private function getPageNumber()
    {
        return $this->request->hasQuery('page') ? $this->request->getQuery('page') : 1;
    }

    /**
     * Renders a grid
     * 
     * @return string
     */
    public function indexAction()
    {
        $page = $this->getPageNumber();
        $url = $this->createUrl('News:Admin:Browser@indexAction', array(null), 0);

        $posts = $this->getFilter($this->getModuleService('postManager'), $url);

        return $this->createGrid($posts, $url, null);
    }

    /**
     * Renders all posts associated with provided category id
     * 
     * @param integer $id Category id
     * @param integer $page Current page number
     * @return string
     */
    public function categoryAction($id, $page = 1)
    {
        $posts = $this->getPostManager()->fetchAllByPage($id, false, false, $page, $this->getSharedPerPageCount());
        $url = $this->createUrl('News:Admin:Browser@categoryAction', array($id), 1);

        return $this->createGrid($posts, $url, $id);
    }
}
