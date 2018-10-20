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

use Krystal\Stdlib\VirtualEntity;
use Cms\Service\AbstractManager;
use News\Storage\PostGalleryMapperInterface;

final class PostGalleryManager extends AbstractManager implements PostGalleryManagerInterface
{
    /**
     * Any compliant post gallery mapper
     * 
     * @var \News\Storage\PostGalleryMapperInterface
     */
    private $postGalleryMapper;

    /**
     * State initialization
     * 
     * @param \News\Storage\PostGalleryMapperInterface $postGalleryMapper
     * @return void
     */
    public function __construct(PostGalleryMapperInterface $postGalleryMapper)
    {
        $this->postGalleryMapper = $postGalleryMapper;
    }

    /**
     * {@inheritDoc}
     */
    protected function toEntity(array $row)
    {
        $entity = new VirtualEntity();
        $entity->setId($row['id'])
               ->setPostId($row['post_id'])
               ->setOrder($row['order'])
               ->setImage($row['image']);

        return $entity;
    }

    /**
     * Deletes an image by its associated ID
     * 
     * @param int $id Image ID
     * @return boolean
     */
    public function deleteById($id)
    {
        return $this->postGalleryMapper->deleteByPk($id);
    }

    /**
     * Returns last ID
     * 
     * @return integer
     */
    public function getLastId()
    {
        return $this->postGalleryMapper->getMaxId();
    }

    /**
     * Fetch images by attached post ID
     * 
     * @param int $postId
     * @return array
     */
    public function fetchAllByPostId($postId)
    {
        return $this->postGalleryMapper->fetchAllByPostId($postId);
    }

    /**
     * Finds image by its associated id
     * 
     * @param int $id Image ID
     * @return mixed
     */
    public function fetchById($id)
    {
        return $this->prepareResult($this->postGalleryMapper->fetchById($id));
    }

    /**
     * Adds an image
     * 
     * @param array $input
     * @return boolean
     */
    public function add(array $input)
    {
        return $this->postGalleryMapper->persist($input);
    }

    /**
     * Updates an image
     * 
     * @param array $input
     * @return boolean
     */
    public function update(array $input)
    {
        return $this->postGalleryMapper->persist($input);
    }
}
