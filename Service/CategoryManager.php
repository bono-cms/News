<?php

/**
 * This file is part of the Bono CMS
 * 
 * Copyright (c) No Global State Lab
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

namespace News\Service;

use Cms\Service\AbstractManager;
use Cms\Service\WebPageManagerInterface;
use News\Storage\CategoryMapperInterface;
use News\Storage\PostMapperInterface;
use Krystal\Stdlib\VirtualEntity;
use Krystal\Image\Tool\ImageManagerInterface;
use Krystal\Stdlib\ArrayUtils;

final class CategoryManager extends AbstractManager implements CategoryManagerInterface
{
    /**
     * Any compliant category mapper
     * 
     * @var \News\Storage\CategoryMapperInterface
     */
    private $categoryMapper;

    /**
     * Any compliant post mapper
     * 
     * @var \News\Storage\PostMapperInterface
     */
    private $postMapper;

    /**
     * Web page manager to deal with slugs
     * 
     * @var \Cms\Service\WebPageManagerInterface
     */
    private $webPageManager;

    /**
     * Image manager to remove post images when removing a category
     * 
     * @var \Krystal\Image\Tool\ImageManagerInterface
     */
    private $imageManager;

    /**
     * State initialization
     * 
     * @param \News\Storage\CategoryMapperInterface $categoryMapper
     * @param \News\Storage\PostMapperInterface $postMapper
     * @param \Cms\Service\WebPageManagerInterface $webPageManager
     * @param \Krystal\Image\Tool\ImageManager $imageManager
     * @return void
     */
    public function __construct(
        CategoryMapperInterface $categoryMapper, 
        PostMapperInterface $postMapper, 
        WebPageManagerInterface $webPageManager,
        ImageManagerInterface $imageManager
    ){
        $this->categoryMapper = $categoryMapper;
        $this->postMapper = $postMapper;
        $this->webPageManager = $webPageManager;
        $this->imageManager = $imageManager;
    }

    /**
     * Returns a collection of switching URLs
     * 
     * @param string $id Category ID
     * @return array
     */
    public function getSwitchUrls($id)
    {
        return $this->categoryMapper->createSwitchUrls($id, 'News (Categories)', 'News:Category@indexAction');
    }

    /**
     * Fetch all categories with their attached post IDs and names
     * 
     * @param int $excludedId Excluded post IDs
     * @return array
     */
    public function fetchAllWithPosts($excludedId = null)
    {
        return ArrayUtils::arrayDropdown($this->categoryMapper->fetchAllWithPosts($excludedId), 'category', 'id', 'post');
    }

    /**
     * Returns category's last id
     * 
     * @return string
     */
    public function getLastId()
    {
        return $this->categoryMapper->getLastId();
    }

    /**
     * {@inheritDoc}
     */
    protected function toEntity(array $category, $all = true)
    {
        $entity = new VirtualEntity();
        $entity->setId($category['id'], VirtualEntity::FILTER_INT)
               ->setWebPageId($category['web_page_id'], VirtualEntity::FILTER_INT)
               ->setLangId($category['lang_id'], VirtualEntity::FILTER_INT)
               ->setName($category['name'], VirtualEntity::FILTER_HTML)
               ->setSlug($category['slug'], VirtualEntity::FILTER_HTML)
               ->setPostCount(isset($category['post_count']) ? $category['post_count'] : 0, VirtualEntity::FILTER_INT)
               ->setUrl($this->webPageManager->surround($entity->getSlug(), $entity->getLangId()))
               ->setChangeFreq($category['changefreq'])
               ->setPriority($category['priority']);

        if ($all === true) {
            $entity->setTitle($category['title'], VirtualEntity::FILTER_HTML)
                   ->setSeo(isset($category['seo']) ? $category['seo'] : null, VirtualEntity::FILTER_BOOL)
                   ->setDescription($category['description'], VirtualEntity::FILTER_SAFE_TAGS)
                   ->setKeywords($category['keywords'], VirtualEntity::FILTER_HTML)
                   ->setMetaDescription($category['meta_description'], VirtualEntity::FILTER_HTML);
        }

        return $entity;
    }

    /**
     * Fetches categories as a list
     * 
     * @return array
     */
    public function fetchList()
    {
        return ArrayUtils::arrayList($this->categoryMapper->fetchList(), 'id', 'name');
    }

    /**
     * Fetches all category entities
     * 
     * @return array|boolean
     */
    public function fetchAll()
    {
        return $this->prepareResults($this->categoryMapper->fetchAll(), false);
    }

    /**
     * Saves a page
     * 
     * @param array $input
     * @return boolen
     */
    private function savePage(array $input)
    {
        $input['category'] = ArrayUtils::arrayWithout($input['category'], array('slug'));
        return $this->categoryMapper->savePage('News (Categories)', 'News:Category@indexAction', $input['category'], $input['translation']);
    }

    /**
     * Adds a category
     * 
     * @param array $input Raw input data
     * @return boolean Depending on success
     */
    public function add(array $input)
    {
        return $this->savePage($input);
    }

    /**
     * Updates a category
     * 
     * @param array $input Raw input data
     * @return boolean Depending on success
     */
    public function update(array $input)
    {
        return $this->savePage($input);
    }

    /**
     * Fetches category's entity by its associated id
     * 
     * @param string $id
     * @param boolean $withTranslations Whether to fetch translations or not
     * @return \Krystal\Stdlib\VirtualEntity|boolean|array
     */
    public function fetchById($id, $withTranslations)
    {
        if ($withTranslations == true) {
            return $this->prepareResults($this->categoryMapper->fetchById($id, true));
        } else {
            return $this->prepareResult($this->categoryMapper->fetchById($id, false));
        }
    }

    /**
     * Deletes a category by its associated id
     * 
     * @param string $id Category id
     * @return boolean Depending on success
     */
    public function deleteById($id)
    {
        // The order of execution is important
        $this->categoryMapper->deletePage($id);
        $this->imageManager->deleteMany($this->postMapper->fetchAllIdsWithImagesByCategoryId($id));

        // Post IDs to be deleted
        $ids = $this->postMapper->findPostIdsByCategoryId($id);
        return $this->postMapper->deletePage($ids);
    }
}
