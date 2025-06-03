<?php
/**
 * Define API options
 *
 * @package viewpoint
 */

namespace Viewpoint\ViewpointApi;

/**
 * Define API Options
 */
class Api {
	/**
	 * Configured endpoints.
	 *
	 * @var Endpoint[]
	 */
	public $endpoints = [];

	/**
	 * Configured authentication.
	 *
	 * @var Auth
	 */
	public $authentication = null;

	/**
	 * Options for API
	 *
	 * @param string      $name API name.
	 * @param string      $base      Base url.
	 * @param array       $headers   Request headers.
	 * @param boolean     $debug     Debug request.
	 * @param int|boolean $cache     Cache timeout in minutes. Set false to disable cache.
	 */
	public function __construct(
		public string $name,
		public string $base,
		public array $headers = [],
		public bool $debug = false,
		public int|bool $cache = 60,
	) {
	}

	/**
	 * Create an endpoint on this API.
	 *
	 * @param Endpoint $endpoint Endpoint.
	 */
	public function add_endpoint(
		Endpoint $endpoint
	) {
		$this->endpoints[ $endpoint->base ] = $endpoint;
	}

	/**
	 * Create an authentication interface for the API.
	 *
	 * @param Auth $authentication Authentication.
	 */
	public function add_authentication(
		Auth $authentication
	) {
		$this->authentication = $authentication;
	}
}
