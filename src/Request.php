<?php
/**
 * Define API Endpoint
 *
 * @package viewpoint
 */

namespace Viewpoint\ViewpointApi;

use Viewpoint\Utilities\Logger;

/**
 * Define API Endpoint
 */
class Request {
	/**
	 * Response body.
	 *
	 * @var mixed
	 */
	public mixed $response;

	/**
	 * Set up request
	 *
	 * @param string  $name  Namespace for request.
	 * @param string  $url   Url to call.
	 * @param mixed   $body  Request parameters.
	 * @param string  $type  Request type: post|get.
	 * @param array   $headers  Request headers.
	 * @param boolean $cache Cache the request results in transient.
	 * @param boolean $debug Debug the request.
	 */
	public function __construct(
		public string $name,
		public string $url,
		public mixed $body = [],
		public string $type = 'post',
		public array $headers = [],
		public bool $cache = true,
		public bool $debug = false
	) {
		$this->response = $this->get();
	}

	/**
	 * Call the API or get data from stored transient
	 *
	 * @return mixed
	 */
	private function get() {
		$data = false;
		// Try transient.
		if ( $this->cache && ! $this->debug ) {
			$transient = new Transient( $this->name );
			if ( $transient && $transient->is_fresh() ) {
				$data = $transient->get();
			}
		}

		// Try remote API.
		if ( ! $data ) {
			$response = $this->call();
			if ( $response && $response['body'] ) {
				$data = $response['body'];
				if ( $this->cache && ! $this->debug ) {
					$transient->set( $data );
				}
			} elseif ( isset( $transient ) && $transient ) {
				$data = $transient->get();
			}
		}
		$data = apply_filters( 'viewpoint_api_get_data/' . $this->name, $data, $this );
		$data = apply_filters( 'viewpoint_api_get_data', $data, $this );
		return $data;
	}

	/**
	 * Make the remote request
	 *
	 * @return mixed HTTP Response
	 */
	private function call() {
		$args = [
			'headers' => $this->headers,
			// Arbitrary timeout value in seconds. Set this to something larger because some APIs are slow and transfers a lot of data.
			'timeout' => 180,
		];
		if ( $this->debug ) {
			Logger::log( $this->name . ': ' . $this->url );
		}
		if ( 'POST' === strtoupper( $this->type ) ) {

			// Format body as json for json content types.
			if ( isset( $this->headers['Content-Type'] ) && 'application/json' === $this->headers['Content-Type'] ) {
				$body = wp_json_encode( $this->body );
			} else {
				$body = $this->body;
			}

			$result = wp_remote_post(
				$this->url,
				[
					'body' => $body,
				] + $args
			);
		} else {
			$result = wp_remote_get(
				$this->url,
				$args
			);
		}

		if ( $this->debug ) {
			Logger::log( $this->name . ': ' . $result['body'] );
		}
		if ( is_wp_error( $result ) || 200 !== $result['response']['code'] ) {
			return false;
		}
		return $result;
	}
}
