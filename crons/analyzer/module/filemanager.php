<?php
require_once 's3manager.php';
require_once 'logger.php';
class FileManager
{
  public string $TMP_DIR; // ダウンロードしたzipファイルを保管する一時ディレクトリ
  public string $S3_TMP_UPLOAD_DIR; // S3へアップロードするための一時ディレクトリ
  private string $post_id;
  private string $wdp_crawled_articles_id;
  private $logger;

  function __construct(string $wdp_crawled_articles_id, string $post_id)
  {
    $this->logger = load_logger();
    $this->post_id = $post_id;
    $this->wdp_crawled_articles_id = $wdp_crawled_articles_id;
    $this->S3_TMP_UPLOAD_DIR = __DIR__ . "/../../../tmp/s3/images/posts/$post_id";
    $this->TMP_DIR = realpath(__DIR__ . "/../../../tmp/wdp_crawled_articles/$wdp_crawled_articles_id");

    // s3へアップロードするための一時ディレクトリを作成
    exec("rm -rf $this->S3_TMP_UPLOAD_DIR");
    wp_mkdir_p($this->S3_TMP_UPLOAD_DIR);
  }


  // TMPディレクトリからzipファイルを解凍して、s3アップロード用ディレクトリに展開に展開
  private function unzip_download_file_to_upload_dir()
  {
    $result = true;
    try {
      $filename = glob($this->TMP_DIR . "/*")[0];

      $zip = new ZipArchive;
      if(!($zip->open($filename) === true)){
        throw new Exception("Can't open zip file");
      }
      $zip->extractTo($this->S3_TMP_UPLOAD_DIR);
      $zip->close();
    } catch (Exception $e) {
      $this->logger->error($e->getMessage() . PHP_EOL);
      $result = false;
    } finally {
      exec("rm -rf " . $this->TMP_DIR);
      return $result;
    }
  }

  private function modify_upload_images()
  {
    $result = true;
    try {
      $file_paths = glob($this->S3_TMP_UPLOAD_DIR . "/*");

      // 全てのimageをwebpに変換
      foreach ($file_paths as $file_path) {
        $this->convert_webp($file_path);
      }

      $new_filepaths = glob($this->S3_TMP_UPLOAD_DIR . "/*"); // 変換後のパスを取得
      sort($new_filepaths);

      for ($i = 0; $i < count($new_filepaths); $i++) {
        $extension = pathinfo($new_filepaths[$i])['extension']; // 拡張子の取得
        $new_filename = "$i.$extension"; // imageファイルのリネーム
        rename($new_filepaths[$i], $this->S3_TMP_UPLOAD_DIR . "/$new_filename");
      }
    } catch (Exception $e) {
      $this->logger->error($e->getMessage() . PHP_EOL);
      $result = false;
    } finally {
      return $result;
    }
  }

  public function extract_images_in_zip_to_upload_dir()
  {
    $s3manager = new S3Manager();
    if ($this->unzip_download_file_to_upload_dir() && $this->modify_upload_images() && $s3manager->upload_images($this->S3_TMP_UPLOAD_DIR,$this->post_id)) {
      return true;
    } else {
      return false;
    }
  }

  public function convert_webp(string $image_path)
  {
    ini_set('memory_limit', '128M');
    set_time_limit(240);
    $quality = 50;

    //元画像ファイルのタイプで分岐処理
    $mime_type = finfo_file( finfo_open( FILEINFO_MIME_TYPE ), $image_path );
    switch ($mime_type) {
      case 'image/jpeg':
        $img = imagecreatefromjpeg($image_path);
        break;

      case 'image/png':
        $img = imagecreatefrompng($image_path);
        break;

      case 'image/webp':
        $img = imagecreatefromwebp($image_path);
        break;

      default:
        break;
    }

    $new_filepath = pathinfo($image_path)['dirname'] . "/" . pathinfo($image_path)['filename'] . ".webp";

    imagepalettetotruecolor($img); // [Paletter image not supported by webp]を防止する
    unlink($image_path); // 変換前のファイルを削除
    imagewebp($img, $new_filepath, $quality); //ファイル名を指定して元画像データからWebP画像を作成
  }

  // imageファイルをリサイズして保存する
  public function resize_image($image_path)
  {
    list($width, $height) = getimagesize($image_path);

    $new_width = 0; // 新しい横幅
    $new_height = 0; // 新しい縦幅
    $res_w = 270; // 最大横幅
    $res_h = 390; // 最大縦幅

    if ($width > $height) {
      // 横長の画像は横のサイズを指定値にあわせる
      $ratio = $height / $width;
      $new_width = $res_w;
      $new_height = $res_w * $ratio;
    } else {
      // 縦長の画像は縦のサイズを指定値にあわせる
      $ratio = $width / $height;
      $new_width = $res_h * $ratio;
      $new_height = $res_h;
    }

    // 再サンプル
    $image_p = imagecreatetruecolor($new_width, $new_height);
    $image = imagecreatefromwebp($image_path);
    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    // 出力
    imagewebp($image_p, $image_path, 50); //
  }
}
