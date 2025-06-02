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
	public $url;

	/**
	 * Set up API.
	 *
	 * @param string       $base API endpoint.
	 * @param array|Api    $api  API options.
	 * @param mixed        $params   Query params.
	 * @param "POST"|"GET" $type Request type.
	 * @param Formatter    $formatter Formatter.
	 */
	public function __construct(
		public string $base,
		public Api $api,
		public mixed $params = [],
		public string $type = 'POST',
		public Formatter $formatter = new Formatter()
	) {
		$this->url = $this->make_url();
	}

	/**
	 * Build the url.
	 *
	 * @return string
	 */
	private function make_url() {
		$url = $this->api->base . '/' . $this->base . '/';
		return apply_filters( 'viewpoint_api_url', $url, $this );
	}

	/**
	 * Initialize a Request.
	 *
	 * @return Request
	 */
	public function request() {
		if ( $this->api->authentication ) {
			$this->api->authentication->addAuthentication();
		}

		if ( 'get' === $this->type && is_array( $this->params ) ) {
			// Modify url with params.
			$url_args = [];
			foreach ( $this->params as $key => $value ) {
				$url_args[ $key ] = is_string( $value ) ? rawurlencode( $value ) : $value;
			}
			$this->url = add_query_arg( $url_args, $this->url );
		}
		$request = new Request(
			name: $this->base,
			url: $this->url,
			body: $this->params,
			type: $this->type,
			headers: $this->api->headers,
			cache: $this->api->cache,
			debug: $this->api->debug
		);
		return $this->formatter->format( $request->response );
	}
}
