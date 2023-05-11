<?php

require_once 'filemanager.php';
require_once 's3manager.php';
require_once 'logger.php';
class DBAccessor
{
  public const ANALYZED_STATUS = array(
    'SUCCESS' => 1,
    'UNANALYZED' => 0,
    'FAILURE' => -1
  );

  private $logger;

  function __construct()
  {
    $this->logger = load_logger();
  }

  public function create_new_article(array $scrapying_results, int $wdp_crawled_articles_id)
  {
    global $wpdb;

    $wpdb->query('START TRANSACTION');
    $result1 = $post_id = $this->insert_post($scrapying_results);

    $filemanager = new FileManager($wdp_crawled_articles_id, $post_id);
    $result2 = $filemanager->extract_images_in_zip_to_upload_dir(); // zipファイルを展開し、uploadディレクトリに保管する
    $result3 = $this->set_featured_image($post_id);
    $result4 = $this->make_body_with_pagination($post_id);

    if ($result1 && $result2 && $result3 && $result4) {
      $wpdb->query("COMMIT");
      return $post_id;
    } else {
      $wpdb->query("ROLLBACK");
      $this->logger->error("$wpdb->last_error");
      return null;
    }
  }

  public function get_crawled_articles(string $domain)
  {
    global $wpdb;
    // 解析待ちのarticle_urls
    $crawled_articles = $wpdb->get_results(
      "
    SELECT *
    FROM wdp_crawled_articles
    WHERE article_url LIKE '$domain%'
    AND analyzed =" . self::ANALYZED_STATUS['UNANALYZED']
    );

    return $crawled_articles;
  }

  public function delete_crawled_articles(string $article_url)
  {
    global $wpdb;
    // 解析待ちのarticle_urls
    $wpdb->query("DELETE FROM wdp_crawled_articles WHERE article_url = '$article_url'");
  }

  // 解析データから投稿する
  private function insert_post(array $scrapying_results): int
  {
    global $wpdb;

    $meta_input_value = array();
    foreach (['author', 'type', 'group', 'series'] as $key) {
      if (!empty($scrapying_results[$key])) {
        $meta_input_value[$key] = $scrapying_results[$key];
      }
    }
    $post_id = wp_insert_post(array(
      'post_author' => 1, // 投稿者のID。
      'post_title' => $scrapying_results['title'], // 投稿のタイトル。
      'post_category' => array($scrapying_results['language']),
      'tags_input' => $scrapying_results['tags'], // タグの名前(配列)。
      'post_status' => 'publish', // ステータス(今回は「公開」)
      'post_date' => $scrapying_results['post_date'],
      'post_date_gmt' => $scrapying_results['post_date'],
      'meta_input' => $meta_input_value
    ));

    $result = $wpdb->update(
      $wpdb->posts, //your_table_nameは接頭辞を除くテーブル名。ダブルクオーテーションなどは不要です。
      array(
        'post_name' => $post_id // column1 という名前のカラムの値をvalue1 に上書きします。
      ),
      array('ID' => $post_id), // ID = x の時だけ（SQLでいえばWHERE句に該当します）
    );

    if ($result) {
      return $post_id;
    } else {
      return 0;
    }
  }

  // サムネイル画像を設定する
  private function set_featured_image(int $post_id)
  {
    // S3からイメージファイルを取得する
    $s3manager = new S3Manager();
    $s3_images = $s3manager->get_post_images($post_id);

    $first_image = "$post_id/" . sanitize_file_name(basename($s3_images[0])); // 0.webpを画像として設定する

    $attach_id = wp_insert_attachment(
      array(
        'post_mime_type' => wp_check_filetype($first_image, null)['type'],
        'post_title' => $first_image,
        'post_content' => '',
        'post_status' => 'inherit'
      ),
      $first_image,
      $post_id
    );

    $result = set_post_thumbnail($post_id, $attach_id);
    return $result;
  }

  // 本文の作成
  private function make_body_with_pagination(string $post_id): bool
  {
    // S3からイメージファイルを取得する
    $s3manager = new S3Manager();
    $s3_images = $s3manager->get_post_images($post_id);

    $body = '';

    for ($i = 0; $i < count($s3_images); $i++) {
      $s3_image_name = "$post_id/" . sanitize_file_name(basename($s3_images[$i]));
      $image_src = S3_IMAGE_CONTENT_URL . "/$s3_image_name";

      $body .= <<< EOM
      <!-- wp:image -->
      <figure class="wp-block-image"><a href="$image_src" data-lightbox="simple-group"><img src="$image_src" alt=""/></a></figure>
      <!-- /wp:image -->
      EOM;


      // 画像n枚ごとにpaginationを入れる
      $n = 10;
      if (($i + 1) % $n == 0 && $i != count($s3_images) - 1) { // ラストページ以外の時
        $body .= <<< EOM
        <!-- wp:nextpage -->
        <!--nextpage-->
        <!-- /wp:nextpage -->
        EOM;
      }
    }

    if ($body) {
      $result = $this->update_post_body($post_id, $body);
      return $result;
    } else {
      return false;
    }
  }

  // 本文を登録する
  private function update_post_body(int $post_id, string $body): bool
  {
    global $wpdb;
    $result = $wpdb->update(
      $wpdb->posts, //your_table_nameは接頭辞を除くテーブル名。ダブルクオーテーションなどは不要です。
      array(
        'post_content' => $body // column1 という名前のカラムの値をvalue1 に上書きします。
      ),
      array('ID' => $post_id), // ID = x の時だけ（SQLでいえばWHERE句に該当します）
    );
    return $result;
  }

  // 解析ステータスの更新
  public function update_wdp_crawled_articles_analyzed(int $analyzed_status, int $wdp_crawled_articles_id, int $post_id = -1): bool
  {
    global $wpdb;
    if ($post_id != -1) {
      $result = $wpdb->update(
        'wdp_crawled_articles', //your_table_nameは接頭辞を除くテーブル名。ダブルクオーテーションなどは不要です。
        array(
          'analyzed' => $analyzed_status, // column1 という名前のカラムの値をvalue1 に上書きします。
          'post_id' => $post_id
        ),
        array('id' => $wdp_crawled_articles_id), // ID = x の時だけ（SQLでいえばWHERE句に該当します）
      );
    } else {
      $result = $wpdb->update(
        'wdp_crawled_articles', //your_table_nameは接頭辞を除くテーブル名。ダブルクオーテーションなどは不要です。
        array(
          'analyzed' => $analyzed_status, // column1 という名前のカラムの値をvalue1 に上書きします。
        ),
        array('id' => $wdp_crawled_articles_id), // ID = x の時だけ（SQLでいえばWHERE句に該当します）
      );
    }
    return $result;
  }
}
