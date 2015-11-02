vkontakte-php-sdk
=================

Simple Vkontakte PHP SDK

Install
-------

Install library with `composer` dependency manager

- Add `"bocharsky-bw/vkontakte-php-sdk": "dev-master"` into the `require` section of your `composer.json` file
- Run `$ composer.phar install`

Include
-------

Require `composer` autoloader in your index file

```php
require __DIR__ . '/path/to/vendor/autoload.php';
```

Create instance of `Vkontakte` class with your own configuration parameters

```php
use \BW\Vkontakte as Vk;

$vk = new Vk([
    'client_id' => 'APP_ID',
    'client_secret' => 'APP_SECRET',
    'redirect_uri' => 'REDIRECT_URI',
]);
```

OAuth authorization
-------------------

Build authorization link in your template

```html
<a href="<?php print $vk->getLoginUrl() ?>">Sign In</a>
```

Handle response, received from `oauth.vk.com` and store access token to session
for restore it when page will be reload

```php
session_start(); // start session if you don't

if (isset($_GET['code'])) {
    $vk->authenticate();
    $_SESSION['access_token'] = $vk->getAccessToken();
    header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
    exit;
} else {
    $vk->setAccessToken($_SESSION['access_token']);
    var_dump($_SESSION);
}
```

Get the authorized user ID

```php
$userId = $vk->getUserId();

var_dump($userId);
```

Calling API
-----------

```php
$user = $vk->api('users.get', [
    'user_id' => '1',
    'fields' => [
        'photo_50',
        'city',
        'sex',
    ],
]);

var_dump($user);
```

For more info read the official docs:
- [Send API requests](https://vk.com/dev/api_requests)
- [List of API methods](https://vk.com/dev/methods)
