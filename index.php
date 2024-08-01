<?php

require 'vendor/autoload.php'; // Ensure you have installed Goutte and Guzzle via Composer

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;

class EcommerceScraper
{
    private $client;
    private $scraperApiKey;

    public function __construct($scraperApiKey)
    {
        $this->client = new Client();
        $this->scraperApiKey = $scraperApiKey;
    }

    public function scrapeWebsite($url)
    {
        // Use ScraperAPI for requests to handle IP bans and CAPTCHAs
        $scraperUrl = 'http://api.scraperapi.com?api_key=' . $this->scraperApiKey . '&url=' . urlencode($url);

        $crawler = $this->client->request('GET', $scraperUrl);

        // Extract product information using Goutte
        $crawler->filter('.product-list-item')->each(function ($node) {
            $title = $node->filter('.product-title')->text();
            $price = $node->filter('.product-price')->text();

            echo 'Title: ' . $title . PHP_EOL;
            echo 'Price: ' . $price . PHP_EOL;
        });
    }

    private function curlRequest($url)
    {
        // Using cURL to handle session management and complex requests
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt'); // Use a file to store cookies
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');  // Write cookies to this file

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        }

        curl_close($ch);
        return $response;
    }

    public function scrapeWithCurl($url)
    {
        $response = $this->curlRequest($url);

        // You can process the response here, e.g., using DOMDocument or regex
        $dom = new DOMDocument();
        @$dom->loadHTML($response);
        $xpath = new DOMXPath($dom);

        $nodes = $xpath->query('//div[contains(@class, "product-list-item")]');
        foreach ($nodes as $node) {
            $title = $xpath->query('.//h2[contains(@class, "product-title")]', $node)->item(0)->nodeValue;
            $price = $xpath->query('.//span[contains(@class, "product-price")]', $node)->item(0)->nodeValue;

            echo 'Title: ' . trim($title) . PHP_EOL;
            echo 'Price: ' . trim($price) . PHP_EOL;
        }
    }
}

// Example usage
$scraperApiKey = 'your_scraper_api_key';
$scraper = new EcommerceScraper($scraperApiKey);

$urls = [
    'https://www.example-ecommerce1.com/products',
    'https://www.example-ecommerce2.com/products'
];

foreach ($urls as $url) {
    echo "Scraping $url with Goutte:" . PHP_EOL;
    $scraper->scrapeWebsite($url);
    echo PHP_EOL;

    echo "Scraping $url with cURL:" . PHP_EOL;
    $scraper->scrapeWithCurl($url);
    echo PHP_EOL;
}

?>
