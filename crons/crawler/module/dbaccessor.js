const mysql = require('mysql2/promise');

let dbaccessor = {};

let db_setting;
if (process.env.ENV == 'dev') {
  db_setting = {
    host: 'db',
    port: '3306',
    user: 'root',
    password: 'root',
    database: 'wordpress'
  }
} else {
  db_setting = {
    host: 'localhost',
    port: '3306',
    user: 'afagras',
    password: 'Warlord9696',
    database: 'afagras_w450'
  }
}

// まだクロールされていないurlのみ残す
dbaccessor.remove_crawled_articles = async function (article_urls) {
  const connection = await mysql.createConnection(db_setting);
  const [result] = await connection.execute('SELECT article_url FROM wdp_crawled_articles');

  const crawled_article_urls = result.flatMap(n => n.article_url); // article_urlだけ抜き出し、配列化する

  const not_crawled_article_urls = article_urls.filter(article_urls =>
    // 配列Bに存在しない要素が返る
    crawled_article_urls.indexOf(article_urls) == -1
  );
  connection.end();

  return not_crawled_article_urls;
}

dbaccessor.insert_crawled_article = async function (article_url, article_html) {
  const connection = await mysql.createConnection(db_setting);
  const [results] = await connection.execute('INSERT INTO wdp_crawled_articles (article_url, article_html, analyzed) VALUES (?, ?, ?)', [article_url, article_html, 0]);
  connection.end();

  return results.insertId;
}

module.exports = dbaccessor;
