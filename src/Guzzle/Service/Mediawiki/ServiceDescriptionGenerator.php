<?php
namespace Guzzle\Service\Mediawiki;

class ServiceDescriptionGenerator {

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
	 * @param array $actions to include in the service descriptions
	 *
	 * @returns string Formatted JSON Guzzle Service Description
	 */
	public function build( $actions = array() ) {
		$data = $this->generateDescription( $actions );
		return json_encode( $data, JSON_PRETTY_PRINT );
	}

	/**
	 * @param array $actions to include in the service descriptions
	 *
	 * @returns array Guzzle Service Description in array form
	 */
	private function generateDescription( $actions ) {
		$description = array();
		$description['_abstract_request'] = $this->getAbstractRequest();
		foreach( $actions as $action ) {
			$description[ $action ] = $this->generateOperation( $action );
		}
		return $description;
	}

	/**
	 * @returns array
	 */
	private function getAbstractRequest() {
		return array(
			'httpMethod' => 'GET',
			'parameters' => array(
				'action' => array(
					'location' => 'query',
					'type' => 'string',
					'required' => true,
				),
				'format' => array(
					'location' => 'query',
					'type' => 'string',
					'default' => 'json',
				),
			)
		);
	}

	/**
	 * @param string $action
	 *
	 * @returns array
	 */
	private function generateOperation( $action ) {
		$details = $this->client->paraminfo( array( 'modules' => $action ) );
		$details = $details['paraminfo']['modules'][0];
		$operation = array();

		$operation['extends'] = '_abstract_request';

		$mustBePosted = array_key_exists( 'mustbeposted', $details );
		if( $mustBePosted ) {
			$operation['httpMethod'] = 'POST';
		} else {
			$operation['httpMethod'] = 'GET';
		}

		if( array_key_exists( 'description', $details ) ) {
			$operation['summary'] = $details['description'];
		}

		if( array_key_exists( 'parameters', $details ) ) {
			$operation['parameters'] = $this->generateParameters( $action, $details['parameters'], $mustBePosted );
		}

		return $operation;
	}

	/**
	 * @param string $action
	 * @param array $parametersDetails result from the API listing all parameters
	 * @param bool $mustBePosted
	 *
	 * @returns array
	 */
	private function generateParameters( $action, $parametersDetails, $mustBePosted ) {
		$parameters = array();

		$parameters['action'] = array(
			'default' => $action,
			'static' => true,
			'type' => 'string',
		);
		if( $mustBePosted ) {
			$parameters['action']['location'] = 'postField';
		} else {
			$parameters['action']['location'] = 'query';
		}

		foreach( $parametersDetails as $parameterDetails ) {
			$parameters[ $parameterDetails['name'] ] = $this->generateParameter( $action, $parameterDetails, $mustBePosted );
		}

		return $parameters;
	}

	/**
	 * @param string $action
	 * @param array $details result from the API listing details of a single parameter
	 * @param bool $mustBePosted
	 *
	 * @returns array
	 */
	private function generateParameter( $action, $details, $mustBePosted ) {
		$param = array();

		$param['name'] = $details['name'];

		if( $mustBePosted ) {
			$param['location'] = 'postField';
		} else {
			$param['location'] = 'query';
		}

		if( array_key_exists( 'type', $details ) ) {
			$param['type'] = $details['type'];
		} else {
			$param['type'] = 'string'; //default to string
		}

		if( array_key_exists( 'required', $details ) ) {
			$param['required'] = true;
		}

		if( array_key_exists( 'description', $details ) ) {
			$param['description'] = $details['description'];
		}

		return $param;
	}
}