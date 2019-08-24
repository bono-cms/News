
/* Categories */
DROP TABLE IF EXISTS `bono_module_news_categories`;
CREATE TABLE `bono_module_news_categories` (
	`id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`seo` varchar(1) NOT NULL COMMENT 'Whether SEO is enabled'
) ENGINE = InnoDB DEFAULT CHARSET = UTF8;

DROP TABLE IF EXISTS `bono_module_news_categories_translations`;
CREATE TABLE `bono_module_news_categories_translations` (
    `id` INT NOT NULL,
    `lang_id` INT NOT NULL,
    `web_page_id` INT NOT NULL,
    `name` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
    `description` LONGTEXT NOT NULL,
    `keywords` TEXT NOT NULL COMMENT 'Keywords for search engines',
    `meta_description` TEXT NOT NULL,

    FOREIGN KEY (id) REFERENCES bono_module_news_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (lang_id) REFERENCES bono_module_cms_languages(id) ON DELETE CASCADE,
    FOREIGN KEY (web_page_id) REFERENCES bono_module_cms_webpages(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = UTF8;

/* Posts */
DROP TABLE IF EXISTS `bono_module_news_posts`;
CREATE TABLE `bono_module_news_posts` (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `category_id` INT NOT NULL,
    `published` varchar(1) NOT NULL,
    `seo` varchar(1) NOT NULL,
    `timestamp` INT(10) NOT NULL,
    `cover` varchar(255) NOT NULL,
    `views` INT NOT NULL,
    `front` BOOLEAN NOT NULL COMMENT 'Whether this post must be front',

    FOREIGN KEY (category_id) REFERENCES bono_module_news_categories(id) ON DELETE CASCADE,
) ENGINE = InnoDB DEFAULT CHARSET = UTF8;

DROP TABLE IF EXISTS `bono_module_news_posts_translations`;
CREATE TABLE `bono_module_news_posts_translations` (
    `id` INT NOT NULL,
	`lang_id` INT NOT NULL,
	`web_page_id` INT NOT NULL,
	`name` varchar(255) NOT NULL,
	`title` varchar(255) NOT NULL,
	`intro` LONGTEXT NOT NULL,
	`full` LONGTEXT NOT NULL,
	`keywords` TEXT NOT NULL,
	`meta_description` TEXT NOT NULL,

    FOREIGN KEY (id) REFERENCES bono_module_news_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (lang_id) REFERENCES bono_module_cms_languages(id) ON DELETE CASCADE,
    FOREIGN KEY (web_page_id) REFERENCES bono_module_cms_webpages(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = UTF8;


DROP TABLE IF EXISTS `bono_module_news_posts_attached`;
CREATE TABLE `bono_module_news_posts_attached` (
    `master_id` INT NOT NULL COMMENT 'Primary Post ID',
    `slave_id` INT NOT NULL COMMENT 'Attached post ID',

    FOREIGN KEY (master_id) REFERENCES bono_module_news_posts(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = UTF8;

/* Post gallery */
DROP TABLE IF EXISTS `bono_module_news_posts_gallery`;
CREATE TABLE `bono_module_news_posts_gallery` (
    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `post_id` INT NOT NULL COMMENT 'Attached post ID',
    `order` INT NOT NULL COMMENT 'Sorting order',
    `image` varchar(255) NOT NULL COMMENT 'Image file',

    FOREIGN KEY (post_id) REFERENCES bono_module_news_posts(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = UTF8;
