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
		public int|bool $cache = 60
	) {
	}

	/**
	 * Create an endpoint on this API.
	 *
	 * @param string    $endpoint  Endpoint base.
	 * @param array     $params    Parameters.
	 * @param Formatter $formatter Formatter.
	 * @return void
	 */
	public function add_endpoint( string $endpoint, array $params = [], Formatter $formatter ) {
		$this->endpoints[ $endpoint ] = new Endpoint( $endpoint, $params, $this, $formatter );
	}
}
