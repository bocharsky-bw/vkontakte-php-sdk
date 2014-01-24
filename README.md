vkontakte-php-sdk
=================

Vkontakte PHP SDK

Installation
------------

1) With composer:

- Add the `"bocharsky-bw/vkontakte-php-sdk": "~1.0"` into the `require` section of your `composer.json`.
- Run `composer install`.
- The example will look like:

```php
if (($loader = require_once __DIR__ . '/vendor/autoload.php') == null)  {
  die('Vendor directory not found, Please run composer install.');
}

$vk = new \BW\Vkontakte(array(
  "app_id"  => "YOUR_APP_ID",
  "secret" => "YOUR_APP_SECRET",
  "redirect_uri" => "http://your_redirect_uri",
));

// Get User info
$user = $vk->api('users.get', array(
  "fields" => array(
    "domain",
    "sex",
  ),
));
```
