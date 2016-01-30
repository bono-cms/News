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

use Krystal\Validate\Pattern;
use Krystal\Stdlib\VirtualEntity;

final class Category extends AbstractAdminController
{
    /**
     * Creates a form
     * 
     * @param \Krystal\Stdlib\VirtualEntity $category
     * @param string $title
     * @return string
     */
    private function createForm(VirtualEntity $category, $title)
    {
        // Load view plugins
        $this->loadMenuWidget();
        $this->view->getPluginBag()
                    ->load($this->getWysiwygPluginName())
                    ->appendScript('@News/admin/category.form.js');

        // Append breadcrumbs
        $this->view->getBreadcrumbBag()->addOne('News', 'News:Admin:Browser@indexAction')
                                       ->addOne($title);

        return $this->view->render('category.form', array(
            'category' => $category
        ));
    }

    /**
     * Renders empty form
     * 
     * @return string
     */
    public function addAction()
    {
        $category = new VirtualEntity();
        $category->setSeo(true);

        return $this->createForm($category, 'Add a category');
    }

    /**
     * Renders edit form
     * 
     * @param string $id
     * @return string
     */
    public function editAction($id)
    {
        $category = $this->getCategoryManager()->fetchById($id);

        if ($category !== false) {
            return $this->createForm($category, 'Edit the category');
        } else {
            return false;
        }
    }

    /**
     * Removes a category
     * 
     * @return string
     */
    public function deleteAction()
    {
        if ($this->request->hasPost('id')) {
            $id = $this->request->getPost('id');

            if ($this->getCategoryManager()->deleteById($id)) {
                $this->flashBag->set('success', 'Selected category has been removed successfully');
                return '1';
            }
        }
    }

    /**
     * Persists a category
     * 
     * @return string
     */
    public function saveAction()
    {
        $input = $this->request->getPost('category');

        $formValidator = $this->validatorFactory->build(array(
            'input' => array(
                'source' => $input,
                'definition' => array(
                    'title' => new Pattern\Title()
                )
            )
        ));

        if ($formValidator->isValid()) {
            $categoryManager = $this->getCategoryManager();

            if ($input['id']) {
                if ($categoryManager->update($this->request->getPost())) {
                    $this->flashBag->set('success', 'The category has been updated successfully');
                    return '1';
                }

            } else {
                if ($categoryManager->add($this->request->getPost())) {
                    $this->flashBag->set('success', 'The category has been created successfully');
                    return $categoryManager->getLastId();
                }
            }

        } else {
            return $formValidator->getErrors();
        }
    }
}
