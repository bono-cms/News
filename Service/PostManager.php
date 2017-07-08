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

use Krystal\Image\Tool\ImageManagerInterface;
use Krystal\Security\Filter;
use Krystal\Stdlib\ArrayUtils;
use News\Storage\PostMapperInterface;
use News\Storage\CategoryMapperInterface;
use News\Service\TimeBagInterface;
use Cms\Service\AbstractManager;
use Cms\Service\WebPageManagerInterface;
use Cms\Service\HistoryManagerInterface;

final class PostManager extends AbstractManager implements PostManagerInterface
{
    /**
     * Any-compliant post mapper
     * 
     * @var \News\Service\PostMapperInterface
     */
    private $postMapper;

    /**
     * Any-compliant category mapper
     * 
     * @var \News\Service\CategoryMapperInterface
     */
    private $categoryMapper;

    /**
     * Image manager for posts
     * 
     * @var \Krystal\Image\Tool\ImageManager
     */
    private $imageManager;

    /**
     * URL manager is responsible for slugs
     * 
     * @var \Cms\Service\WebPageManagerInterface
     */
    private $webPageManager;

    /**
     * History Manager to keep track
     * 
     * @var \Cms\Service\HistoryManagerInterface
     */
    private $historyManager;

    /**
     * Time bag to represent a timestamp in different formats
     * 
     * @var \News\Service\TimeBagInterface
     */
    private $timeBag;

    /**
     * State initialization
     * 
     * @param \News\Service\PostMapperInterface $postMapper Any compliant post mapper
     * @param \News\Service\CategoryMapperInterface $categoryMapper Any compliant category mapper
     * @param \News\Service\TimeBagInterface $timeBag Time bag to represent timestamp in different formats
     * @param \News\Service\WebPageManagerInterface $webPageManager Web page manager to handle web pages
     * @param \Krystal\Image\Tool\ImageManager $imageManager Image manager to handle post's cover and its paths
     * @param \Cms\Service\HistoryManagerInterface $historyManager History manager to keep track of latest actions
     * @return void
     */
    public function __construct(
        PostMapperInterface $postMapper, 
        CategoryMapperInterface $categoryMapper, 
        TimeBagInterface $timeBag,
        WebPageManagerInterface $webPageManager, 
        ImageManagerInterface $imageManager,
        HistoryManagerInterface $historyManager
    ){
        $this->postMapper = $postMapper;
        $this->categoryMapper = $categoryMapper;
        $this->timeBag = $timeBag;
        $this->webPageManager = $webPageManager;
        $this->imageManager = $imageManager;
        $this->historyManager = $historyManager;
    }

    /**
     * Returns a collection of switching URLs
     * 
     * @param string $id Post ID
     * @return array
     */
    public function getSwitchUrls($id)
    {
        return $this->postMapper->createSwitchUrls($id, 'News (Posts)', 'News:Post@indexAction');
    }

    /**
     * Returns post breadcrumb collection for view
     * 
     * @param \News\Service\PostEntity $post
     * @return array
     */
    public function getBreadcrumbs(PostEntity $post)
    {
        $category = $this->categoryMapper->fetchById($post->getCategoryId(), false);
        $categoryWebPage = $this->webPageManager->fetchById($category['web_page_id']);

        return array(
            array(
                'name' => $category['name'],
                'link' => $this->webPageManager->surround($categoryWebPage['slug'], $categoryWebPage['lang_id']),
            ),
            array(
                'name' => $post->getName(),
                'link' => '#',
            )
        );
    }

    /**
     * Increments view count by post id
     * 
     * @param string $id
     * @return boolean
     */
    public function incrementViewCount($id)
    {
        return $this->postMapper->incrementViewCount($id);
    }

    /**
     * Update settings
     * 
     * @param array $settings
     * @return boolean
     */
    public function updateSettings(array $settings)
    {
        return $this->postMapper->updateSettings($settings);
    }

    /**
     * Delete posts by their associated ids
     * 
     * @param array $ids
     * @return boolean
     */
    public function deleteByIds(array $ids)
    {
        foreach ($ids as $id) {
            if (!$this->removeAllById($id)) {
                return false;
            }
        }

        #$this->track('%s posts have been removed', count($ids));
        return true;
    }

