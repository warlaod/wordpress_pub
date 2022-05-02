package htmlgetter

import (
	"context"
	"log"
	"os"
	"time"

	"github.com/chromedp/cdproto/emulation"
	"github.com/chromedp/chromedp"
)

var env string = os.Getenv("ENV")

func CrawlIndexHTML(url string) (result string) {

	opts := append(chromedp.DefaultExecAllocatorOptions[:],
		chromedp.ProxyServer(proxyPass()),
	)

	ctx, cancel := chromedp.NewExecAllocator(context.Background(), opts...)
	defer cancel()

	ctx, cancel = chromedp.NewContext(ctx)
	defer cancel()

	// create a timeout
	ctx, cancel = context.WithTimeout(ctx, 30*time.Second)
	defer cancel()

	// navigate to a page, wait for an element, click
	err := chromedp.Run(ctx,
		emulation.SetUserAgentOverride(randomUserAgent()),
		chromedp.Navigate(url),
		// wait for footer element is visible (ie, page is loaded)
		chromedp.WaitVisible(`body`, chromedp.ByQuery),
		// find and click "Example" link
		// chromedp.Click(`#example-After`, chromedp.NodeVisible),
		// retrieve the text of the textarea
		chromedp.Evaluate(`document.documentElement.outerHTML;`, &result),
	)
	if err != nil {
		log.Fatal(err)
	}
	return
}

func CrawlArticleHTML(url string) (result string) {

	opts := append(chromedp.DefaultExecAllocatorOptions[:],
		chromedp.ProxyServer(proxyPass()),
	)

	ctx, cancel := chromedp.NewExecAllocator(context.Background(), opts...)
	defer cancel()

	ctx, cancel = chromedp.NewContext(ctx)
	defer cancel()

	// create a timeout
	ctx, cancel = context.WithTimeout(ctx, 120*time.Second)
	defer cancel()

	// get working directory
	wd, err := os.Getwd()
	if err != nil {
		log.Fatal(err)
	}

	// navigate to a page, wait for an element, click
	err = chromedp.Run(ctx,
		readyToCrawl(url),
		chromedp.Evaluate(`document.documentElement.outerHTML;`, &result),
		chromedp.
			clickDownloadButton(wd),
	)
	if err != nil {
		log.Fatal(err)
	}

	return
}

func proxyPass() (proxyPass string) {
	if env == "dev" {
		proxyPass = "http://tor:8118"
	} else {
		proxyPass = "http://localhost:8118"
	}
	return
}
