<?php
class Matcher
{

  public static function match_all(string $html, string $pattern, string $url): array
  {
    if (preg_match_all("/$pattern/u", $html, $matched_array, PREG_PATTERN_ORDER)) {
      return $matched_array[1];
    } else {
      return array();
    }
  }

  public static function match(string $html, string $pattern, string $url): string
  {
    if (preg_match("/$pattern/u", $html, $matched)) {
      return $matched[1];
    } else {
      return '';
    }
  }

  // httpsにしないとcurlできない場合は置換する
  public function modify_valid_url(string $url): string
  {
    $modified_url = preg_match('/http:|https:/u', $url) ? $url : 'https:' . $url;
    return $modified_url;
  }
}
