<?php

require 'analyzer/module/cron_init.php';

// make searchlistt.json


$tag_list = get_tag_lists();
$category_list = get_category_list();
$author_list = get_custom_fileds('author');
$type_list = get_custom_fileds('type');
$group_list = get_custom_fileds('group');
$series_list = get_custom_fileds('series');
$character_list = get_custom_fileds('character');

$search_list = array_merge($tag_list, $category_list, $author_list, $type_list, $group_list, $series_list, $character_list);

$file = fopen("wp-content/themes/luxeritas/searchlist.json", "w");
fwrite($file, json_encode($search_list, JSON_UNESCAPED_UNICODE));
fclose($file);


function get_custom_fileds(string $key): array
{
  global $wpdb;

  $serialized_values = $wpdb->get_col("SELECT DISTINCT meta_value FROM wdp_postmeta WHERE meta_key = '$key'");

  // 配列をデシリアライズし、重複を弾いたuniqueなvaluesを取得する
  $unserialized_values = array();

  if ($key != 'type' && $key != 'group') {
    foreach ($serialized_values as $serialized_value) {
      $unserialized_value = unserialize($serialized_value);
      if (!is_array($unserialized_value)) {
        $serialized_value = array($serialized_value);
      }
      $unserialized_values = array_merge($unserialized_values, $unserialized_value); //デシリアライズしたvalueを配列にmerge
    }
  }else{
    $unserialized_values = array_merge($unserialized_values, $serialized_values); // $type == keyの場合は、serializedする必要がないので、別処理にする
  }
  array_unique($unserialized_values); // 重複を弾く

  $value_list = array();
  foreach ($unserialized_values as $unserialized_value) {
    $value_hash = array(
      "value" => "$key:$unserialized_value",
      "label" => "$unserialized_value ($key)",
    );
    array_push($value_list, $value_hash);
  }
  return $value_list;
}

function get_tag_lists(): array
{
  $tags = get_tags();
  $tag_list = array();
  foreach ($tags as $tag) {
    $tag_hash = array(
      "value" => "tag:$tag->name",
      "label" => "$tag->name (tag)",
    );
    array_push($tag_list, $tag_hash);
  }
  return $tag_list;
}

function get_category_list()
{
  $category_args = array(
    'orderby' => 'name',
    'order' => 'ASC',
  );
  $categories = get_terms('category', $category_args);

  $category_list = array();
  foreach ($categories as $category) {
    $tag_hash = array(
      "value" => "language:$category->name",
      "label" => "$category->name (language)",
    );
    array_push($category_list, $tag_hash);
  }
  return $category_list;
}
