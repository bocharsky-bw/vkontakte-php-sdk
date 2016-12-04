# VKontakte PHP SDK

A simple and lightweight PHP SDK library for VKontakte social network.

## Install

Install library with Composer dependency manager:

```bash
$ composer require bocharsky-bw/vkontakte-php-sdk
```

## Include

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

## OAuth authorization

Build authorization link in your template

```php
<a href="<?= $vk->getLoginUrl() ?>">Authenticate</a>
```

Handle response, received from `oauth.vk.com` and store access token to session
for restore it when page will be reload

```php
session_start(); // start session if you don't

if (isset($_GET['code'])) {
    $vk->authenticate($_GET['code']);
    $_SESSION['access_token'] = $vk->getAccessToken();
    header('Location: '.'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
    exit;
} else {
    $accessToken = isset($_SESSION['access_token']) ? $_SESSION['access_token'] : null;
    $vk->setAccessToken($accessToken);
    var_dump($_SESSION['access_token']);
}
```

### Get the authenticated user ID

```php
$userId = $vk->getUserId();
var_dump($userId);
```

## Calling API

```php
/** @var array[] $users */
$users = $vk->api('users.get', [
    'user_id' => 1,
    'fields' => [
        'photo_50',
        'city',
        'sex',
    ],
]);
var_dump($users);
```

For more info read the official docs:
- [Send API requests](https://vk.com/dev/api_requests)
- [List of API methods](https://vk.com/dev/methods)
