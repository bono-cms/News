<?php

/**
 * This file is part of the Bono CMS
 * 
 * Copyright (c) No Global State Lab
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

namespace News\Controller\Admin\Post;

final class Add extends AbstractPost
{
    /**
     * Shows adding form
     * 
     * @return string
     */
    public function indexAction()
    {
        $this->view->getPluginBag()
                   ->load('preview');

        $this->loadSharedPlugins();

        return $this->view->render($this->getTemplatePath(), $this->getWithSharedVars(array(
            'title' => 'Add a post',
            'post' => $this->getPostManager()->fetchDummy()
        )));
    }

    /**
     * Adds a post
     * 
     * @return string
     */
    public function addAction()
    {
        $formValidator = $this->getValidator($this->request->getPost('post'), $this->request->getFiles());

        if ($formValidator->isValid()) {
            $postManager = $this->getPostManager();

            if ($postManager->add($this->request->getAll())) {

                $this->flashBag->set('success', 'A post has been created successfully');
                return $postManager->getLastId();
            }

        } else {

            return $formValidator->getErrors();
        }
    }
}
