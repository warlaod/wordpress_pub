ALTER TABLE
  wp_commentmeta RENAME TO wdp_commentmeta;

ALTER TABLE
  wp_comments RENAME TO wdp_comments;

ALTER TABLE
  wp_links RENAME TO wdp_links;

ALTER TABLE
  wp_options RENAME TO wdp_options;

ALTER TABLE
  wp_postmeta RENAME TO wdp_postmeta;

ALTER TABLE
  wp_posts RENAME TO wdp_posts;

ALTER TABLE
  wp_termmeta RENAME TO wdp_termmeta;

ALTER TABLE
  wp_terms RENAME TO wdp_terms;

ALTER TABLE
  wp_term_relationships RENAME TO wdp_term_relationships;

ALTER TABLE
  wp_term_taxonomy RENAME TO wdp_term_taxonomy;

ALTER TABLE
  wp_usermeta RENAME TO wdp_usermeta;

ALTER TABLE
  wp_users RENAME TO wdp_users;

DELETE FROM
  `wdp_postmeta`;

DELETE FROM
  `wdp_posts`;

DELETE FROM
  `wdp_postmeta`;

DROP TABLE IF EXISTS `wdp_post_origin_info`;

DROP TABLE IF EXISTS `wdp_crawled_articles`;

DELETE FROM
  `wdp_termmeta`;

DELETE FROM
  `wdp_terms`;

DELETE FROM
  `wdp_term_relationships`;

DELETE FROM
  `wdp_term_taxonomy`;

CREATE TABLE `wdp_crawled_articles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `article_url` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `article_html` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_id` bigint(20) unsigned,
  `analyzed` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_url` (`article_url`),
  KEY `post_id` (`post_id`),
  CONSTRAINT `wdp_crawled_articles_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `wdp_posts` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_520_ci;

INSERT INTO
  `wdp_terms` (`term_id`, `name`, `slug`, `term_group`)
VALUES
  (166, 'Indonesian', 'indonesian', 0),
  (165, 'German', 'german', 0),
  (164, 'English', 'english', 0),
  (163, 'Spanish', 'spanish', 0),
  (162, 'French', 'french', 0),
  (161, 'Italian', 'italian', 0),
  (160, 'Portuguese', 'portuguese', 0),
  (159, 'Vietnamese', 'vietnamese', 0),
  (
    158,
    'Russian',
    'russian',
    0
  ),
  (157, 'Thai', 'thai', 0),
  (156, 'Korean', 'korean', 0),
  (155, 'Chinese', 'chinese', 0),
  (154, 'Japanese', 'japanese', 0),
  (153, 'N/A', 'n-a', 0);

INSERT INTO
  `wdp_term_taxonomy` (
    `term_taxonomy_id`,
    `term_id`,
    `taxonomy`,
    `description`,
    `parent`,
    `count`
  )
VALUES
  (153, 153, 'category', '', 0, 0),
  (154, 154, 'category', '', 0, 0),
  (155, 155, 'category', '', 0, 0),
  (156, 156, 'category', '', 0, 0),
  (157, 157, 'category', '', 0, 0),
  (158, 158, 'category', '', 0, 0),
  (159, 159, 'category', '', 0, 0),
  (160, 160, 'category', '', 0, 0),
  (161, 161, 'category', '', 0, 0),
  (162, 162, 'category', '', 0, 0),
  (163, 163, 'category', '', 0, 0),
  (164, 164, 'category', '', 0, 0),
  (165, 165, 'category', '', 0, 0),
  (166, 166, 'category', '', 0, 0);
