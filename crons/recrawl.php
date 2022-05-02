<?php
require 'module/cron_init.php';

$htmlgetter = new HTMLGetter(parse_url($index_url, PHP_URL_SCHEME) . '://' . parse_url($index_url, PHP_URL_HOST));
$matcher = new Matcher();
$dbaccessor = new DBAccessor();

// $index_url = 'http://erosnoteiri.com/?xml';
// $index_html = $htmlgetter->get_curl($index_url);
// if (!$index_html) {
//   exit;
// }

// $article_urls = $matcher->match_all($index_html, '<link>(.+blog-entry.+)<\/link>', $index_url);
// $new_article_urls = $dbaccessor->get_new_article_urls($article_urls);

$query_results = $wpdb->get_results("SELECT article_url FROM `wdp_post_origin_info` WHERE article_url LIKE '%sexy.erodouga%'", "ARRAY_A");

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

  $patterns = array(
    'title' => '<meta property="og:title" content="(.+?)">',
    'image_url' => '<img src="(.+?)" alt="',
    'video_url' => '<a href="(.+?)" target="_blank" rel="nofollow">',
    'tags' => '<a href="https:\/\/movie\.eroterest\.net\/\?word=.+?">([^a-zA-Z]+)<\/a>'
  );

  $scrapying_results = array(
    'title' => $matcher->match($article_html, $patterns['title'], $article_url),
    'image_url' => $matcher->match($article_html, $patterns['image_url'], $article_url),
    'video_url' => $matcher->match($article_html, $patterns['video_url'], $article_url),
    'tags' => $matcher->match_all($article_html, $patterns['tags'], $article_url),
  );

  if (!$scrapying_results['video_url']) {
    continue;
  }
  if ($dbaccessor->is_the_same_video_exists($scrapying_results['video_url'])) {
    continue;
  }

  $scrapying_results['category'] = $matcher->judge_video_provider_id($scrapying_results['video_url']);

  $video_html = $htmlgetter->get_dynamic($scrapying_results['video_url']);
  if (!$scrapying_results['category'] || !$video_html) {
    continue;
  }
  $scrapying_results['video_sec'] = $matcher->get_video_sec($video_html, $scrapying_results['category'], $scrapying_results['video_url'],);

  //一つでも取得できていないパターンがあれば次をみる
  $result = true;
  foreach ($scrapying_results as $value) {
    if (!$value) {
      $result = false;
      break;
    }
  }
  if (!$result) {
    continue;
  }

  $image_html = $htmlgetter->get_curl($scrapying_results['image_url']);
  if (!$image_html) {
    continue;
  }

  $dbaccessor->create_new_article($scrapying_results, $article_url, $image_html);

  echo ("[" . date('Y-m-d H:i:s') . "]" . "============ post creation finished: $article_url ============\n");
};
