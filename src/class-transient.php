<?php
/**
 * Define Transient
 *
 * @package viewpoint
 */

namespace Viewpoint\ViewpointApi;

/**
 * Transient stored data.
 */
class Transient {

	/**
	 * Name of transient.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Constructor
	 *
	 * @param string $name Name of transient.
	 * @param int    $cache Cache timeout in minutes. Set false to disable cache.
	 */
	public function __construct( $name, public int $cache = 60 ) {
		$this->name = $this->make_name( $name );
	}

	/**
	 * Namespace transient name.
	 *
	 * @param string $name Name of transient.
	 * @return string
	 */
	private function make_name( $name ) {
		return 'ccb_' . $name;
	}

	/**
	 * Check if transient is still fresh.
	 *
	 * @return mixed
	 */
	public function is_fresh() {
		return get_transient( 'fresh_' . $this->name );
	}

	/**
	 * Get transient value.
	 *
	 * @return mixed
	 */
	public function get() {
		$data = get_transient( $this->name );
		if ( is_serialized( $data ) ) {
			$data = maybe_unserialize( $data );
		}
		return $data;
	}

	/**
	 * Set transient value. Also set a 'fresh' flag transient with expiry. That way, we are never left without data.
	 *
	 * @param mixed $data Data to store.
	 * @return void
	 */
	public function set( $data ) {
		// Set an expiry flag so we don't make another call until an hour has passed. That way we still have stale data as a fallback in case regeneration is not possible.
		set_transient( 'fresh_' . $this->name, true, 60 * $this->cache );
		// Store the values indefinetly.
		if ( ! is_serialized( $data ) ) {
			$data = maybe_serialize( $data );
		}
		set_transient( $this->name, $data, 0 );
	}
}
