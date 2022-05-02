const fs = require('fs');
const path = require('path');

let filemanager = {};

filemanager.DOWNLOAD_DIR = path.resolve(__dirname, '../../../tmp/downloads'); // zipファイルがダウンロードされる先のディレクトリ
filemanager.UPLOAD_DIR = path.resolve(__dirname, '../../../tmp/wdp_crawled_articles'); //ダウンロードしたzipファイルを移す先

filemanager.get_zip_file_name = function () {
  file = fs.readdirSync(filemanager.DOWNLOAD_DIR)[0];
  return file;
}

filemanager.get_zip_file_path = function () {
  file = fs.readdirSync(filemanager.DOWNLOAD_DIR)[0];
  return filemanager.DOWNLOAD_DIR + '/' + file;
}

// ダウンロード中かどうかを判定
filemanager.is_download_in_progress = async function () {
  filename = filemanager.get_zip_file_name();
  if (!filename || filename.endsWith('.crdownload')) { // fileが無いorダウンロード中(.crdownload)の場合、
    return false;
  }
  return true; // ファイルがある場合
}

filemanager.move_zip_file_to_upload_dir = function(id) {
  const path_from = filemanager.get_zip_file_path();
  const folder_to = filemanager.UPLOAD_DIR + '/' + id;
  const path_to = folder_to + '/' + filemanager.get_zip_file_name();

  fs.mkdirSync(folder_to, { recursive: true });
  fs.copyFileSync(path_from, path_to);
  fs.unlinkSync(path_from);
}

filemanager.clean_DOWNLOAD_DIR = async function () {
  fs.rmSync(filemanager.DOWNLOAD_DIR, { recursive: true, force: true });
}

module.exports = filemanager;
