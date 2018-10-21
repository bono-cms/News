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

use Krystal\Stdlib\VirtualEntity;

final class PostGallery extends AbstractAdminController
{
    /**
     * Renders gallery form
     * 
     * @param mixed $image
     * @return string
     */
    private function createForm($image)
    {
        // Load view plugins
        $this->view->getPluginBag()->load('preview');

        // Append breadcrumbs
        $this->view->getBreadcrumbBag()->addOne('News', $this->createUrl('News:Admin:Browser@indexAction', array(null)))
                                       ->addOne('Edit the post', $this->createUrl('News:Admin:Post@editAction', array($image->getPostId())))
                                       ->addOne($image->getId() ? 'Update image' : 'Add new image');

        return $this->view->render('gallery.form', array(
            'image' => $image
        ));
    }

    /**
     * Renders add form
     * 
     * @param int $postId
     * @return string
     */
    public function addAction($postId)
    {
        $image = new VirtualEntity();
        $image->setPostId($postId);

        return $this->createForm($image);
    }

    /**
     * Renders edit form
     * 
     * @param int $id Image id
     * @return mixed
     */
    public function editAction($id)
    {
        $image = $this->getModuleService('postGalleryManager')->fetchById($id);

        if ($image) {
            return $this->createForm($image);
        } else {
            return false;
        }
    }

    /**
     * Deletes a post image
     * 
     * @param int $id Image id
     * @return mixed
     */
    public function deleteAction($id)
    {
        $this->getModuleService('postGalleryManager')->deleteById($id);
        $this->flashBag->set('success', 'Selected element has been removed successfully');

        return 1;
    }

    /**
     * Saves an image
     * 
     * @return mixed
     */
    public function saveAction()
    {
        $input = $this->request->getPost('image');
        $service = $this->getModuleService('postGalleryManager');

        if (!empty($input['id'])) {
            $service->update($this->request->getAll());
            $this->flashBag->set('success', 'The element has been updated successfully');

            return 1;
        } else {
            $service->add($this->request->getAll());
            $this->flashBag->set('success', 'The element has been created successfully');

            return $service->getLastId();
        }
    }
}
