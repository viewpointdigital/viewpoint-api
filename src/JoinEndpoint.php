<?php
/**
 * Define API JoinEndpoint
 *
 * @package viewpoint
 */

namespace Viewpoint\ViewpointApi;

/**
 * Define API JoinEndpoint to merge two endpoints.
 */
class JoinEndpoint {

	/**
	 * Create join endpoint.
	 *
	 * @param Endpoint $endpoint1 First endpoint.
	 * @param string   $joinKey   Key to use as param for second endpoint.
	 * @param Endpoint $endpoint2 Second endpoint.
	 */
	public function __construct(
		public Endpoint $endpoint1,
		public string $joinKey,
		public Endpoint $endpoint2,
	) {
	}

	/**
	 * Initialize a series of requests to second endpoint with params from first request.
	 *
	 * @return mixed
	 */
	public function request() {
		$items          = [];
		$endpoint1Items = $this->endpoint1->request();
		if ( is_array( $endpoint1Items ) ) {
			foreach ( $endpoint1Items as $endpoint1Item ) {
				if ( property_exists( $endpoint1Item, $this->joinKey ) ) {
					$this->endpoint2->updateParams( $this->joinKey, $endpoint1Item->{$this->joinKey} );
					$endpoint2Items = $this->endpoint2->request();
					if ( $endpoint2Items ) {
						array_push( $items, ...$endpoint2Items );
					}
				}
			}
		}
		return $items;
	}
}
