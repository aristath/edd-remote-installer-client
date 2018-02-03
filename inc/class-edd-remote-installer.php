<?php
/**
 * The main EDD_Remote_Installer class.
 *
 * @package     EDD Remote Installer
 * @category    Core
 * @author      Aristeides Stathopoulos
 * @copyright   Copyright (c) 2017, Aristeides Stathopoulos
 * @license     http://opensource.org/licenses/https://opensource.org/licenses/MIT
 * @since       1.0
 */

/**
 * The main EDD_Remote_Installer class.
 *
 * @since 1.0
 */
class EDD_Remote_Installer {

	/**
	 * The remote server domain.
	 *
	 * @access private
	 * @since 1.0
	 * @var string
	 */
	private $remote_api = 'https://presscodes.com';

	/**
	 * Instance arguments.
	 *
	 * @access private
	 * @since 1.0
	 * @var array
	 */
	private $args = array();

	/**
	 * An array of all the instances.
	 *
	 * @static
	 * @access private
	 * @since 1.0
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Constructor.
	 *
	 * @access private
	 * @since 1.0
	 * @param array $args The arguments required to init the object.
	 */
	private function __construct( $args ) {
		$this->args         = $args;
		$api                = new EDD_Remote_Installer_API( $args );
		$this->args['data'] = $api->get_data();
		new EDD_Remote_Installer_Admin_Page( $this->args );
		new EDD_Remote_Installer_Actions();
		$plugin_installer = new EDD_Remote_Installer_Plugin_Install();
		$plugin_installer->set_api_url( $args['api_url'] );
	}

	/**
	 * Gets an instance of the class.
	 * If the instance with the args we're passing does not already exist, then one will be created.
	 *
	 * @static
	 * @access public
	 * @since 1.0
	 * @param array $args The arguments required to init the object.
	 * @return EDD_Remote_Installer An instance of this class.
	 */
	public static function get_instance( $args ) {

		// If we don't have a "slug" defined, then we need to exit early
		// otherwise we'll just get undefined index errors.
		if ( ! isset( $args['slug'] ) ) {
			return;
		}

		// Make sure the slug is properly sanitized.
		$args['slug'] = sanitize_key( $args['slug'] );

		// If an instance with this slug does not exist, create it.
		if ( ! isset( self::$instances[ $args['slug'] ] ) ) {
			self::$instances[ $args['slug'] ] = new self( $args );
		}

		// Return the object.
		return self::$instances[ $args['slug'] ];
	}

}
