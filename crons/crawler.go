package main // 自作パッケージ

import (
	"fmt"
	"go_crawler/pkg/htmlgetter"
	"log"
	"net/url"
)

func main() {
	// indexURL := "https://www.cman.jp/network/support/go_access.cgi"
	articleURL := "https://esacs.ccom/"
	// domainURL := getDomainURL(indexURL)

	// fmt.Print(indexURL)
	// fmt.Print(domainURL)
	// dbaccessor.ConnectDB()
	// indexHTML := htmlgetter.CrawlIndexHTML(indexURL)
	articleHTML := htmlgetter.CrawlArticleHTML(articleURL)
	// fmt.Print(indexHTML)
	fmt.Print(articleHTML)
}

func getDomainURL(indexURL string) (domainURL string) {
	u, err := url.Parse(indexURL)
	if err != nil {
		log.Fatal(err)
	}
	domainURL = u.Scheme + "//" + u.Host
	return
}
