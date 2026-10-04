<?php

/**
 * This file is part of the Bono CMS
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

namespace News\Controller\Admin;

use Krystal\Stdlib\VirtualEntity;

final class Category extends AbstractAdminController
{
    /**
     * Creates a form
     * 
     * @param \Krystal\Stdlib\VirtualEntity|array $category
     * @param string $title
     * @return string
     */
    private function createForm($category, $title)
    {
        // Load view plugins
        $this->loadMenuWidget();
        $this->view->getPluginBag()
                    ->load($this->getWysiwygPluginName());

        // Append breadcrumbs
        $this->view->getBreadcrumbBag()->addOne('News', 'News:Admin:Post@indexAction')
                                       ->addOne($title);

        return $this->view->render('category.form', [
            'category' => $category
        ]);
    }

    /**
     * Renders empty form
     * 
     * @return string
     */
    public function addAction()
    {
        // CMS configuration object
        $config = $this->getService('Cms', 'configManager')->getEntity();

        $category = new VirtualEntity();
        $category->setSeo(true)
                 ->setChangeFreq($config->getSitemapFrequency())
                 ->setPriority($config->getSitemapPriority());

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
        $category = $this->getCategoryManager()->fetchById($id, true);

        if ($category !== false) {
            $name = $this->getCurrentProperty($category, 'name');
            return $this->createForm($category, $this->translator->translate('Edit the category "%s"', $name));
        } else {
            return false;
        }
    }

    /**
     * Removes a category by its id
     * 
     * @param string $id Category id
     * @return string
     */
    public function deleteAction($id)
    {
        $historyService = $this->getService('Cms', 'historyManager');
        $category = $this->getCategoryManager()->fetchById($id, false);

        if ($category !== false) {
            $service = $this->getModuleService('categoryManager');
            $service->deleteById($id);

            // Save in the history
            $historyService->write('News', 'Category "%s" has been removed', $category->getName());

            $this->flashBag->set('success', 'Selected element has been removed successfully');
        }

        return $this->json([
            'refresh' => true
        ]);
    }

    /**
     * Persists a category
     * 
     * @return string
     */
    public function saveAction()
    {
        $input = $this->request->getPost('category');

        $validator = $this->createValidation();

        $validator->field('translation.*.name')
                  ->required()
                  ->addRule('minlength', null, ['min' => 5]);

        if ($validator->isPassed()) {
            $service = $this->getModuleService('categoryManager');
            $historyService = $this->getService('Cms', 'historyManager');

            $name = $this->getCurrentProperty($this->request->getPost('translation'), 'name');
            $service->save($this->request->getPost());

            if (!empty($input['id'])) {
                $this->flashBag->set('success', 'The element has been updated successfully');

                $historyService->write('News', 'Category "%s" has been updated', $name);
                return $this->json([
                    'refresh' => true
                ]);

            } else {
                $this->flashBag->set('success', 'The element has been created successfully');

                $historyService->write('News', 'Category "%s" has been created', $name);
                return $this->json([
                    'redirect' => $this->createUrl('News:Admin:Category@editAction', [$service->getLastId()]),
                ]);
            }

        } else {
            return $this->json([
                'errors' => $validator->getErrors()
            ]);
        }
    }
}