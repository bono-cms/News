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
use Krystal\Stdlib\ArrayUtils;
use Krystal\Db\Filter\FilterableServiceInterface;
use News\Storage\PostMapperInterface;
use News\Storage\CategoryMapperInterface;
use News\Service\TimeBagInterface;
use Cms\Service\AbstractManager;
use Cms\Service\WebPageManagerInterface;

final class PostManager extends AbstractManager implements FilterableServiceInterface
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
     * Time bag to represent a timestamp in different formats
     * 
     * @var \News\Service\TimeBag
     */
    private $timeBag;

    /**
     * State initialization
     * 
     * @param \News\Service\PostMapperInterface $postMapper Any compliant post mapper
     * @param \News\Service\CategoryMapperInterface $categoryMapper Any compliant category mapper
     * @param \News\Service\TimeBag $timeBag Time bag to represent timestamp in different formats
     * @param \News\Service\WebPageManagerInterface $webPageManager Web page manager to handle web pages
     * @param \Krystal\Image\Tool\ImageManager $imageManager Image manager to handle post's cover and its paths
     * @return void
     */
    public function __construct(
        PostMapperInterface $postMapper, 
        CategoryMapperInterface $categoryMapper, 
        TimeBag $timeBag,
        WebPageManagerInterface $webPageManager, 
        ImageManagerInterface $imageManager
    ){
        $this->postMapper = $postMapper;
        $this->categoryMapper = $categoryMapper;
        $this->timeBag = $timeBag;
        $this->webPageManager = $webPageManager;
        $this->imageManager = $imageManager;
    }

    /**
     * Find sequential (i.e previous and next) posts between provided ID
     * 
     * @param string $id Post ID
     * @return array
     */
    public function findSequential($id)
    {
        $output = array();
        $rows = $this->postMapper->findSequential($id);

        foreach ($rows as $index => $row) {
            // Append URL key
            $row['url'] = $this->webPageManager->surround($row['slug'], $row['lang_id']);

            if ($row['id'] > $id) {
                $output['next'] = $row;
            }

            if ($row['id'] < $id) {
                $output['previous'] = $row;
            }
        }

        return $output;
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
        return $this->removeAllById($id);
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

        // Configure image bag
        $imageBag = clone $this->imageManager->getImageBag();
        $imageBag->setId((int) $post['id'])
                 ->setCover($post['cover']);

        if ($full === true) {
            $entity->setTitle($post['title'], PostEntity::FILTER_HTML)
                   ->setFull($post['full'], PostEntity::FILTER_SAFE_TAGS)
                   ->setPermanentUrl('/module/news/post/'.$entity->getId())
                   ->setKeywords($post['keywords'], PostEntity::FILTER_HTML)
                   ->setMetaDescription($post['meta_description'], PostEntity::FILTER_HTML);

            // Attached ones if available
            if (isset($post[PostMapperInterface::PARAM_COLUMN_ATTACHED])) {
                $entity->setAttachedIds(ArrayUtils::arrayList($post[PostMapperInterface::PARAM_COLUMN_ATTACHED], 'id', 'id'));
            }
        }

        $entity->setImageBag($imageBag)
               ->setId($post['id'], PostEntity::FILTER_INT)
               ->setCategoryId($post['category_id'], PostEntity::FILTER_INT)
               ->setLangId($post['lang_id'], PostEntity::FILTER_INT)
               ->setWebPageId($post['web_page_id'], PostEntity::FILTER_INT)
               ->setCategoryName($post['category_name'], PostEntity::FILTER_HTML)
               ->setSlug($post['slug'], PostEntity::FILTER_HTML)
               ->setUrl($this->webPageManager->surround($entity->getSlug(), $entity->getLangId()))
               ->setChangeFreq($post['changefreq'])
               ->setPriority($post['priority'])
               ->setName($post['name'], PostEntity::FILTER_HTML)
               ->setTimestamp($post['timestamp'], PostEntity::FILTER_INT)
               ->setTimeBag($timeBag)
               ->setPublished($post['published'], PostEntity::FILTER_BOOL)
               ->setSeo($post['seo'], PostEntity::FILTER_BOOL)
               ->setFront($post['front'], PostEntity::FILTER_BOOL)
               ->setCover($post['cover'], PostEntity::FILTER_HTML)
               ->setIntro($post['intro'], PostEntity::FILTER_SAFE_TAGS)
               ->setViewCount($post['views'], PostEntity::FILTER_INT);

        return $entity;
    }

    /**
     * Fetches dummy post entity
     * 
     * @param \Krystal\Stdlib\VirtualEntity $config
     * @return \Krystal\Stdlib\VirtualEntity
     */
    public function fetchDummy($config)
    {
        $timeBag = clone $this->timeBag;
        $timeBag->setTimestamp(time());

        $post = new PostEntity();
        $post->setPublished(true)
             ->setSeo(true)
             ->setFront(true)
             ->setTimeBag($timeBag)
             ->setChangeFreq($config->getSitemapFrequency())
             ->setPriority($config->getSitemapPriority());

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
     * Saves a page
     * 
     * @param array $input
     * @return boolean
     */
    private function savePage(array $input)
    {
        // Grab a reference
        $post =& $input['data']['post'];

        // Prepare timestamp
        $post['timestamp'] = (int) strtotime($post['date']);

        // Safe type casting
        $post['category_id'] = (int) $post['category_id'];

        // Remove extra keys if present
        $data = ArrayUtils::arrayWithout($post, array('date', 'remove_cover', 'attached'));

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
        $file = isset($input['files']['file']) ? $input['files']['file'] : false;

        // By default there are 0 views
        $data['views'] = 0;
        $data['cover'] = $file ? $file->getUniqueName() : '';

        // Save the page
        $this->savePage($input);

        // Grab last ID, after saving
        $id = $this->getLastId();

        // Now upload a cover if present
        if ($file) {
            $this->imageManager->upload($id, $file);
        }

        // Insert attached ones, if provided
        $this->postMapper->insertAttached($id, isset($data['attached']) ? $data['attached'] : array());

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
        $post =& $input['data']['post'];
        $file = isset($input['files']['file']) ? $input['files']['file'] : false;

        // Allow to remove a cover, only it case it exists and checkbox was checked
        if (isset($post['remove_cover']) && !empty($post['cover'])) {
            // Remove a cover, but not a dir itself
            $this->imageManager->delete($post['id'], $post['cover']);
            $post['cover'] = '';
        } else {
            // Do the check only in case cover doesn't need to be removed
            if ($file) {
                // If we have a previous cover, then we gotta remove it
                if (!empty($post['cover'])) {
                    // Remove previous one
                    $this->imageManager->delete($post['id'], $post['cover']);
                }

                $this->imageManager->upload($post['id'], $file);

                // Now override cover's value with file's base name we currently have from user's input
                $post['cover'] = $file->getUniqueName();
            }
        }

        // Update the post
        $this->savePage($input);

        // Update attached IDs
        $this->postMapper->updateAttached($post['id'], isset($post['attached']) ? $post['attached'] : array());

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
     * Fetches posts ordering by view count
     * 
     * @param integer $limit Limit of records to be fetched
     * @param int|array $categoryId Optional category ID (or collection) constraint
     * @param bool $rand Whether to order in random order
     * @param bool $front Whether to fetch only front ones
     * @param int $views Minimal view count in order to be considered as mostly viewed
     * @return array
     */
    public function fetchMostlyViewed($limit, $categoryId = null, $rand = false, $front = false, $views = 50)
    {
        $rows = $this->postMapper->fetchMostlyViewed($limit, $categoryId, $rand, $front, $views);

        // If category collection of IDs provided, then drop rows by them
        if (is_array($categoryId)) {
            $rows = ArrayUtils::arrayPartition($rows, 'category_id');

            // Apply hydrator on each row
            foreach ($rows as $id => $row) {
                $rows[$id] = $this->prepareResults($row, false);
            }

            return $rows;
        }

        return $this->prepareResults($rows, false);
    }

    /**
     * Fetches all posts filtered by pagination
     * 
     * @param string $categoryId Filtering category ID
     * @param boolean $published Whether to filter by 'publihsed' attribute
     * @param boolean $front Whether to filter by 'front' attribute
     * @param integer $page Current page number
     * @param integer $itemsPerPage Items per page count
     * @param mixed $sort
     * @return array
     */
    public function fetchAllByPage($categoryId, $published, $front, $page, $itemsPerPage, $sort = null)
    {
        return $this->prepareResults($this->postMapper->fetchAllByPage($categoryId, $published, $front, $page, $itemsPerPage, $sort), false);
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
     * Filters the raw input
     * 
     * @param array|\ArrayAccess $input Raw input data
     * @param integer $page Current page number
     * @param integer $itemsPerPage Items per page to be displayed
     * @param string $sortingColumn Column name to be sorted
     * @param string $desc Whether to sort in DESC order
     * @param array $parameters
     * @return array
     */
    public function filter($input, $page, $itemsPerPage, $sortingColumn, $desc, array $parameters = array())
    {
        return $this->prepareResults($this->postMapper->filter($input, $page, $itemsPerPage, $sortingColumn, $desc, $parameters), false);
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
        return $this->prepareResults($this->postMapper->fetchAllByPage($categoryId, true, true, null, $limit), false);
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
}
