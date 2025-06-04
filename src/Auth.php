<?php
/**
 * Define Authentication for the API.
 *
 * @package viewpoint
 */

namespace Viewpoint\ViewpointApi;

class Auth {
	/**
	 * Construct Auth interface.
	 *
	 * @param string   $type              Authentication type: Basic, JWT, oAuth.
	 * @param Api      $api               API to apply authentication to.
	 * @param Endpoint $authEndpoint      Endpoint to authenticate against.
	 * @param string   $accessToken       Access token.
	 * @param int      $accessTokenExpiry Access token expiry in seconds.
	 */
	public function __construct(
		public string $type,
		public Api $api,
		private Endpoint $authEndpoint,
		private string $accessToken = '',
		private int $accessTokenExpiry = 0,
	) {
	}

	/**
	 * Authenticate the api.
	 *
	 * @return void
	 */
	public function authenticate() {
		switch ( $this->type ) {
			case 'Basic':
				$this->authenticateBasic();
				break;
			case 'JWT':
				$this->authenticateJWT();
				break;
		}
	}

	/**
	 * Authenticate Basic.
	 *
	 * @return void
	 */
	private function authenticateBasic() {
		$this->api->headers['Authorization'] = 'Basic ' . $this->accessToken;
	}

	/**
	 * Authenticate JWT.
	 *
	 * @return void
	 */
	private function authenticateJWT() {
		$authTokenTransient = new Transient(
			name: $this->authEndpoint->api->name . '_auth',
			setFresh: false,
		);
		if ( $authTokenTransient->get() ) {
			$this->accessToken = $authTokenTransient->get();
		} else {
			$authRequest = $this->authEndpoint->request();
			if ( $authRequest && isset( $authRequest->access_token ) ) {
				$this->accessToken       = $authRequest->access_token;
				$this->accessTokenExpiry = $authRequest->expires_in;
				$authTokenTransient->set( $this->accessToken, $this->accessTokenExpiry );
			}
		}
		$this->api->headers['Authorization'] = 'Bearer ' . $this->accessToken;
	}
}
