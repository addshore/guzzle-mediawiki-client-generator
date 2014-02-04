<?php
if( !file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	die( 'You must run "composer install" first!' );
}

require_once( __DIR__ . '/vendor/autoload.php' );

$generator = new \Guzzle\Service\Mediawiki\ServiceDescriptionGenerator(
	\Guzzle\Service\Mediawiki\MediawikiApiClient::factory(
		array(
			'base_url' => 'http://localhost/wiki/api.php'
		)
	)
);

$actions = array(
	'edit',
);

$json = $generator->build( $actions );

echo $json;