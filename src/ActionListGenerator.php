<?php

namespace Guzzle\Service\Mediawiki;

class ActionListGenerator {

	/**
	 * @var MediawikiApiClient
	 */
	protected $client;

	/**
	 * @param MediawikiApiClient $client to use when building descriptions
	 */
	public function __construct( MediawikiApiClient $client ) {
		$this->client = $client;
	}

	/**
	 * @returns array
	 */
	public function generateList() {
		$config = $this->client->getConfig();
		$request = $this->client->get( $config['base_url'] );
		$result = $request->send();
		$body = $result->getBody( true );

		preg_match_all( '/\*\saction\=(\S+?)\s/', $body, $matches );
		$actions = $matches[1];

		return array_unique( $actions );
	}

} 