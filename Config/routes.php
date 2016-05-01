<?php

/**
 * This file is part of the Bono CMS
 * 
 * Copyright (c) No Global State Lab
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

return array(
    
    '/module/news/post/(:var)' => array(
        'controller' => 'Post@indexAction'
    ),

    '/admin/module/news' => array(
        'controller' => 'Admin:Browser@indexAction',
    ),
    
    '/admin/module/news/config' => array(
        'controller' => 'Admin:Config@indexAction',
    ),
    
    '/admin/module/news/config.ajax' => array(
        'controller' => 'Admin:Config@saveAction',
        'disallow' => array('guest')
    ),
    
    '/admin/module/news/tweak' => array(
        'controller' => 'Admin:Post@tweakAction',
        'disallow' => array('guest')
    ),
    
    '/admin/module/news/browse/(:var)' => array(
        'controller' => 'Admin:Browser@indexAction',
    ),
    
    '/admin/module/news/browse/category/(:var)' => array(
        'controller' => 'Admin:Browser@categoryAction'
    ),
    
    '/admin/module/news/browse/category/(:var)/page/(:var)' => array(
        'controller' => 'Admin:Browser@categoryAction'
    ),
    
    '/admin/module/news/post/add' => array(
        'controller' => 'Admin:Post@addAction'
    ),
    
    '/admin/module/news/post/edit/(:var)' => array(
        'controller' => 'Admin:Post@editAction'
    ),
    
    '/admin/module/news/post/save' => array(
        'controller' => 'Admin:Post@saveAction',
        'disallow' => array('guest')
    ),
    
    '/admin/module/news/post/delete/(:var)' => array(
        'controller' => 'Admin:Post@deleteAction',
        'disallow' => array('guest')
    ),
    
    '/admin/module/news/category/add' => array(
        'controller' => 'Admin:Category@addAction'
    ),
    
    '/admin/module/news/category/edit/(:var)' => array(
        'controller' => 'Admin:Category@editAction'
    ),
    
    '/admin/module/news/category/save' => array(
        'controller' => 'Admin:Category@saveAction',
        'disallow' => array('guest')
    ),
    
    '/admin/module/news/category/delete/(:var)' => array(
        'controller' => 'Admin:Category@deleteAction',
        'disallow' => array('guest')
    )
);
