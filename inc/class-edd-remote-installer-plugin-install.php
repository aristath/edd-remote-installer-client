<?php
/**
 * Handles installing plugins.
 *
 * @package     EDD Remote Installer
 * @category    Core
 * @author      Aristeides Stathopoulos
 * @copyright   Copyright (c) 2017, Aristeides Stathopoulos
 * @license     http://opensource.org/licenses/https://opensource.org/licenses/MIT
 * @since       1.0
 */

/**
 * Handles installing plugins.
 *
 * @since 1.0
 */
class EDD_Remote_Installer_Plugin_Install {

	/**
	 * The remote-api URI.
	 *
	 * @access private
	 * @since 1.0
	 * @var string
	 */
	private $api_url = '';

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 */
	public function __construct() {

		add_action( 'plugins_api', array( $this, 'plugins_api' ), 99, 3 );
		add_action( 'wp_ajax_edd_ri_install', array( $this, 'install' ) );
	}

	/**
	 * Set the remote-API url.
	 *
	 * @since 1.0
	 * @access public
	 * @param string $url The API URL.
	 * @return void
	 */
	public function set_api_url( $url ) {
		$this->api_url = trailingslashit( esc_url_raw( trim( $url ) ) );
	}

	/**
	 * Hook into the WordPress plugins API.
	 * Allows us to use URLs from our EDD store.
	 *
	 * @since 1.0
	 * @access public
	 * @param false|object|array $api    The result object or array. Default false.
	 * @param string             $action The type of information being requested from the Plugin Installation API.
	 * @param object             $args   Plugin API arguments.
	 */
	public function plugins_api( $api, $action, $args ) {
		if ( 'plugin_information' == $action ) {
			if ( isset( $_POST['edd_ri'] ) ) {
				$license   = isset( $_POST['license'] ) ? sanitize_text_field( wp_unslash( $_POST['license'] ) ) : ''; // WPCS: CSRF ok.
				$item_name = isset( $_POST['item_name'] ) ? sanitize_text_field( wp_unslash( $_POST['item_name'] ) ) : ''; // WPCS: CSRF ok.
				$api_params = array(
					'edd_action' => 'get_download',
					'item_name'  => urlencode( $item_name ),
					'license'    => urlencode( $license ),
				);
				$api = new stdClass();
				$api->name          = $args->slug;
				$api->version       = '';
				$api->download_link = $this->api_url . '?edd_action=get_download&item_name=' . $api_args['item_name'] . '&license=' . $api_args['license'];
			}
		}
		return $api;
	}

	/**
	 * Tries to install the plugin
	 *
	 * @access public
	 * @since 1.0
	 */
	public function install() {
		$this->check_capabilities();
		check_ajax_referer( 'edd_ri', 'nonce' );

		$download = '';
		$license  = '';

		if ( isset( $_POST['item_name'] ) ) {
			$download      = sanitize_text_field( wp_unslash( $_POST['item_name'] ) );
		}
		if ( isset( $_POST['license'] ) ) {
			$license       = sanitize_text_field( wp_unslash( $_POST['license'] ) );
		}
		$message       = esc_attr__( 'An Error Occured', 'eddri' );
		$download_type = $this->_check_download( $download );

		// Throw error of the product is not free and license it empty.
		if ( empty( $download ) || ( empty( $license ) && 'free' !== $download_type ) ) {
			wp_send_json_error( $message );
		}

		// Install the plugin if it's free.
		if ( 'free' === $download_type ) {
			$installed = $this->_install_plugin( $download, '' );
			wp_send_json_success( $installed );
		}

		// Check for license and then install if it's a valid licens.
		if ( $this->_check_license( $license, $download ) ) {
			$installed = $this->_install_plugin( $download, $license );
			wp_send_json_success( $installed );
		} else {
			wp_send_json_error( __( 'Invalid License', 'eddri' ) );
		}
	}

	/**
	 * Check download type.
	 *
	 * @access private
	 * @since 1.0
	 * @param string $download The plugin we want to install.
	 * @return string
	 */
	private function _check_download( $download ) {

		// Check the user's capabilities before proceeding.
		$this->check_capabilities();

		// Early exit if the plugin is already installed.
		if ( $this->is_plugin_installed( $download ) ) {
			wp_die( json_encode( __( 'Already Installed', 'eddri' ) ) );
		}

		// Send our details to the remote server.
		$request  = wp_remote_post(
			$this->api_url, array(
				'timeout' => 15,
				'sslverify' => false,
				'body' => array(
					'edd_action' => 'check_download',
					'item_name'  => urlencode( $download ),
				),
			)
		);
		// Exit if error was detected.
		if ( is_wp_error( $request ) ) {
			return 'error';
		}

		$request  = maybe_unserialize( json_decode( wp_remote_retrieve_body( $request ) ) );
		if ( isset( $request->download ) ) {
			return $request->download;
		}
		return 'invalid';
	}

	/**
	 * Literally installs the plugin
	 *
	 * @param string $download The plugin we want to install.
	 * @param string $license  The license key.
	 *
	 * @return bool
	 */
	private function _install_plugin( $download, $license ) {

		// Build the download link.
		$download_link = add_query_arg(
			array(
				'edd_action' => 'get_download',
				'item_name'  => urlencode( $download ),
				'license'    => $license,
			), $this->api_url
		);

		// Make sure the WP-Core file is loaded.
		if ( ! class_exists( 'Plugin_Upgrader' ) ) {
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		// Install the plugin.
		$upgrader = new Plugin_Upgrader( $skin = new Plugin_Installer_Skin( compact( 'type', 'title', 'url', 'nonce', 'plugin', 'api' ) ) ); // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
		return $upgrader->install( $download_link );
	}

	/**
	 * Checks license against API
	 *
	 * @param string $license  The license.
	 * @param string $download The item-name.
	 * @return bool
	 */
	private function _check_license( $license, $download ) {

		// Get a response from our EDD server.
		$response = wp_remote_get(
			add_query_arg(
				array(
					'edd_action' => 'activate_license',
					'license'    => $license,
					'item_name'  => urlencode( $download ),
				),
				$this->api_url
			),
			array(
				'timeout'   => 15,
				'sslverify' => false,
			)
		);
		// Make sure the response came back okay.
		if ( is_wp_error( $response ) ) {
			return false;
		}
		// Decode the license data.
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		return 'valid' === $license_data->license;
	}

	/**
	 * Checks if plugin is intalled
	 *
	 * @param string $plugin_name The name of the plugin we want to install.
	 * @return bool
	 */
	public function is_plugin_installed( $plugin_name ) {
		if ( empty( $plugin_name ) ) {
			return false;
		}
		foreach ( get_plugins() as $plugin ) {
			if ( $plugin['Name'] === $plugin_name ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if the user is allowed to install the plugin.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function check_capabilities() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			// TODO: Error message.
			return;
		}
	}
}
