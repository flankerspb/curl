<a href="https://curl.se/" target="_blank" rel="external"><img src="https://curl.se/logo/curl-logo.svg" height="48px" alt="CURL"></a>

# Simple cURL wrapper


[![License](https://img.shields.io/github/license/flankerspb/curl)](LICENSE.md)
![GitHub code size in bytes](https://img.shields.io/github/languages/code-size/flankerspb/curl)
![PHP](https://img.shields.io/badge/php-%3E%3D7.4-7377AD)


## Installation

```
php composer.phar require --prefer-dist flankerspb/curl
```


## Usage

```php
$curl = new \fl\curl\Curl([
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_AUTOREFERER    => true,
]);

$responseGet = $curl
    ->setQuery([
        'key1' => 'value',
        'key2' => 'value',
    ])
    ->get('https://www.site.com/');

$responsePost = $curl
    ->setBody([
        'key1' => 'value',
        'key2' => 'value',
    ], true)
    ->post('https://site.com/');
```
```php
$curl = new \fl\curl\Curl();

$curl
    ->setHeader('key', 'value')
    ->setCookie('key', 'value')
    ->setProxy('socks5://user:pass@1.1.1.1:1080')
;


$response = new class() implements \fl\curl\ResponseInterface {
    public function init($handle, array $options) : void
    {
        // TODO: Implement init() method.
    }
};


$curl->get('https://www.site.com/', $response);
```
