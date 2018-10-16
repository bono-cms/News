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
use Cms\Service\HistoryManagerInterface;
use News\Storage\CategoryMapperInterface;
use News\Storage\PostMapperInterface;
use Krystal\Stdlib\VirtualEntity;
use Krystal\Security\Filter;
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
     * History manager to track activity
     * 
     * @var \Cms\Service\HistoryManagerInterface
     */
    private $historyManager;

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
     * @param \Cms\Service\HistoryManagerInterface $historyManager
     * @param \Krystal\Image\Tool\ImageManager $imageManager
     * @return void
     */
    public function __construct(
        CategoryMapperInterface $categoryMapper, 
        PostMapperInterface $postMapper, 
        WebPageManagerInterface $webPageManager,
        HistoryManagerInterface $historyManager,
        ImageManagerInterface $imageManager
    ){
        $this->categoryMapper = $categoryMapper;
        $this->postMapper = $postMapper;
        $this->webPageManager = $webPageManager;
        $this->historyManager = $historyManager;
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
               ->setUrl($this->webPageManager->surround($entity->getSlug(), $entity->getLangId()));

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
        #$this->track('Category "%s" has been created', $category['name']);
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
        #$this->track('Category "%s" has been updated', $category['name']);
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
     * @param string $id Category's id
     * @return boolean Depending on success
     */
    public function deleteById($id)
    {
        // Grab category's title before we remove it
        #$title = Filter::escape($this->categoryMapper->fetchNameById($id));

        if ($this->removeAllById($id)) {
            #$this->track('Category "%s" has been removed', $title);
            return true;

        } else {
            return false;
        }
    }

    /**
     * Removes a category by it associated id
     * 
     * @param string $id Category's id
     * @return boolean
     */
    private function removeAllById($id)
    {
        // The order of execution is important
        $this->categoryMapper->deletePage($id);
        $this->removeAllPostImagesByCategoryId($id);

        // Post IDs to be deleted
        $ids = $this->postMapper->findPostIdsByCategoryId($id);
        return $this->postMapper->deletePage($ids);
    }

    /**
     * Removes all post images associated with category id
     * 
     * @param string $id Category's id
     * @return boolean
     */
    private function removeAllPostImagesByCategoryId($id)
    {
        $ids = $this->postMapper->fetchAllIdsWithImagesByCategoryId($id);

        // Do the work, in case there's at least one id
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $this->imageManager->delete($id);
            }
        }

        return true;
    }

    /**
     * Tracks activity
     * 
     * @param string $message
     * @param string $placeholder
     * @return boolean
     */
    private function track($message, $placeholder)
    {
        return $this->historyManager->write('News', $message, $placeholder);
    }
}
