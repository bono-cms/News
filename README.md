News module
===========
This module facilitates comprehensive news management, offering features for article creation, category organization, and customizable display templates. It enables scheduled publishing, media integration, and user-friendly content editing for seamless news dissemination.

## Template files

News display is controlled by two primary templates located in the active theme's directory: `news-category.phtml` and `news-post.phtml`.

### Category template

The category template must be named `news-category.phtml` and located under your current theme's directory.

Available variables:
 * `$category`: The current category entity
 * `$posts`: An array of post entities within the category

This template renders news categories and their associated post listings. Here's an example:

    <h1><?= $category->getName(); ?></h1> 
    <div class="py-3">
       <?= $category->getDescription(); ?>
    </div>
    
    <?php if (empty($posts)): ?>
    <div class="row">
       <?php foreach ($posts as $post): ?>
       <div class="col-lg-4">
           <h4><?= $post->getName(); ?></h4>
           <div class="py-3 px-2">
              <?= $post->getIntro(); ?>
           </div>
           <p><a class="btn btn-primary" href="<?= $post->getUrl(); ?>">View full</a></p>
       </div>
       <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p>Sorry, no news at the moment</p>
    <?php endif; ?>

The category entity is  called `$category` and has the following methods:

    $category->getDescription(); // Returns description of current category
    $category->getName(); // Returns name of currenct category
    $category->getUrl(); // Returns URL of current category

Pagination can be included via built-in widget. Learn more [here](https://bono.software/docs/pagination).

    <nav>
        <?php $this->loadPartial('pagination'); ?>
    </nav>

### Post template

The post template must be named `news-post.phtml` and located under your current theme's directory.

This template renders individual news posts.

Basic example:

    <article>
       <h1><?= $post->getName(); ?></h1>
       <?= $post->getFull(); ?>
    </article>
    
    <p>Views: <?= $post->getViewCount(); ?></p>

### Post template with image gallery

Sometimes your post might require a dedicated image gallery. Luckily, this feature comes right out of the box.

Basic example:

    <article>
       <h1><?= $post->getName(); ?></h1>
       <?= $post->getFull(); ?>
    </article>
     
    <?php if ($post->hasGallery()): ?>
    <div class="row my-3">
	    <?php foreach ($post->getGallery() as $item): ?>
	    <div class="col-lg-4">
	       <img src="<?= $item->getImageUrl('500x500'); ?>" class="img-fluid" alt="<?=$post->getName(); ?>" />
	    </div>
	    <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <p>Views: <?= $post->getViewCount()(); ?></p>

### Available methods

Each post entity has the following methods:

    $post->getName(); // Returns name of current post
    $post->getTitle(); // Returns title of current post
    $post->getIntro(); // Returns post's introduction text
    $post->getFull(); // Returns post's full text
    $post->getUrl(); // Returns post full URL
    $post->hasCover(); // Returns true, if post has a cover image
    $post->hasGallery(); // Returns true, if post has at least one gallery image uploaded
    $post->getGallery(); // Returns an array of gallery entities
    $post->getImageUrl('500x500'); // Returns full URL to cover image
    $post->getViewCount(); // Returns view count
    $post->getCategoryName(); // Returns category name, if available
    $post->hasGallery(); // True, if there's at least one uploaded gallery image

To display a time, you can use the following methods:

    $post->getTimeBag()->getPostFormat(); // Returns formatted time for post's template
    $post->getTimeBag()->getListFormat(); // Returns formatted time for category's template

## Global methods

You'd have a global `$news` object available across all your templates. It has the following methods:

    $news->getSequential($postId); // Returns previous and next post entities
    $news->getAllByCategoryId($categoryId, $limit); // Returns posts within a category
    $news->getRecent($limit, $categoryId = null); // Returns recent posts
    $news->getRandom($limit, $categoryId = null); // Returns random post entity