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

	'/admin/module/news' =>	array(
		'controller' => 'Admin:Browser@indexAction',
	),
	
	'/admin/module/news/config' => array(
		'controller' => 'Admin:Config@indexAction',
	),
	
	'/admin/module/news/config.ajax' => array(
		'controller' => 'Admin:Config@saveAction',
		'disallow' => array('guest')
	),
	
	'/admin/module/news/save.ajax' => array(
		'controller' => 'Admin:Browser@saveAction',
		'disallow' => array('guest')
	),
	
	'/admin/module/news/delete-selected.ajax' => array(
		'controller' => 'Admin:Browser@deleteSelectedAction',
		'disallow' => array('guest')
	),
	
	'/admin/module/news/browse/(:var)' => array(
		'controller' => 'Admin:Browser@indexAction',
	),
	
	'/admin/module/news/browse/category/(:var)' => array(
		'controller' => 'Admin:Browser@categoryAction'
	),
	
	'/admin/module/news/browse/category/(:var)/page/(:var)'	=>	array(
		'controller' => 'Admin:Browser@categoryAction'
	),
	
	'/admin/module/news/post/add' => array(
		'controller' => 'Admin:Post:Add@indexAction'
	),
	
	'/admin/module/news/post/add.ajax' => array(
		'controller' => 'Admin:Post:Add@addAction',
		'disallow' => array('guest')
	),
	
	'/admin/module/news/post/edit/(:var)' => array(
		'controller' => 'Admin:Post:Edit@indexAction'
	),
	
	'/admin/module/news/post/edit.ajax' => array(
		'controller' => 'Admin:Post:Edit@updateAction',
		'disallow' => array('guest')
	),
	
	'/admin/module/news/post/delete.ajax' => array(
		'controller' => 'Admin:Browser@deleteAction',
		'disallow' => array('guest')
	),
	
	'/admin/module/news/category/add' => array(
		'controller' => 'Admin:Category:Add@indexAction'
	),
	
	'/admin/module/news/category/add.ajax' => array(
		'controller' => 'Admin:Category:Add@addAction',
		'disallow' => array('guest')
	),
	
	'/admin/module/news/category/edit/(:var)' => array(
		'controller' => 'Admin:Category:Edit@indexAction'
	),
	
	'/admin/module/news/category/edit.ajax' => array(
		'controller' => 'Admin:Category:Edit@updateAction',
		'disallow' => array('guest')
	),
	
	'/admin/module/news/category/delete.ajax' => array(
		'controller' => 'Admin:Browser@deleteCategoryAction',
		'disallow' => array('guest')
	)
);
