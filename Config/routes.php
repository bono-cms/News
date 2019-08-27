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
    
    '/news/pg/(:var)' => array(
        'controller' => 'Home@indexAction'
    ),
    
    '/module/news/post/(:var)' => array(
        'controller' => 'Post@indexAction'
    ),

    '/%s/module/news' => array(
        'controller' => 'Admin:Post@indexAction',
    ),

    '/%s/module/news/config' => array(
        'controller' => 'Admin:Config@indexAction',
    ),
    
    '/%s/module/news/config.ajax' => array(
        'controller' => 'Admin:Config@saveAction',
        'disallow' => array('guest')
    ),
    
    '/%s/module/news/tweak' => array(
        'controller' => 'Admin:Post@tweakAction',
        'disallow' => array('guest')
    ),
    
    // Post gallery
    '/%s/module/news/post/gallery/add/(:var)' => array(
        'controller' => 'Admin:PostGallery@addAction'
    ),

    '/%s/module/news/post/gallery/edit/(:var)' => array(
        'controller' => 'Admin:PostGallery@editAction'
    ),

    '/%s/module/news/post/gallery/delete/(:var)' => array(
        'controller' => 'Admin:PostGallery@deleteAction'
    ),

    '/%s/module/news/post/gallery/save' => array(
        'controller' => 'Admin:PostGallery@saveAction'
    ),

    // Post
    '/%s/module/news/post/add' => array(
        'controller' => 'Admin:Post@addAction'
    ),
    
    '/%s/module/news/post/edit/(:var)' => array(
        'controller' => 'Admin:Post@editAction'
    ),
    
    '/%s/module/news/post/save' => array(
        'controller' => 'Admin:Post@saveAction',
        'disallow' => array('guest')
    ),
    
    '/%s/module/news/post/delete/(:var)' => array(
        'controller' => 'Admin:Post@deleteAction',
        'disallow' => array('guest')
    ),
    
    '/%s/module/news/category/add' => array(
        'controller' => 'Admin:Category@addAction'
    ),
    
    '/%s/module/news/category/edit/(:var)' => array(
        'controller' => 'Admin:Category@editAction'
    ),
    
    '/%s/module/news/category/save' => array(
        'controller' => 'Admin:Category@saveAction',
        'disallow' => array('guest')
    ),
    
    '/%s/module/news/category/delete/(:var)' => array(
        'controller' => 'Admin:Category@deleteAction',
        'disallow' => array('guest')
    )
);
