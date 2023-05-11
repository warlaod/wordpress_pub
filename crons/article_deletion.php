<?php
require 'module/cron_init.php';

$htmlgetter = new HTMLGetter(parse_url($index_url, PHP_URL_SCHEME) . '://' . parse_url($index_url, PHP_URL_HOST));
$matcher = new Matcher();
$dbaccessor = new DBAccessor();

// $index_url = '';
// $index_html = $htmlgetter->get_curl($index_url);
// if (!$index_html) {
//   exit;
// }

// $article_urls = $matcher->match_all($index_html, '', $index_url);
// $new_article_urls = $dbaccessor->get_new_article_urls($article_urls);

$query_results = $wpdb->get_results("SELECT article_url FROM `wdp_post_origin_info` WHERE article_url like ''", "ARRAY_A");

foreach ($query_results as $query_result) {

  $article_url = $query_result['article_url'];

  echo ("[" . date('Y-m-d H:i:s') . "]" . "------------ post deletion started: $article_url ------------\n");

  $post_id_toDelete = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "post_origin_info WHERE article_url =\"$article_url\"", "ARRAY_A");

  if (!wp_delete_post($post_id_toDelete[0]['post_id'], true)) {
    echo ("[" . date('Y-m-d H:i:s') . "]" . "Failed to delete post\n");
    continue;
  }
}
