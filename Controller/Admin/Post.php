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
     * Creates a form
     * 
     * @param \Krystal\Stdlib\VirtualEntity $post
     * @param string $title
     * @return string
     */
    private function createForm(VirtualEntity $post, $title)
    {
        // Load view plugins
        $this->view->getPluginBag()
                   ->appendScript('@News/admin/post.form.js')
                   ->load(array($this->getWysiwygPluginName(), 'datepicker'));

        // Append breadcrumbs
        $this->view->getBreadcrumbBag()->addOne('News', 'News:Admin:Browser@indexAction')
                                       ->addOne($title);

        return $this->view->render('post.form', array(
            'categories' => $this->getCategoryManager()->fetchList(),
            'post' => $post
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
        
        $entity = $this->getPostManager()->fetchDummy();
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
        $post = $this->getPostManager()->fetchById($id);

        if ($post !== false) {
            return $this->createForm($post, 'Edit the post');
        } else {
            return false;
        }
    }

    /**
     * Deletes a post
     * 
     * @return string
     */
    public function deleteAction()
    {
        // Batch removal
        if ($this->request->hasPost('toDelete')) {
            $ids = array_keys($this->request->getPost('toDelete'));

            $postManager = $this->getPostManager();
            $postManager->deleteByIds($ids);

            $this->flashBag->set('success', 'Selected posts have been removed successfully');
        } else {
            $this->flashBag->set('warning', 'You should select at least one post to remove');
        }

        // Single removal
        if ($this->request->hasPost('id')) {
            $id = $this->request->getPost('id');

            if ($this->getPostManager()->deleteById($id)) {
                $this->flashBag->set('success', 'Selected post has been removed successfully');
            }
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
        if ($this->request->hasPost('published', 'seo')) {
            $published = $this->request->getPost('published');
            $seo = $this->request->getPost('seo');

            $postManager = $this->getPostManager();

            if ($postManager->updatePublished($published) && $postManager->updateSeo($seo)) {
                $this->flashBag->set('success', 'Settings have been saved successfully');
                return '1';
            }
        }
    }

    /**
     * Persists a post
     * 
     * @return string
     */
    public function saveAction()
    {
        $input = $this->request->getPost('post');

        $formValidator = $this->validatorFactory->build(array(
            'input' => array(
                'source' => $input,
                'definition' => array(
                    'title' => new Pattern\Title(),
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

        if ($formValidator->isValid()) {
            $postManager = $this->getPostManager();

            if ($input['id']) {
                if ($postManager->update($this->request->getAll())) {
                    $this->flashBag->set('success', 'A post has been updated successfully');
                    return '1';
                }

            } else {
                if ($postManager->add($this->request->getAll())) {
                    $this->flashBag->set('success', 'A post has been created successfully');
                    return $postManager->getLastId();
                }
            }

        } else {
            return $formValidator->getErrors();
        }
    }
}
