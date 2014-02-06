<?php
namespace Guzzle\Service\Mediawiki;

class ServiceDescriptionGenerator {

	private static $allowedTypes = array( 'string', 'integer', 'boolean', 'null' );

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
		$operations = array();
		$operations['_abstract_request'] = $this->getAbstractRequest();
		foreach( $actions as $action ) {
			$operations[ $action ] = $this->generateOperation( $action );
		}
		return array( 'operations' => $operations );
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

		if( array_key_exists( 'prefix', $details ) ) {
			$prefix = $details['prefix'];
		} else {
			$prefix = '';
		}

		if( array_key_exists( 'parameters', $details ) ) {
			$operation['parameters'] = $this->generateParameters( $action, $details['parameters'], $mustBePosted, $prefix );
		}

		return $operation;
	}

	/**
	 * @param string $action
	 * @param array $parametersDetails result from the API listing all parameters
	 * @param bool $mustBePosted
	 * @param string $prefix prefix to be appended to params if any
	 *
	 * @returns array
	 */
	private function generateParameters( $action, $parametersDetails, $mustBePosted, $prefix ) {

		$parameters = array();

		$parameters['action'] = array(
			'name' => 'action',
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
			$parameters[ $prefix . $parameterDetails['name'] ] = $this->generateParameter( $parameterDetails, $mustBePosted, $prefix );
		}

		return $parameters;
	}

	/**
	 * @param array $details result from the API listing details of a single parameter
	 * @param bool $mustBePosted
	 * @param string $prefix prefix to be appended to params if any
	 *
	 * @returns array
	 */
	private function generateParameter( $details, $mustBePosted, $prefix ) {
		$param = array();

		$param['name'] = $prefix . $details['name'];

		if( $mustBePosted ) {
			$param['location'] = 'postField';
		} else {
			$param['location'] = 'query';
		}

		if( array_key_exists( 'type', $details ) ) {
			// Don't limit what can be in arrays here, just make sure they are arrays
			// As mediawiki splits arrays with | we just want a string here!
			// Also don't allow silly types such as 'user'
			if( is_array( $details['type'] ) || !in_array( $details['type'], self::$allowedTypes ) ) {
				$param['type'] = 'string';
			} else {
				$param['type'] = $details['type'];
			}
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