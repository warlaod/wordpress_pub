<?php

//一つでも取得できていないパターンがあれば次をみる
function is_analysis_correct(array $scrapying_results): bool
{
  $result = true;
  foreach ($scrapying_results as $value) {
    if (!$value) {
      $result = false;
      break;
    }
  }
  return $result;
}