    /**
     * Deletes a post by its associated id
     * 
     * @param string $id Post's id
     * @return boolean
     */
    public function deleteById($id)
    {
        // Grab post's title before we remove it
        #$title = Filter::escape($this->postMapper->fetchNameById($id));

        if ($this->removeAllById($id)) {
            #$this->track('Post "%s" has been removed', $title);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Completely removes a post by its associated id
     * 
     * @param string $id Post's id
     * @return boolean
     */
    private function removeAllById($id)
    {
        return $this->postMapper->deletePage($id) && $this->imageManager->delete($id);
    }

    /**
     * Returns prepared paginator's instance
     * 
     * @return \Krystal\Paginate\Paginator
     */
    public function getPaginator()
    {
        return $this->postMapper->getPaginator();
    }

    /**
     * {@inheritDoc}
     */
    protected function toEntity(array $post, $full = true)
    {
        $entity = new PostEntity(false);

        // Configure time
        $timeBag = clone $this->timeBag;
        $timeBag->setTimestamp((int) $post['timestamp']);

        if ($full === true) {
            // Configure image bag
            $imageBag = clone $this->imageManager->getImageBag();
            $imageBag->setId((int) $post['id'])
                     ->setCover($post['cover']);

            $entity->setImageBag($imageBag)
                   ->setCategoryId($post['category_id'], PostEntity::FILTER_INT)
                   ->setTitle($post['title'], PostEntity::FILTER_HTML)
                   ->setFull($post['full'], PostEntity::FILTER_SAFE_TAGS)
                   ->setPermanentUrl('/module/news/post/'.$entity->getId())
                   ->setKeywords($post['keywords'], PostEntity::FILTER_HTML)
                   ->setMetaDescription($post['meta_description'], PostEntity::FILTER_HTML)
                   ->setViewCount($post['views'], PostEntity::FILTER_INT);

            // Attached ones if available
            if (isset($post[PostMapperInterface::PARAM_COLUMN_ATTACHED])) {
                $entity->setAttachedIds(ArrayUtils::arrayList($post[PostMapperInterface::PARAM_COLUMN_ATTACHED], 'id', 'id'));
            }
        }

        $entity->setId($post['id'], PostEntity::FILTER_INT)
               ->setLangId($post['lang_id'], PostEntity::FILTER_INT)
               ->setWebPageId($post['web_page_id'], PostEntity::FILTER_INT)
               ->setCategoryName($post['category_name'], PostEntity::FILTER_HTML)
               ->setSlug($post['slug'], PostEntity::FILTER_HTML)
               ->setUrl($this->webPageManager->surround($entity->getSlug(), $entity->getLangId()))
               ->setName($post['name'], PostEntity::FILTER_HTML)
               ->setTimestamp($post['timestamp'], PostEntity::FILTER_INT)
               ->setTimeBag($timeBag)
               ->setPublished($post['published'], PostEntity::FILTER_BOOL)
               ->setSeo($post['seo'], PostEntity::FILTER_BOOL)
               ->setCover($post['cover'], PostEntity::FILTER_HTML)
               ->setIntro($post['intro'], PostEntity::FILTER_SAFE_TAGS);

        return $entity;
    }

    /**
     * Fetches dummy post entity
     * 
     * @return \Krystal\Stdlib\VirtualEntity
     */
    public function fetchDummy()
    {
        $timeBag = clone $this->timeBag;
        $timeBag->setTimestamp(time());

        $post = new PostEntity();
        $post->setPublished(true)
             ->setSeo(true)
             ->setTimeBag($timeBag);

        return $post;
    }

    /**
     * Returns an id of latest post
     * 
     * @return integer
     */
    public function getLastId()
    {
        return $this->postMapper->getLastId();
    }

    /**
     * Prepares raw input, before sending to the mapper
     * 
     * @param array $input Raw input data
     * @return array
     */
    private function prepareInput(array $input)
    {
        $data =& $input['data']['post'];
        $data['timestamp'] = strtotime($data['date']);

        // Take a slug from a name if empty
        if (empty($data['slug'])) {
            $data['slug'] = $data['name'];
        }

        // Take empty name from title
        if (empty($data['title'])) {
            $data['title'] = $data['name'];
        }

        $data['slug'] = $this->webPageManager->sluggify($data['slug']);

        // Safe type casting
        $data['web_page_id'] = (int) $data['web_page_id'];
        $data['category_id'] = (int) $data['category_id'];

        return $input;
    }

    /**
     * Saves a page
     * 
     * @param array $input
     * @return boolean
     */
    private function savePage(array $input)
    {
        // Grab a reference
        $data =& $input['data']['post'];

        // Prepare timestamp
        $data['timestamp'] = (int) strtotime($data['date']);

        // Safe type casting
        $data['category_id'] = (int) $data['category_id'];

        // Remove extra keys if present
        $data = ArrayUtils::arrayWithout($data, array('date', 'slug', 'remove_cover', 'attached'));

        return $this->postMapper->savePage('News (Posts)', 'News:Post@indexAction', $data, $input['data']['translation']);
    }

    /**
     * Adds a post
     * 
     * @param array $input Raw input data
     * @return boolean Depending on success
     */
    public function add(array $input)
    {
        $data =& $input['data']['post'];

        // By default there are 0 views
        $data['views'] = 0;

        // Handle cover
        if (!empty($input['files']['file'])) {
            $file =& $input['files']['file'];
            $this->filterFileInput($file);

            $data['cover'] = $file[0]->getName();
        } else {
            $data['cover'] = '';
        }

        // Save the page
        $this->savePage($input);

        // Now upload a cover if present
        if (!empty($input['files'])) {
            $file =& $input['files']['file'];
            $this->imageManager->upload($this->getLastId(), $file);
        }

        #$this->track('New post "%s" has been created', $data['name']);
        return true;
    }

    /**
     * Updates a post
     * 
     * @param array $input Raw input data
     * @return boolean Depending on success
     */
    public function update(array $input)
    {
        //$form = $this->prepareInput($input);
        $post =& $input['data']['post'];

        // Allow to remove a cover, only it case it exists and checkbox was checked
        if (isset($post['remove_cover']) && !empty($post['cover'])) {
            // Remove a cover, but not a dir itself
            $this->imageManager->delete($post['id'], $post['cover']);
            $post['cover'] = '';
        } else {
            // Do the check only in case cover doesn't need to be removed
            if (!empty($input['files'])) {
                // If we have a previous cover, then we gotta remove it
                if (!empty($post['cover'])) {
                    // Remove previous one
                    $this->imageManager->delete($post['id'], $post['cover']);
                }

                $file = $input['files']['file'];

                // Before we start uploading a file, we need to filter its base name
                $this->filterFileInput($file);
                $this->imageManager->upload($post['id'], $file);

                // Now override cover's value with file's base name we currently have from user's input
                $post['cover'] = $file[0]->getName();
            }
        }

        $this->savePage($input);
        
        // And finally now just track it
        #$this->track('Post "%s" has been updated', $post['name']);
        return true;
    }

    /**
     * Fetches post entity by its associated id
     * 
     * @param string $id Post ID
     * @param boolean $withAttached Whether to grab attached entities
     * @param boolean $withTranslations Whether to include translations as well
     * @return \News\Service\PostEntity|boolean|array
     */
    public function fetchById($id, $withAttached, $withTranslations)
    {
        if ($withTranslations) {
            return $this->prepareResults($this->postMapper->fetchById($id, true));

        } else {
            $entity = $this->prepareResult($this->postMapper->fetchById($id));

            if ($entity !== false) {
                if ($withAttached === true) {
                    $rows = $this->postMapper->fetchByIds($entity->getAttachedIds());
                    $entity->setAttachedPosts($this->prepareResults($rows, false));
                }

                return $entity;
            } else {
                return false;
            }
        }
    }

    /**
     * Fetches mostly viewed entities
     * 
     * @param integer $limit
     * @return array
     */
    public function fetchMostlyViewed($limit)
    {
        return $this->prepareResults($this->postMapper->fetchMostlyViewed($limit), false);
    }

    /**
     * Fetches all posts filtered by pagination
     * 
     * @param string $categoryId Filtering category ID
     * @param boolean $published Whether to filter by 'publihsed' attribute
     * @param integer $page Current page number
     * @param integer $itemsPerPage Items per page count
     * @return array
     */
    public function fetchAllByPage($categoryId, $published, $page, $itemsPerPage)
    {
        return $this->prepareResults($this->postMapper->fetchAllByPage($categoryId, $published, $page, $itemsPerPage), false);
    }

    /**
     * Fetch recent news post entities
     * 
     * @param string $categoryId Optional category ID filter
     * @param integer $limit Limit of rows to be returned
     * @return array
     */
    public function fetchRecent($limit, $categoryId = null)
    {
        return $this->prepareResults($this->postMapper->fetchRecent($limit, $categoryId), false);
    }

    /**
     * Fetches all published post bags associated with category id
     * 
     * @param string $categoryId
     * @param integer $limit Amount of returned posts
     * @return array
     */
    public function fetchAllPublishedByCategoryId($categoryId, $limit)
    {
        return $this->prepareResults($this->postMapper->fetchAllByPage($categoryId, true, null, $limit), false);
    }

    /**
     * Fetches random published posts
     * 
     * @param integer $amount
     * @param string $categoryId Optionally can be filtered by category id
     * @return array
     */
    public function fetchRandomPublished($amount, $categoryId = null)
    {
        return $this->prepareResults($this->postMapper->fetchRandomPublished($amount, $categoryId), false);
    }

    /**
     * Fetches all posts
     * 
     * @return array
     */
    public function fetchAll()
    {
        return $this->postMapper->fetchAll();
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
