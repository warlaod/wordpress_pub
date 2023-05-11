<?php
require 'module/cron_init.php';

$htmlgetter = new HTMLGetter(parse_url($index_url, PHP_URL_SCHEME) . '://' . parse_url($index_url, PHP_URL_HOST));
$matcher = new Matcher();
$dbaccessor = new DBAccessor();

// $index_url = 'http://examle.com/?xml';
// $index_html = $htmlgetter->get_dynamic($index_url);
// if (!$index_html) {
//   exit;
// }

// $article_urls = $matcher->match_all($index_html, '<link>(.+blog-entry.+)<\/link>', $index_url);
// $new_article_urls = $dbaccessor->get_new_article_urls($article_urls);
$query_results = $wpdb->get_results(
  "
SELECT
    wdp_postmeta.meta_value,
    wdp_posts.post_parent,
    wdp_post_origin_info.article_url
FROM
    wdp_postmeta
JOIN wdp_posts ON wdp_postmeta.post_id = wdp_posts.ID
JOIN wdp_post_origin_info ON wdp_post_origin_info.post_id = wdp_posts.post_parent
WHERE
    wdp_postmeta.meta_key = '_wp_attached_file'
",
  "ARRAY_A"
);

$invalid_image_post_ids = array();
foreach ($query_results as $query_result) {
  $image_url = $query_result['meta_value'];
  if (getenv('ENV') == 'dev') {
    $mime_type = getimagesize("/var/www/html/" . "wp-content/uploads/$image_url")['mime'];
  } else {
    $mime_type = getimagesize("/home/examle/public_html/" . "wp-content/uploads/$image_url")['mime'];
  }
  if (!preg_match('/image\//u', $mime_type)) {
    array_push($invalid_image_post_ids, $query_result['post_parent']);
  }
}

foreach ($invalid_image_post_ids as $post_id) {
  echo ("[" . date('Y-m-d H:i:s') . "]" . "------------ post deletion started: ID: $post_id ------------\n");
  if (!wp_delete_post($post_id, true)) {
    echo ("[" . date('Y-m-d H:i:s') . "]" . "Failed to delete post\n");
    continue;
  }
}
