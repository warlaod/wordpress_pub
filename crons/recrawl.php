<?php
require 'module/cron_init.php';

$htmlgetter = new HTMLGetter(parse_url($index_url, PHP_URL_SCHEME) . '://' . parse_url($index_url, PHP_URL_HOST));
$matcher = new Matcher();
$dbaccessor = new DBAccessor();

$query_results = $wpdb->get_results("SELECT article_url FROM `wdp_post_origin_info` WHERE article_url LIKE ''", "ARRAY_A");

foreach ($query_results as $query_result) {

  $article_url = $query_result['article_url'];

  echo ("[" . date('Y-m-d H:i:s') . "]" . "------------ post recrawl started: $article_url ------------\n");

  $post_id_toDelete = $wpdb->get_results("SELECT post_id FROM " . $wpdb->prefix . "post_origin_info WHERE article_url =\"$article_url\"", "ARRAY_A");

  if (!wp_delete_post($post_id_toDelete[0]['post_id'], true)) {
    echo ("[" . date('Y-m-d H:i:s') . "]" . "Failed to delete post\n");
    continue;
  }

  //ここから下を置き換える

  $article_html = $htmlgetter->get_curl($article_url);
  if (!$article_html) {
    continue;
  }