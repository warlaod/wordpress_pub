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

CREATE TABLE IF NOT EXISTS `wdp_post_origin_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL,
  `article_url` text COLLATE utf8_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_uniq` (`article_url`(255)),
  KEY `linked_with_post_id_deletion` (`post_id`),
  CONSTRAINT `linked_with_post_id_deletion` FOREIGN KEY (`post_id`) REFERENCES `wdp_posts` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_unicode_520_ci;

CREATE TABLE `wdp_crawled_articles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `article_url` varchar(255) COLLATE utf8_unicode_520_ci NOT NULL,
  `article_html` longtext COLLATE utf8_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_url` (`article_url`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_unicode_520_ci;

INSERT INTO
  `wdp_terms` (`term_id`, `name`, `slug`, `term_group`)
VALUES
  (166, 'Bahasa Indonesia', 'bahasa-indonesia', 0),
  (165, 'Deutsch', 'deutsch', 0),
  (164, 'English', 'english', 0),
  (163, 'Español', 'espanol', 0),
  (162, 'Français', 'francais', 0),
  (161, 'Italiano', 'italiano', 0),
  (160, 'Português', 'portugues', 0),
  (159, 'Tiếng Việt', 'tieng-viet', 0),
  (
    158,
    'Русский',
    '%d1%80%d1%83%d1%81%d1%81%d0%ba%d0%b8%d0%b9',
    0
  ),
  (157, 'ไทย', '%e0%b9%84%e0%b8%97%e0%b8%a2', 0),
  (156, '한국어', '%ed%95%9c%ea%b5%ad%ec%96%b4', 0),
  (155, '中文', '%e4%b8%ad%e6%96%87', 0),
  (154, '日本語', '%e6%97%a5%e6%9c%ac%e8%aa%9e', 0),
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
