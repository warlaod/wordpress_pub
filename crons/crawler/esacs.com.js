const dbaccessor = require('./module/dbaccessor');
const htmlgetter = require('./module/htmlgetter');
const filemanager = require('./module/filemanager');
const matcher = require('./module/matcher');
const logger = require('./module/logger');


(async () => {
  for (let index = 0; index < 3000; index++) {

    const index_url = 'https://esacs.com/?page=' + index;
    const domain = (new URL(index_url)).origin;

    const index_html = await htmlgetter.get_index_html(index_url);

    // 記事URLを取得
    regexp = /<div class="(?:acg|cg|dj|manga)">\s*?<a href="(.+?\.html)"/g;
    results = matcher.match_all(index_html, regexp);

    // 相対パスを絶対パスに修正
    article_urls = results.map(function (result) {
      const article_url = domain + result;
      return article_url;
    });

    article_urls = await dbaccessor.remove_crawled_articles(article_urls);

    for await (const article_url of article_urls) {
      logger.info(article_url);
      const [article_html, zip_filename] = await htmlgetter.get_article_data(article_url);

      if (!article_html || !zip_filename) {
        logger.warn('Failed to get article_data: ' + article_url);
        continue;
      }

      const wdp_crawled_articles_id = await dbaccessor.insert_crawled_article(article_url, article_html);
      filemanager.move_zip_file_to_upload_dir(wdp_crawled_articles_id);
    }
  }
})();
