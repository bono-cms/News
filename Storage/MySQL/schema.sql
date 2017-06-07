
DROP TABLE IF EXISTS `bono_module_news_categories`;
CREATE TABLE `bono_module_news_categories` (

	`id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`lang_id` INT NOT NULL,
	`web_page_id` INT NOT NULL,
	`name` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
	`description` LONGTEXT NOT NULL,
	`seo` varchar(1) NOT NULL COMMENT 'Whether SEO is enabled',
	`keywords` TEXT NOT NULL COMMENT 'Keywords for search engines',
	`meta_description` TEXT NOT NULL

) DEFAULT CHARSET = UTF8;


DROP TABLE IF EXISTS `bono_module_news_posts`;
CREATE TABLE `bono_module_news_posts` (

	`id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`lang_id` INT NOT NULL,
	`web_page_id` INT NOT NULL,
	`category_id` INT NOT NULL,
	`published` varchar(1) NOT NULL,
	`seo` varchar(1) NOT NULL,
	`name` varchar(255) NOT NULL,
	`title` varchar(255) NOT NULL,
	`intro` LONGTEXT NOT NULL,
	`full` LONGTEXT NOT NULL,
	`timestamp` INT(10) NOT NULL,
	`keywords` TEXT NOT NULL,
	`meta_description` TEXT NOT NULL,
	`cover` varchar(255) NOT NULL,
	`views` INT NOT NULL

) DEFAULT CHARSET = UTF8;


DROP TABLE IF EXISTS `bono_module_news_posts_attached`;
CREATE TABLE `bono_module_news_posts_attached` (
    `master_id` INT NOT NULL COMMENT 'Post ID',
    `slave_id` INT NOT NULL COMMENT 'Attached post ID'
) DEFAULT CHARSET = UTF8;
