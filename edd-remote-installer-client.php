<?php
/**
 * Plugin Name: EDD Remote Installer Client
 * Plugin URI: https://presscodes.com
 * Author: Aristeides Stathopoulos
 * Author URI: http://aristath.github.io
 * Version: 1.0
 * Text Domain: eddri-client
 *
 * @package EDD Remote Installer Server
 * @category Core
 * @author Aristeides Stathopoulos
 * @version 1.0
 */

if ( ! class_exists( 'EDD_Remote_Installer' ) ) {
	require_once 'inc/class-edd-remote-installer.php';
}
if ( ! class_exists( 'EDD_Remote_Installer_Admin_Page' ) ) {
	require_once 'inc/class-edd-remote-installer-admin-page.php';
}
if ( ! class_exists( 'EDD_Remote_Installer_API' ) ) {
	require_once 'inc/class-edd-remote-installer-api.php';
}
if ( ! class_exists( 'EDD_Remote_Installer_Actions' ) ) {
	require_once 'inc/class-edd-remote-installer-actions.php';
}
if ( ! class_exists( 'EDD_Remote_Installer_Plugin_Install' ) ) {
	require_once 'inc/class-edd-remote-installer-plugin-install.php';
}

EDD_Remote_Installer::get_instance(
	array(
		'api_url'      => 'https://presscodes.com',
		'slug'         => 'presscodes',
		/* translators: PressCodes (company name). */
		'title'        => sprintf( esc_attr__( '%s Products', 'eddri' ), 'PressCodes' ),
		'permissions'  => 'manage_options',
		'eddri_url'    => plugins_url( '', __FILE__ ),
		'account_page' => 'https://presscodes.com/account/'
	)
);
