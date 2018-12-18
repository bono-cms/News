<?php

return array(
    'name' => 'News',
    'caption' => 'News',
    'route' => array('News:Admin:Browser@indexAction', null),
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