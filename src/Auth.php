<?php
/**
 * Define Authentication for the API.
 * 
 * @package viewpoint
 */

namespace Viewpoint\ViewpointApi;

class Auth {
	public function __construct(
		public string $type,
		private Api $api,
		public array $headers = [],
		private string $username = '',
		private string $password = '',
		private string $accessToken = 'Test',
		private int $accessTokenExpiry = 0,
		private string $refreshToken = '',
	) {
	}

	public function addAuthentication() {
		switch ( $this->type ) {
			case 'Basic':
				$this->api->headers['Authorization'] = 'Basic ' . $this->accessToken;
				break;
			case 'JWT':
				$this->api->headers['Authorization'] = 'Bearer ' . $this->accessToken;
				break;
		}
	}
}
