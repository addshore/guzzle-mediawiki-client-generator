<?php
if( !file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	die( 'You must run "composer install" first!' );
}

require_once( __DIR__ . '/vendor/autoload.php' );

$client = 	\Guzzle\Service\Mediawiki\MediawikiApiClient::factory( array( 'base_url' => 'http://localhost/wiki/api.php' ) );
$actionListGenerator = new \Guzzle\Service\Mediawiki\ActionListGenerator( $client );
$descriptionGenerator = new \Guzzle\Service\Mediawiki\ServiceDescriptionGenerator( $client );

$actions = $actionListGenerator->generateList();
$json = $descriptionGenerator->build( $actions );

file_put_contents( __DIR__ . '/build_output.json', $json );
echo $json;