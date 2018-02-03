<?php
/**
 * Build buttons for actions, and handle the actions themselves.
 *
 * @package     EDD Remote Installer
 * @category    Core
 * @author      Aristeides Stathopoulos
 * @copyright   Copyright (c) 2017, Aristeides Stathopoulos
 * @license     http://opensource.org/licenses/https://opensource.org/licenses/MIT
 * @since       1.0
 */

/**
 * Build buttons for actions, and handle the actions themselves.
 *
 * @since 1.0
 */
class EDD_Remote_Installer_Actions {

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'wp_ajax_edd_ri_activate_license', array( $this, 'activate_license' ) );
	}

	/**
	 * Activates a license.
	 *
	 * @access public
	 * @since 1.0
	 */
	public function activate_license() {

		// Security check.
		if ( ! isset( $_POST['nonce'] ) ) {
			return;
		}
		check_ajax_referer( 'edd_ri', 'nonce' );

		// Listen for our activate button to be clicked.
		if ( ! isset( $_POST['action'] ) || 'edd_ri_activate_license' !== sanitize_key( wp_unslash( $_POST['action'] ) ) ) {
			return;
		}

		$license   = ( isset( $_POST['license'] ) ) ? trim( sanitize_key( $_POST['license'] ) ) : '';
		$item_name = ( isset( $_POST['item_name'] ) ) ? sanitize_text_field( wp_unslash( $_POST['item_name'] ) ) : '';
		$api_url   = ( isset( $_POST['api_uri'] ) ) ? esc_url_raw( wp_unslash( $_POST['api_uri'] ) ) : '';

		// Call the custom API.
		$response = wp_remote_post(
			$api_url, array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => array(
					'edd_action' => 'activate_license',
					'license'    => $license,
					'item_name'  => urlencode( $item_name ),
					'url'        => home_url(),
				),
			)
		);

		// Make sure the response came back ok.
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$message = ( is_wp_error( $response ) ) ? $response->get_error_message() : esc_attr__( 'An error occurred, please try again.', 'textdomain' );
		} else {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( false === $license_data->success ) {
				switch ( $license_data->error ) {
					case 'expired':
						$message = sprintf(
							/* translators: date. */
							esc_attr__( 'Your license key expired on %s.', 'textdomain' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;
					case 'revoked':
						$message = esc_attr__( 'Your license key has been disabled.', 'textdomain' );
						break;
					case 'missing':
						$message = esc_attr__( 'Invalid license.', 'textdomain' );
						break;
					case 'invalid':
					case 'site_inactive':
						$message = esc_attr__( 'Your license is not active for this URL.', 'textdomain' );
						break;
					case 'item_name_mismatch':
						/* translators: plugin-name. */
						$message = sprintf( esc_attr__( 'This appears to be an invalid license key for %s.', 'textdomain' ), $this->args['item_name'] );
						break;
					case 'no_activations_left':
						$message = esc_attr__( 'Your license key has reached its activation limit.', 'textdomain' );
						break;
					default:
						$message = esc_attr__( 'An error occurred, please try again.' );
						break;
				}
			}
		}

		// Check if anything passed on a message constituting a failure.
		if ( ! empty( $message ) ) {
			wp_send_json_success(
				array(
					'message' => $message,
					'status'  => 'fail',
				)
			);
			wp_die();
		}

		if ( isset( $_POST['option_slug'] ) ) {

			$option_slug = sanitize_key( wp_unslash( $_POST['option_slug'] ) );

			// $license_data->license will be either "valid" or "invalid".
			update_option( "edd_ri_{$option_slug}_license_status", $license_data->license );
			update_option( "edd_ri_{$option_slug}_license", $license );
			wp_send_json_success(
				array(
					'message' => esc_attr__( 'Success', 'eddri' ),
					'status'  => 'success',
				)
			);
		}
		wp_die();
	}
}
