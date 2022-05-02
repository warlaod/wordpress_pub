<?php
require 'module/cron_init.php';

$index_url = 'http://www.stardrifter.org/cgi-bin/ref.cgi';

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

// https://www.pornhub.com/embed/ph5efc2d45d1d22

$new_article_urls = ['https://www.pornhub.com/embed/ph5efc2d45d1d22'];

foreach ($new_article_urls as $article_url) {
  echo ("[" . date('Y-m-d H:i:s') . "]" . "------------ post creation started: $article_url ------------\n");

  $article_html = $htmlgetter->get_dynamic($article_url);
  if (!$article_html) {
    continue;
  }

  $patterns = array(
    'title' => '<h1 class="text-white pl-1 pr-2">(.+?)<\/h1>',
    'image_url' => '"thumbnailUrl":"(.+?)"',
    'video_url' => '<a href="(.+?)" class="btn btn-success direct_link"',
    'tags' => '<a href="\/search\/tag\/\d+?" class="badge badge-dark p-2 mr-2 mb-2"><i class="fa fa-tag" aria-hidden="true"><\/i> (.+?)<\/a>'
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
