<?php

require_once 'module/cron_init.php';

$logger = load_logger();
$index_url = 'https://esacs.com/?page=2';
$domain_url = parse_url($index_url, PHP_URL_SCHEME) . '://' . parse_url($index_url, PHP_URL_HOST);

$dbaccessor = new DBAccessor();

$crawled_articles = $dbaccessor->get_crawled_articles($domain_url);

foreach ($crawled_articles as $article) {
  $crawled_id = $article->id;
  $article_url = $article->article_url;
  $article_html = $article->article_html;

  $logger->info("$crawled_id : $article_url");

  $patterns = array(
    'title' => '<a href="\/reader\/\d+\.html".*?>(.+?)<\/a><\/h1>',
    'type' => '<td id="type">(.+?)<\/td>',
    'tags' => '<a href="\/tag\/.+?\.html">(.+?)<\/a><\/li>',
    'author' => '<a href="\/artist\/.+?\.html">(.+?)<\/a>',
    'group' => '<a href="\/group\/.+?">(.+?)<\/a>',
    'language' => '<a href="\/index-.+?\.html">(.+?)<\/a>',
    'series' => '<a href="\/series\/.+?\.html">(.+?)<\/a>',
    'character' => '<a href="\/character\/.+?\.html">(.+?)<\/a>',
  );

  $scrapying_results = array(
    'title' => Matcher::match($article_html, $patterns['title'], $article_url),
    'language' => get_language_match_result($article_html, $patterns['language'], $article_url),
    'type' => Matcher::match($article_html, $patterns['type'], $article_url),
    'tags' => Matcher::match_all($article_html, $patterns['tags'], $article_url),
    'author' => Matcher::match_all($article_html, $patterns['author'], $article_url),
    'group' => Matcher::match($article_html, $patterns['group'], $article_url),
    'series' => Matcher::match_all($article_html, $patterns['series'], $article_url),
    'character' => Matcher::match_all($article_html, $patterns['character'], $article_url),
    'post_date' => get_date_match_result($article_html, $article_url)
  );

  // タグの加工
  foreach ($scrapying_results['tags'] as &$tag) {
    $tag = urldecode($tag);
  }

  // 先頭文字を大文字にする
  transform_large_capital_letter($scrapying_results);

  if (is_failed_to_get_essential_results($scrapying_results)) {
    $dbaccessor->update_wdp_crawled_articles_analyzed(DBAccessor::ANALYZED_STATUS['FAILURE'], $crawled_id);
    continue;
  }

  $post_id = $dbaccessor->create_new_article($scrapying_results, $crawled_id);
  if ($post_id) {
    $dbaccessor->update_wdp_crawled_articles_analyzed(DBAccessor::ANALYZED_STATUS['SUCCESS'], $crawled_id, $post_id);
  } else {
    $dbaccessor->update_wdp_crawled_articles_analyzed(DBAccessor::ANALYZED_STATUS['FAILURE'], $crawled_id);
  }
};


// 先頭文字を大文字に
function transform_large_capital_letter(array &$scrapying_results)
{
  foreach (['series', 'tags', 'author', 'character'] as $key) {
    foreach ($scrapying_results[$key] as &$value) {
      $value = ucwords($value);
    }
  }
  $scrapying_results['group'] = ucwords($scrapying_results['group']);
  $scrapying_results['type'] = ucwords($scrapying_results['type']);
}

// 必須項目がうまく取得できなかったかどうかを判定する
function is_failed_to_get_essential_results(array $scrapying_results)
{
  $keys = ['title', 'type', 'language', 'post_date'];
  foreach ($keys as $key) {
    if (empty($scrapying_results[$key])) {
      $logger->warning("Unable to find [$key]");
      return true;
    }
  }
  return false;
}

function get_language_match_result(string $article_html, string $pattern, string $article_url): int
{
  $result = Matcher::match($article_html, $pattern, $article_url);
  if (empty($result)) {
    $result =  Matcher::match($article_html, '<td id="language">(.+?)<\/td>', $article_url);
  }

  $result = category_mapping($result);

  // languageを文字列からIDに変換する
  $result = get_cat_ID($result);

  return $result;
}

// 取得した言語解析結果をカテゴリーの名前に直す
function category_mapping($result)
{
  $mappings = array(
    'Bahasa Indonesia' => 'Indonesian',
    'Deutsch' => 'German',
    'English' => 'English',
    'Español' => 'Spanish',
    'Français' => 'French',
    'Italiano' => 'Italian',
    'Português' => 'Portuguese',
    'tiếng việt' => 'Vietnamese',
    'Русский' => 'Russian',
    'ไทย' => 'Thai',
    '한국어' => 'Korean',
    '中文' => 'Chinese',
    '日本語' => 'Japanese',
    'N/A' => 'N/A'
  );

  return $mappings[$result];
}

function get_date_match_result($article_html, $article_url)
{
  // 日本語ページから結果を取得する
  $pattern = '<span class="date">(\d+?)年(\d+?)月(\d+?)日 (.+?)<\/span>';
  if (preg_match_all("/$pattern/u", $article_html, $matches, PREG_PATTERN_ORDER)) {
    $year = $matches[1][0];
    $month = $matches[2][0];
    $day = $matches[3][0];
    $hour_minute = $matches[4][0];
    $time = strtotime("$year-$month-$day $hour_minute");
    $result = date('Y-m-d H:i:s', $time);
  } else {
    // 日本語ページから取れなかったら、英語ページから取得
    $result = date('Y-m-d H:i:s', strtotime(Matcher::match($article_html, '<span class="date">(.+?)<\/span>', $article_url)));
  }
  return $result;
}
