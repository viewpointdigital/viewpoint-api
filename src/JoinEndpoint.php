<?php
/**
 * Define API JoinEndpoint
 *
 * @package viewpoint
 */

namespace Viewpoint\ViewpointApi;

use Viewpoint\ViewpointSyncedPosts\Map;

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
	 * @param Map|null $map Post map.
	 */
	public function __construct(
		public Endpoint $endpoint1,
		public string $joinKey,
		public Endpoint $endpoint2,
		public Map|null $map = null,
	) {
	}

	/**
	 * Initialize a series of requests to second endpoint with params from first request.
	 *
	 * @return mixed
	 */
	public function request() {
		$items          = [];
		$endpoint1Items = apply_filters( 'viewpoint_joint_endpoint1_results', $this->endpoint1->request(), $this->map );
		if ( is_array( $endpoint1Items ) ) {
			foreach ( $endpoint1Items as $endpoint1Item ) {
				if ( property_exists( $endpoint1Item, $this->joinKey ) ) {
					$this->endpoint2->updateParams( $this->joinKey, $endpoint1Item->{$this->joinKey} );
					$endpoint2Items = apply_filters( 'viewpoint_joint_endpoint2_results', $this->endpoint2->request(), $this->map );
					if ( $endpoint2Items ) {
						array_push( $items, ...$endpoint2Items );
					}
				}
			}
		}
		return $items;
	}
}
