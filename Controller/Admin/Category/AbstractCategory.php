<?php

/**
 * This file is part of the Bono CMS
 * 
 * Copyright (c) No Global State Lab
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

namespace News\Controller\Admin\Category;

use Krystal\Validate\Pattern;
use News\Controller\Admin\AbstractAdminController;

abstract class AbstractCategory extends AbstractAdminController
{
    /**
     * Returns prepared validator instance
     * 
     * @param array $input Raw input data
     * @return \Krystal\Validate\ValidatorChain
     */
    final protected function getValidator(array $input)
    {
        return $this->validatorFactory->build(array(
            'input' => array(
                'source' => $input,
                'definition' => array(
                    'title' => new Pattern\Title()
                )
            )
        ));
    }

    /**
     * Returns template path
     * 
     * @return string
     */
    final protected function getTemplatePath()
    {
        return 'category.form';
    }

    /**
     * Loads shared plugins
     * 
     * @return void
     */
    final protected function loadSharedPlugins()
    {
        $this->loadMenuWidget();
        $this->view->getPluginBag()
                    ->load($this->getWysiwygPluginName())
                    ->appendScript('@News/admin/category.form.js');
    }

    /**
     * Loads breadcrumbs
     * 
     * @param string $title
     * @return void
     */
    final protected function loadBreadcrumbs($title)
    {
        $this->view->getBreadcrumbBag()->addOne('News', 'News:Admin:Browser@indexAction')
                                       ->addOne($title);
    }
}
