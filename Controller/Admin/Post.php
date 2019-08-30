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

final class Post extends AbstractAdminController
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

    /**
     * Creates a form
     * 
     * @param \Krystal\Stdlib\VirtualEntity|array $post
     * @param string $title
     * @return string
     */
    private function createForm($post, $title)
    {
        // If coming from edit form, then grab ID to be excluded
        if (is_array($post) && isset($post[0]['id'])) {
            $id = $post[0]['id'];
        } else {
            // Coming from add form, no ID to be excluded
            $id = null;
        }

        // Load view plugins
        $this->view->getPluginBag()
                   ->appendScript('@News/admin/post.form.js')
                   ->load(array($this->getWysiwygPluginName(), 'datepicker', 'chosen'));

        // Append breadcrumbs
        $this->view->getBreadcrumbBag()->addOne('News', 'News:Admin:Post@indexAction')
                                       ->addOne($title);

        return $this->view->render('post.form', array(
            'categories' => $this->getCategoryManager()->fetchList(),
            // If you don't require option to attach similar posts, you can comment 'posts' key to reduce DB queries
            'posts' => $this->getCategoryManager()->fetchAllWithPosts($id),
            'post' => $post,
            'images' => $id !== null ? $this->getModuleService('postGalleryManager')->fetchAllByPostId($id) : array()
        ));
    }

    /**
     * Renders empty form
     * 
     * @return string
     */
    public function addAction()
    {
        $this->view->getPluginBag()
                   ->load('preview');

        // CMS configuration object
        $config = $this->getService('Cms', 'configManager')->getEntity();
        $entity = $this->getPostManager()->fetchDummy($config);

        return $this->createForm($entity, 'Add a post');
    }

    /**
     * Renders edit form
     * 
     * @param string $id
     * @return string
     */
    public function editAction($id)
    {
        $post = $this->getPostManager()->fetchById($id, false, true);

        if ($post !== false) {
            $name = $this->getCurrentProperty($post, 'name');
            return $this->createForm($post, $this->translator->translate('Edit the post "%s"', $name));
        } else {
            return false;
        }
    }

    /**
     * Deletes a post or a collection of them
     * 
     * @param string $id
     * @return string
     */
    public function deleteAction($id)
    {
        $historyService = $this->getService('Cms', 'historyManager');
        $service = $this->getModuleService('postManager');

        // Batch removal
        if ($this->request->hasPost('batch')) {
            $ids = array_keys($this->request->getPost('batch'));

            $service->deleteByIds($ids);
            $this->flashBag->set('success', 'Selected elements have been removed successfully');

            // Save in the history
            $historyService->write('News', '%s posts have been removed', count($ids));

        } else {
            $this->flashBag->set('warning', 'You should select at least one element to remove');
        }

        // Single removal
        if (!empty($id)) {
            $post = $this->getPostManager()->fetchById($id, false, false);
            $service->deleteById($id);
            $this->flashBag->set('success', 'Selected element has been removed successfully');

            // Save in the history
            $historyService->write('News', 'Post "%s" has been removed', $post->getName());
        }

        return '1';
    }

    /**
     * Saves options that come from a table
     * 
     * @return string
     */
    public function tweakAction()
    {
        if ($this->request->isPost()) {
            $this->getPostManager()->updateSettings($this->request->getPost());

            $this->flashBag->set('success', 'Settings have been saved successfully');
            return '1';
        }
    }

    /**
     * Persists a post
     * 
     * @return string
     */
    public function saveAction()
    {
        $post = $this->request->getPost('post');

        $formValidator = $this->createValidator(array(
            'input' => array(
                'source' => $post,
                'definition' => array(
                    'name' => new Pattern\Name(),
                    'intro' => new Pattern\IntroText(),
                    'full'  => new Pattern\FullText(),
                    'date' => new Pattern\DateFormat('m/d/Y')
                )
            ),

            'file' => array(
                'source' => $this->request->getFiles(),
                'definition' => array(
                    'file' => new Pattern\ImageFile(array(
                        'required' => false
                    ))
                )
            )
        ));

        if (1) {
            $service = $this->getModuleService('postManager');

            $historyService = $this->getService('Cms', 'historyManager');
            $name = $this->getCurrentProperty($this->request->getPost('translation'), 'name');

            if (!empty($post['id'])) {
                if ($service->update($this->request->getAll())) {
                    $this->flashBag->set('success', 'The element has been updated successfully');

                    $historyService->write('News', 'Post "%s" has been updated', $name);
                    return '1';
                }

            } else {
                if ($service->add($this->request->getAll())) {
                    $this->flashBag->set('success', 'The element has been created successfully');

                    $historyService->write('News', 'New post "%s" has been created', $name);
                    return $service->getLastId();
                }
            }

        } else {
            return $formValidator->getErrors();
        }
    }
}
