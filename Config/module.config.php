<?php

return array(
    'name' => 'News',
    'caption' => 'News',
    'route' => 'News:Admin:Browser@indexAction',
    'icon' => 'fa fa-newspaper-o fa-5x',
    'order' => 1,
    'description' => 'News module allows you to manage news on your site',

    // Bookmarks of this module
    'bookmarks' => array(
        array(
            'name' => 'Add new post',
            'controller' => 'News:Admin:Post@addAction',
            'icon' => 'fa fa-newspaper-o'
        )
    )
);