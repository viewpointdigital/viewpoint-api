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
	 * @param string $endpoint Endpoint base.
	 * @param array  $params   Parameters.
	 * @param string $type     Request type.
	 * @return Endpoint
	 */
	public function endpoint( string $endpoint, array $params = [] ) {
		return new Endpoint( $endpoint, $params, $this );
	}
}
