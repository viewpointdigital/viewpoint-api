<?php
/**
 * Define API Endpoint
 *
 * @package viewpoint
 */

namespace Viewpoint\ViewpointApi;

/**
 * Define API Endpoint
 */
class Endpoint {
	/**
	 * Call url.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Set up API.
	 *
	 * @param string    $endpoint API endpoint.
	 * @param array     $params   Query params.
	 * @param array|Api $api  API options.
	 * @param Formatter $formatter Formatter.
	 */
	public function __construct( public string $endpoint, public array $params = [], public Api $api, public Formatter $formatter ) {
		$this->url = $this->make_url();
	}

	/**
	 * Build the url.
	 *
	 * @return string
	 */
	private function make_url() {
		$url = $this->api->base . '/' . $this->endpoint . '/';
		return apply_filters( 'viewpoint_api_url', $url, $this );
	}

	/**
	 * Initialize a Request.
	 *
	 * @param string $type Request type: post|get.
	 * @return Request
	 */
	public function request( string $type = 'post' ) {
		$name = $this->api->name . '-' . $this->endpoint;

		if ( 'get' === $type ) {
			// Modify url with params.
			$url_args = [];
			foreach ( $this->params as $key => $value ) {
				$url_args[ $key ] = is_string( $value ) ? rawurlencode( $value ) : $value;
			}
			$this->url = add_query_arg( $url_args, $this->url );
		}
		$request = new Request( $name, $this->url, $this->params, $type, $this->api->headers, $this->api->cache, $this->api->debug );
		return $this->formatter->format( $request->response );
	}
}
