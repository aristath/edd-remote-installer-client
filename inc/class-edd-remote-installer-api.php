<?php
/**
 * Query the remote API, get product details, cache results etc.
 *
 * @package     EDD Remote Installer
 * @category    Core
 * @author      Aristeides Stathopoulos
 * @copyright   Copyright (c) 2017, Aristeides Stathopoulos
 * @license     http://opensource.org/licenses/https://opensource.org/licenses/MIT
 * @since       1.0
 */

/**
 * Query the remote API, get product details, cache results etc.
 *
 * @since 1.0
 */
class EDD_Remote_Installer_API {

	/**
	 * Instance arguments.
	 *
	 * @access private
	 * @since 1.0
	 * @var array
	 */
	private $args = array();

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $args The arguments required to init the object.
	 */
	public function __construct( $args ) {
		$this->args = $args;
	}

	/**
	 * Get the data.
	 *
	 * @access public
	 * @since 1.0
	 * @return array|false Returns the products array on success, false on error.
	 */
	public function get_data() {
		$transient_name = 'eddri_data_' . $this->args['slug'];
		$data           = get_site_transient( $transient_name );
		// If we're currently debugging, don't cache.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'EDD_RI_DEBUG' ) && EDD_RI_DEBUG ) {
			$data = false;
		}

		// If data was not found, get and cache.
		if ( ! $data ) {
			$data = $this->query_remote_api();
			if ( $data ) {
				set_site_transient( $transient_name, $data, 12 * HOUR_IN_SECONDS );
			}
		}
		return $data;
	}

	/**
	 * Query the remote API and get the products.
	 *
	 * @since 1.0
	 * @access private
	 * @return array|false Returns the products array on success, false on error.
	 */
	private function query_remote_api() {
		// Build the URL.
		$url = trailingslashit( $this->args['api_url'] ) . 'edd-api/products/';

		// Get the response from the remote server.
		$response = wp_remote_get( $url );

		// Check for error.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// Parse remote HTML file.
		$data = wp_remote_retrieve_body( $response );

		// Check for error.
		if ( is_wp_error( $data ) ) {
			return false;
		}

		return json_decode( $data, true );
	}
}
