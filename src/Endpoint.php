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
		public Formatter $formatter = new Formatter(),
		public Pagination $pagination = new Pagination(),
	) {
		$this->url = $this->make_url();
	}

	/**
	 * Build the url.
	 *
	 * @return string
	 */
	private function make_url() {
		$url = $this->api->base . '/' . $this->base;
		return apply_filters( 'viewpoint_api_url', $url, $this );
	}

	/**
	 * Update params so they can be changed before running request.
	 *
	 * @param string $key   Key to update.
	 * @param mixed  $value  Value to update.
	 * @return void
	 */
	public function updateParams( $key, $value ) {
		$this->params[ $key ] = $value;
	}

	/**
	 * Initialize a Request.
	 *
	 * @return mixed
	 */
	public function request() {
		if ( $this->api->authentication ) {
			$this->api->authentication->authenticate();
		}

		// Paginated request.
		if ( true === $this->pagination->isPaginated ) {
			return $this->paginatedRequest();
		}
		// Single request.
		$request = $this->makeRequest();
		return $this->formatter->format( $request->response );
	}

	/**
	 * Loop on pages until no more!
	 *
	 * @return array
	 */
	public function paginatedRequest() {
		$responses = [];
		while ( true === $this->pagination->hasMorePages() ) {
			$request                       = $this->makeRequest();
			$data                          = $this->formatter->format( $request->response, true );
			$this->pagination->totalPages  = intval( Formatter::getNestedKeys( [ 'pagination', 'totalPages' ], $data ) );
			$this->pagination->currentPage = intval( Formatter::getNestedKeys( [ 'pagination', 'currentPage' ], $data ) );
			$formattedResponse = $this->formatter->format( $request->response );
			if ( is_array( $formattedResponse ) ) {
				$responses = array_merge( $responses, $this->formatter->format( $request->response ) );
			}
			if ( is_array( $this->pagination->pageParam ) && ! empty( $this->pagination->pageParam ) ) {
				$currentParam = &$this->params;
				foreach ( $this->pagination->pageParam as $param ) {
					$currentParam = &$currentParam[ $param ];
				}
				++$currentParam;
			}
		}
		return $responses;
	}

	/**
	 * Initialize a Request.
	 *
	 * @return mixed
	 */
	public function makeRequest() {
		if ( 'get' === $this->type && is_array( $this->params ) ) {
			// Modify url with params.
			$url_args = [];
			foreach ( $this->params as $key => $value ) {
				$url_args[ $key ] = is_string( $value ) ? rawurlencode( $value ) : $value;
			}
			$this->url = add_query_arg( $url_args, $this->url );
		}

		return new Request(
			name: $this->base,
			url: $this->url,
			body: $this->params,
			type: $this->type,
			headers: $this->api->headers,
			cache: $this->api->cache,
			debug: $this->api->debug
		);
	}
}
