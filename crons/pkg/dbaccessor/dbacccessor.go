package dbaccessor

import (
	"database/sql"
	"fmt"
	"log"
	"os"

	_ "github.com/go-sql-driver/mysql" //わかりやすくするために()で囲ってます
)

var env string = os.Getenv("ENV")

type crawledArticles struct {
	id          int64
	articleURL  string
	articleHTML string
}

// NewUniverse は指定のグリッドサイズで2次元配列を作成・初期化
func ConnectDB() {
	db, err := sql.Open("mysql", sqlServerInfo())
	if err != nil {
		log.Fatal("OpenError: ", err)
	}
	defer db.Close()
	if err := db.Ping(); err != nil {
		log.Fatal("PingError: ", err)
	}
	fmt.Print("connected")
}

func sqlServerInfo() (sql string) {
	if env == "dev" {
		sql = "root:root@tcp(db:3306)/wordpress"
	} else {
		sql = "root:root@tcp(localhost:3306)/wordpress"
	}
	return
}

// func GetAllCrawledArticles(db sql) {
// 	rows, err := db.Query("SELECT article_url from wdp_crawled_articles", 1)
// }
