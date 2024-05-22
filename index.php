<?php

require_once 'vendor/autoload.php';

use Instagramclient\InstaDisplay;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$instagram = new InstaDisplay();

//aplication json
header('Content-Type: application/json');
$data = json_encode($instagram->getDB('media', "media"));
if($data){
    echo $data;
}else{
    echo $instagram->getUserMidiaApi('media', "media");
}
?>