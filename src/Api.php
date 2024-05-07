<?php
/**
 * Define API
 *
 * @package viewpoint
 */

namespace Viewpoint\ViewpointApi;

use Viewpoint\ViewpointApi\Options;
use Viewpoint\ViewpointApi\Transient;

/**
 * Define API
 */
class Api {
	/**
	 * Call url.
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Set up API.
	 *
	 * @param string        $endpoint API endpoint.
	 * @param array         $params   Query params.
	 * @param array|Options $options  API options.
	 * @param string        $type     Request type, post or get.
	 */
	public function __construct( public string $endpoint, public array $params = [], public array|Options $options, public string $type = 'post' ) {
		if ( ! is_a( $this->options, 'Viewpoint\ViewpointApi\Options' ) ) {
			$this->options = new Options( ...$this->options );
		}

		$this->url = $this->make_url();
	}

	/**
	 * Build the url.
	 *
	 * @return string
	 */
	private function make_url() {
		$url      = $this->options->base . '/' . $this->endpoint . '/';
		$url_args = [];
		foreach ( $this->params as $key => $value ) {
			$url_args[ $key ] = is_string( $value ) ? rawurlencode( $value ) : $value;
		}
		$url = add_query_arg( $url_args, $url );
		return apply_filters( 'viewpoint_api_url', $url, $this );
	}

	/**
	 * Call the API or get data from stored transient
	 *
	 * @return object
	 */
	public function get() {
		$data = false;
		// Try transient.
		if ( $this->options->cache && ! $this->options->debug ) {
			$transient = new Transient( $this->options->name . '_' . $this->endpoint );
			if ( $transient && $transient->is_fresh() ) {
				$data = $transient->get();
			}
		}

		// Try remote API.
		if ( ! $data ) {
			$response = $this->call();
			if ( $response['body'] ) {
				$data = self::format_body( $response['body'] );
				if ( $this->options->cache ) {
					$transient->set( $data );
				}
			}
		}
		$data = apply_filters( 'viewpoint_api_get_data/' . $this->options->name, $data, $this );
		return apply_filters( 'viewpoint_api_get_data', $data, $this );
	}

	/**
	 * Make the remote request
	 *
	 * @return object HTTP Response
	 */
	private function call() {
		$args = [
			'headers' => $this->options->headers,
			// Arbitrary timeout value in seconds. Set this to something larger because some APIs are slow and transfers a lot of data.
			'timeout' => 180,
		];
		if ( 'post' === $this->type ) {
			$result = wp_remote_post(
				$this->url,
				[
					'body' => $this->params,
				] + $args
			);
		} else {
			$result = wp_remote_get(
				$this->url,
				$args
			);
		}

		if ( is_wp_error( $result ) || 200 !== $result['response']['code'] ) {
			if ( $this->options->debug ) {
				self::log( $this->endpoint, $result['body'] );
			}
			return false;
		}
		return $result;
	}

	/**
	 * Format response body.
	 *
	 * @param string $body Body of API response.
	 * @return obj
	 */
	private static function format_body( $body ) {
		return json_decode( $body );
	}

	/**
	 * Log request to Query Monitor.
	 *
	 * @param string $title  Title of log entry.
	 * @param string $body   Body of response.
	 * @return void
	 */
	public static function log( $title, $body ) {
		do_action(
			'qm/debug',
			'{title}: {body}',
			[
				'title' => $title,
				'body'  => $body,
			]
		);
	}
}
