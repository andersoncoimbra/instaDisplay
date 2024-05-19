<?php

require_once 'vendor/autoload.php';

use Instagramclient\InstaDisplay;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$instagram = new InstaDisplay();

if (isset($_GET['code'])) {
	$code = $_GET['code'];
	$accessToken = $instagram->getShorterLivedAccessToken($code);
	if($accessToken) {
		$accessToken = $instagram->getLongerLivedAccessToken($accessToken);
		if($accessToken) {
			$instagram->saveAccessToken($accessToken);
		}
	}
}

if($instagram->testAccessToken()) {
	header('Location: index.php');
} else {
	$url = $instagram->getLinkAuthorizationCode();
	echo "
	<a href=' $url '>
        <button >Integrar Instagram</button>
    </a>
	";
}

