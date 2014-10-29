<?php

session_start();

require __DIR__ . '/path/to/vendor/autoload.php';

use \BW\Vkontakte as Vk;

$vk = new Vk([
    'client_id' => '4609415',
    'client_secret' => 'JgeSQNKLZCT44qEsAyWG',
    'redirect_uri' => 'http://localhost/test/',
]);

if (isset($_GET['code'])) {
    $vk->authenticate();
    $_SESSION['access_token'] = $vk->getAccessToken();
    header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
    exit;
} else {
    $vk->setAccessToken($_SESSION['access_token']);
}

$userId = $vk->getUserId();
var_dump($userId);

$users = $vk->api('users.get', [
    'user_id' => '1',
    'fields' => [
        'photo_50',
        'city',
        'sex',
    ],
]);

print '<pre>';
print_r($users);
print '</pre>';

print '<br><a href="' . $vk->getLoginUrl() . '">Sign In</a>';
