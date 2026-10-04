<?php

/**
 * Module configuration container
 */

return [
    'name' => 'News',
    'route' => ['News:Admin:Post@indexAction', null],
    'description' => 'News module allows you to manage news on your site',
    // Bookmarks of this module
    'bookmarks' => [
        [
            'name' => 'Add new post',
            'controller' => 'News:Admin:Post@addAction',
            'icon' => 'fas fa-newspaper'
        ]
    ],
    'menu' => [
        'name' => 'News',
        'icon' => 'fas fa-newspaper',
        'items' => [
            [
                'route' => 'News:Admin:Post@indexAction',
                'name' => 'View all news'
            ],
            [
                'route' => 'News:Admin:Post@addAction',
                'name' => 'Add new post'
            ],
            [
                'route' => 'News:Admin:Category@addAction',
                'name' => 'Add new category'
            ],
            [
                'route' => 'News:Admin:Config@indexAction',
                'name' => 'Configuration'
            ]
        ]
    ]
];