<?php

session_start();

require __DIR__.'/../vendor/autoload.php';

use \BW\Vkontakte as Vk;

$vk = new Vk([
    'client_id' => '5759854',
    'client_secret' => 'a556FovqtUBHArlXlAAO',
    'redirect_uri' => 'http://localhost:8000',
]);

if (isset($_GET['code'])) {
    $vk->authenticate($_GET['code']);
    $_SESSION['access_token'] = $vk->getAccessToken();
    header('Location: '.'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
    exit;
} else {
    $accessToken = isset($_SESSION['access_token']) ? $_SESSION['access_token'] : null;
    $vk->setAccessToken($accessToken);
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
var_dump($users);

?>

<br>
<a href="<?= $vk->getLoginUrl() ?>">
    <?php if ($userId) : ?>
        Re-authenticate
    <?php else : ?>
        Authenticate
    <?php endif ?>
</a>
