<?php

require_once 'vendor/autoload.php';

use Instagramclient\InstaDisplay;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$instagram = new InstaDisplay();

//aplication json
header('Content-Type: application/json');
if($instagram->testAccessToken()) {
    $media = $instagram->getUserMidiaApi();
    echo $media;
} else {
    $media = $instagram->getDB('media','media');
    echo $media;
}
?>