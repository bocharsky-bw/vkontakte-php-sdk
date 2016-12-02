<?php
// Example - get all public wall links - useful for repost links pulbics
session_start();

require '../vendor/autoload.php';

use \BW\Vkontakte as Vk;

$vk = new Vk([
    'client_id' => 'XXXXXX',
    'client_secret' => 'XXXXXX',
    'redirect_uri' => 'http://localhost/vkontakte-php-sdk/examples/',
]);
$public = '';

if (isset($_GET['code'])) {
    $vk->authenticate();
    $_SESSION['access_token'] = $vk->getAccessToken();
    header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
    exit;
} else {
    $vk->setAccessToken($_SESSION['access_token']);
}

$userId = $vk->getUserId();

$wall0Empty = $vk->api('wall.get', [
    'domain' => $public,
]);
$wallCount = $wall0Empty['count'];

$apiParams = [
    'domain' => 'xxxxxxxx',
    'count' => 100
];  

$wallArray = [];
for($i = 0; $i < ceil($wallCount / 100); $i++) {
    if($i > 0) {
        $offset = 100 * $i+1;
        $apiParams['offset'] = $offset;
    }
    $wall = $vk->api('wall.get', $apiParams);
    $wallArray = array_merge($wallArray, $wall['items']);    
}

$linksArray = [];
foreach ($wallArray as $key => $value) {
    if(!empty($value['attachments'])) {
        foreach ($value['attachments'] as $keyAttach => $valueAttach) {
            if($valueAttach['type'] == 'link') {
                $linksArray[] = $valueAttach['link']['url'];
            }
        }
    } 
}

foreach($linksArray as $link) {
   echo $link . '<br>';
}
print '<br><a href="' . $vk->getLoginUrl() . '">Sign In</a>';
