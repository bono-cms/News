<?php

/**
 * Module configuration container
 */

return array(
    'name' => 'News',
    'route' => array('News:Admin:Browser@indexAction', null),
    'description' => 'News module allows you to manage news on your site',
    // Bookmarks of this module
    'bookmarks' => array(
        array(
            'name' => 'Add new post',
            'controller' => 'News:Admin:Post@addAction',
            'icon' => 'fas fa-newspaper'
        )
    ),
    'menu' => array(
        'name' => 'News',
        'icon' => 'fas fa-newspaper',
        'items' => array(
            array(
                'route' => 'News:Admin:Browser@indexAction',
                'name' => 'View all news'
            ),
            array(
                'route' => 'News:Admin:Post@addAction',
                'name' => 'Add new post'
            ),
            array(
                'route' => 'News:Admin:Category@addAction',
                'name' => 'Add new category'
            ),
            array(
                'route' => 'News:Admin:Config@indexAction',
                'name' => 'Configuration'
            )
        )
    )
);