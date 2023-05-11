<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'logger.php';

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class S3Manager
{
  public Aws\S3\S3Client $s3client; // S3へアップロードするための一時ディレクトリ
  public array $s3_info = array(
    'bucket' => '',
    'content_dir' => '',
  );
  private $logger;

  function __construct()
  {
    $this->logger = load_logger();

    $this->s3client = new Aws\S3\S3Client([
      'credentials' => [
        'key' => '',
        'secret' => '',
      ],
      'region' => 'ap-northeast-1',
      'version' => 'latest',
    ]);

    $s3_image_content_url = parse_url(S3_IMAGE_CONTENT_URL);
    $s3_image_thumbnail_url = parse_url(S3_IMAGE_THUMBNAIL_URL);
    $this->s3_info = array(
      'bucket' => $s3_image_content_url['host'],
      'content_dir' => ltrim($s3_image_content_url['path'], '/'), // 先頭の'/'を削除
      'thumbnail_dir' => ltrim($s3_image_thumbnail_url['path'], '/'), // 先頭の'/'を削除
    );
  }

  // post_idごとにイメージファイルをS3にアップロードする
  public function upload_images(string $local_image_dir, $post_id): bool
  {
    $s3_upload_content_dir = $this->s3_info['content_dir'] . "/$post_id";
    $s3_upload_thumbnail_dir = $this->s3_info['thumbnail_dir'] . "/$post_id";
    $local_images = glob($local_image_dir . "/*");

    $this->sort_by_number($local_images); // ファイルを番号順に並べる

    // ディレクトリが重複しないようにする
    $this->s3client->deleteMatchingObjects($this->s3_info['bucket'], $s3_upload_content_dir);
    $this->s3client->deleteMatchingObjects($this->s3_info['bucket'], $s3_upload_thumbnail_dir);

    // contentイメージファイルのアップロード
    try {
      for ($i = 0; $i < count($local_images); $i++) {
        $local_image = $local_images[$i];
        $mime_type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $local_image);

        //  upload images
        $this->s3client->putObject([
          'Bucket' => $this->s3_info['bucket'],
          'Key' => "$s3_upload_content_dir/$i.webp",
          'SourceFile' => $local_image,
          'ContentType' => $mime_type
        ]);
      }
    } catch (S3Exception $e) {
      $this->logger->error($e->getMessage() . PHP_EOL);
      return false;
    }

    // thhumbnailイメージファイルのアップロード。0.webpをサムネイル画像として扱う
    try {
      $local_image = $local_images[0];
      $filemanager = new FileManager(0, 0);
      $filemanager->resize_image($local_image); // 0.webpをリサイズして保存

      $mime_type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $local_image);
      //  upload images
      $this->s3client->putObject([
        'Bucket' => $this->s3_info['bucket'],
        'Key' => "$s3_upload_thumbnail_dir/0.webp",
        'SourceFile' => $local_image,
        'ContentType' => $mime_type
      ]);
    } catch (Exception $e) {
      $this->logger->error($e->getMessage() . PHP_EOL);
      return false;
    }

    exec("rm -rf $local_image_dir");
    return true;
  }

  // S3から画像ファイルの一覧をレスポンス
  public function get_post_images($post_id): array
  {
    $s3_object_list = $this->s3client->listObjects([
      'Bucket' => $this->s3_info['bucket'],
      'Prefix' => $this->s3_info['content_dir'] . "/$post_id",
    ])['Contents'];

    if (empty($s3_object_list)) {
      return array();
    }

    $s3_image_paths = array_column($s3_object_list, 'Key'); // s3オブジェクトから、s3のimageのpathを取得
    $this->sort_by_number($s3_image_paths); // ファイルを番号順に並べる

    return $s3_image_paths;
  }

  private function sort_by_number(&$image_array) // 番号順にarrayを並び替え
  {
    natsort($image_array); // ファイルを番号順に並べる
    $image_array = array_values($image_array); // バラバラになったindexを貼り直す

  }
}
