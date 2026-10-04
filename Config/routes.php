<?php

/**
 * This file is part of the Bono CMS
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

return [
    
    '/news/pg/(:var)' => [
        'controller' => 'Home@indexAction'
    ],
    
    '/module/news/post/(:var)' => [
        'controller' => 'Post@indexAction'
    ],

    '/%s/module/news' => [
        'controller' => 'Admin:Post@indexAction',
    ],

    '/%s/module/news/config' => [
        'controller' => 'Admin:Config@indexAction',
    ],
    
    '/%s/module/news/config.ajax' => [
        'controller' => 'Admin:Config@saveAction',
        'disallow' => ['guest']
    ],
    
    '/%s/module/news/tweak' => [
        'controller' => 'Admin:Post@tweakAction',
        'disallow' => ['guest']
    ],
    
    // Post gallery
    '/%s/module/news/post/gallery/add/(:var)' => [
        'controller' => 'Admin:PostGallery@addAction'
    ],

    '/%s/module/news/post/gallery/edit/(:var)' => [
        'controller' => 'Admin:PostGallery@editAction'
    ],

    '/%s/module/news/post/gallery/delete/(:var)' => [
        'controller' => 'Admin:PostGallery@deleteAction'
    ],

    '/%s/module/news/post/gallery/save' => [
        'controller' => 'Admin:PostGallery@saveAction'
    ],

    // Post
    '/%s/module/news/post/add' => [
        'controller' => 'Admin:Post@addAction'
    ],
    
    '/%s/module/news/post/edit/(:var)' => [
        'controller' => 'Admin:Post@editAction'
    ],
    
    '/%s/module/news/post/save' => [
        'controller' => 'Admin:Post@saveAction',
        'disallow' => ['guest']
    ],
    
    '/%s/module/news/post/delete/(:var)' => [
        'controller' => 'Admin:Post@deleteAction',
        'disallow' => ['guest']
    ],
    
    '/%s/module/news/category/add' => [
        'controller' => 'Admin:Category@addAction'
    ],
    
    '/%s/module/news/category/edit/(:var)' => [
        'controller' => 'Admin:Category@editAction'
    ],
    
    '/%s/module/news/category/save' => [
        'controller' => 'Admin:Category@saveAction',
        'disallow' => ['guest']
    ],
    
    '/%s/module/news/category/delete/(:var)' => [
        'controller' => 'Admin:Category@deleteAction',
        'disallow' => ['guest']
    ]
];