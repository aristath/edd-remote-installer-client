<?php
/**
 * Creates an admin page.
 *
 * @package     EDD Remote Installer
 * @category    Core
 * @author      Aristeides Stathopoulos
 * @copyright   Copyright (c) 2017, Aristeides Stathopoulos
 * @license     http://opensource.org/licenses/https://opensource.org/licenses/MIT
 * @since       1.0
 */

/**
 * Creates an admin page.
 *
 * @since 1.0
 */
class EDD_Remote_Installer_Admin_Page {

	/**
	 * Instance arguments.
	 *
	 * @access private
	 * @since 1.0
	 * @var array
	 */
	private $args = array();

	/**
	 * An instance of the EDD_Remote_Installer_Plugin_Install class.
	 *
	 * @access private
	 * @since 1.0
	 * @var EDD_Remote_Installer_Plugin_Install
	 */
	private $installer;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $args The arguments required to init the object.
	 */
	public function __construct( $args ) {
		$this->args = $args;
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Add the admin menu.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function admin_menu() {
		add_plugins_page( $this->args['title'], $this->args['title'], $this->args['permissions'], $this->args['slug'], array( $this, 'page_content' ) );
	}

	/**
	 * Add scripts and styles.
	 *
	 * @since 1.0
	 * @access public
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'edd_ri_admin_css', trailingslashit( $this->args['eddri_url'] ) . 'assets/css/styles.css', false, '1.0' );
		wp_enqueue_script( 'edd_ri_admin_js', trailingslashit( $this->args['eddri_url'] ) . 'assets/js/edd-ri.js', array( 'jquery', 'underscore' ), time(), true );
	}

	/**
	 * The page contents.
	 *
	 * @since 1.0
	 * @access public
	 * @return void
	 */
	public function page_content() {
		?>
		<div class="wrap">
			<h2><?php echo esc_attr( $this->args['title'] ); ?></h2>
			<div class="products-wrapper">
				<?php if ( isset( $this->args['data']['products'] ) ) : ?>
					<?php foreach ( $this->args['data']['products'] as $product ) : ?>
						<?php
						// Skip item if we don't have info.
						if ( ! isset( $product['info'] ) ) {
							continue;
						}
						?>
						<div class="eddri-product <?php $this->the_tags(); ?>">
							<div class="thumb">
								<img src="<?php echo esc_url_raw( $product['info']['thumbnail'] ); ?>">
							</div>
							<h4><?php echo esc_html( $product['info']['title'] ); ?></h4>
							<div class="actions">
								<?php if ( ! $this->is_installed( $product ) ) : ?>
									<a class="button button-primary thickbox" href="#TB_inline?width=600&height=300&inlineId=edd-ri-tb-<?php echo esc_attr( $product['info']['slug'] ); ?>" class="thickbox">
										<?php esc_attr_e( 'Install', 'eddri' ); ?>
									</a>
								<?php else : ?>
									<?php if ( $this->is_billable( $product ) ) : ?>
										<?php if ( $this->is_registered( $product ) ) : ?>
											<a class="button button-primary thickbox" href="#TB_inline?width=600&height=300&inlineId=edd-ri-tb-<?php echo esc_attr( $product['info']['slug'] ); ?>" class="thickbox">
												<?php esc_attr_e( 'Manage License', 'eddri' ); ?>
											</a>
										<?php else : ?>
											<a class="button button-primary thickbox" href="#TB_inline?width=600&height=300&inlineId=edd-ri-tb-<?php echo esc_attr( $product['info']['slug'] ); ?>" class="thickbox">
												<?php esc_attr_e( 'Register', 'eddri' ); ?>
											</a>
										<?php endif; ?>
									<?php else : ?>
										<a class="button button-primary thickbox" href="#TB_inline?width=600&height=300&inlineId=edd-ri-tb-<?php echo esc_attr( $product['info']['slug'] ); ?>" class="thickbox">
											<?php esc_attr_e( 'Register', 'eddri' ); ?>
										</a>
									<?php endif; ?>
								<?php endif; ?>
								<div id="edd-ri-tb-<?php echo esc_attr( $product['info']['slug'] ); ?>" class="edd-ri-tb" style="display:none;">
									<?php if ( $this->is_billable( $product ) ) : ?>
										<div class="edd-ri-form-licensing">
											<h3><?php esc_attr_e( 'Product Registration', 'eddri' ); ?></h3>
											<p class="edd-ri-register-text<?php echo ( ! $this->is_registered( $product ) ) ? '' : ' hidden'; ?>">
												<?php
												printf(
													/* translators: Link to the plugin's site, with the "purchase this product" text. */
													esc_attr__( 'Please use the form below to register your product. If you do not have a valid license, please %s before proceeding.', 'eddri' ),
													'<a target="_blank" href="' . esc_url_raw( $this->get_the_buy_link( $product ) ) . '">' . esc_attr__( 'purchase this product', 'eddri' ) . '</a>'
												);
												?>
											</p>
											<?php if ( isset( $this->args['account_page'] ) ) : ?>
												<p class="edd-ri-thankyou-text<?php echo ( ! $this->is_registered( $product ) ) ? ' hidden' : ''; ?>">
													<?php
													printf(
														/* translators: Link to the account page where users can manage their licenses. Link text: "account". */
														esc_attr__( 'You can manage activated sites and deregister your license for this site from your %s page on our site.', 'eddri' ),
														'<a target="_blank" href="' . esc_url_raw( $this->args['account_page'] ) . '">' . esc_attr__( 'account', 'eddri' ) . '</a>'
													);
													?>
												</p>
											<?php endif; ?>
											<div class="edd-ri-tb-form">
												<div class="license"><input type="text" class="edd-ri-license" data-slug="<?php echo esc_attr( $product['info']['slug'] ); ?>" placeholder="<?php esc_attr_e( 'Enter License Key', 'eddri' ); ?>" value="<?php echo esc_attr( $this->get_license( $product['info']['slug'] ) ); ?>"></div>
												<div class="license-actions edd-ri-register <?php echo ( ! $this->is_registered( $product ) ) ? '' : ' hidden'; ?>">
													<?php $this->the_button( $product, 'edd_ri_activate_license', esc_attr__( 'Register & Activate key', 'eddri' ) ); ?>
												</div>
												<div class="license-actions edd-ri-update <?php echo ( ! $this->is_registered( $product ) ) ? ' hidden' : ''; ?>">
													<?php $this->the_button( $product, 'edd_ri_activate_license', esc_attr__( 'Update key', 'eddri' ), 'secondary' ); ?>
												</div>
											</div>
											<p class="edd-ri-install-pending-text<?php echo ( ! $this->is_registered( $product ) ) ? '' : ' hidden'; ?>">
												<?php esc_attr_e( 'Once your registration is successfully you will be able to continue with the plugin installation.', 'eddri' ); ?>
											</p>
											<?php if ( ! $this->is_installed( $product ) ) : ?>
												<p class="edd-ri-install-button<?php echo ( ! $this->is_registered( $product ) ) ? ' hidden' : ''; ?>">
													<?php $this->the_button( $product, 'edd_ri_install', esc_attr__( 'Install', 'eddri' ) ); ?></div>
												</p>
											<?php else : ?>
												<p class="edd-ri-message-plugin-installed"><?php esc_attr_e( 'Plugin is installed.', 'eddri' ); ?></p>
											<?php endif; ?>
											<div class="edd-ri-response"></div>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
		add_thickbox();
	}

	/**
	 * Build the action buttons.
	 *
	 * @since 1.0
	 * @access private
	 * @param array  $product The product arguments.
	 * @param string $action  The AJAX action we want this button to perform.
	 * @param string $text    The button text.
	 * @param string $type    Button type (primary|secondary).
	 * @return void
	 */
	private function the_button( $product = array(), $action = '', $text = '', $type = 'primary' ) {

		// The option-name we'll be using for licences etc.
		$option_name = sanitize_key( str_replace( array( ' ', '-' ), '_', trim( $product['info']['slug'] ) ) );

		// Build the install button.
		$attrs = array(
			'class'            => 'button button-' . $type . ' edd-ri-button',
			'data-api_uri'     => trailingslashit( $this->args['api_url'] ),
			'data-slug'        => $product['info']['slug'],
			'data-option_slug' => $option_name,
			'data-item_name'   => $product['info']['title'],
			'data-license'     => $this->get_license( $product['info']['slug'] ),
			'data-nonce'       => wp_create_nonce( 'edd_ri' ),
			'data-action'      => esc_attr( $action ),
		);

		// The button.
		echo '<button';
		foreach ( $attrs as $key => $value ) {
			echo ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}
		echo '>' . esc_html( $text ) . '</button>';
	}

	/**
	 * Get the license of a product.
	 *
	 * @access private
	 * @since 1.0
	 * @param string $slug The product-slug.
	 * @return string
	 */
	private function get_license( $slug ) {

		// The option-name we'll be using for licences.
		$option_name = sanitize_key( str_replace( array( ' ', '-' ), '_', trim( $slug ) ) );
		return get_option( "edd_ri_{$option_name}_license", '' );
	}

	/**
	 * Return the buy link.
	 *
	 * @access private
	 * @since 1.0
	 * @param array $product The product.
	 * @return void|string
	 */
	private function get_the_buy_link( $product ) {
		return add_query_arg(
			array(
				'edd_action' => 'add_to_cart',
				'download_id' => absint( $product['info']['id'] ),
			),
			$this->args['api_url']
		);
	}

	/**
	 * Print the product tags.
	 *
	 * @access private
	 * @since 1.0
	 * @param array $product The product arguments.
	 * @return void
	 */
	private function the_tags( $product = array() ) {
		// Get the tags.
		$tags = array();
		if ( isset( $product['info']['tags'] ) && $product['info']['tags'] && is_array( $product['info']['tags'] ) ) {
			foreach ( $product['info']['tags'] as $tag ) {
				$tags[] = $tag['slug'];
			}
		}
		echo esc_attr( implode( ' ', $tags ) );
	}

	/**
	 * Figure out if a product is installed or not.
	 *
	 * @since 1.0
	 * @access private
	 * @uses EDD_Remote_Installer_Plugin_Install
	 * @param array $product The product arguments.
	 * @return bool
	 */
	private function is_installed( $product ) {
		if ( ! $this->installer ) {
			$this->installer = new EDD_Remote_Installer_Plugin_Install();
		}
		return $this->installer->is_plugin_installed( $product['info']['title'] );
	}

	/**
	 * Figure out if a product is billable.
	 *
	 * @access private
	 * @since 1.0
	 * @param array $product The product.
	 * @return bool
	 */
	private function is_billable( $product ) {
		if ( isset( $product['pricing'] ) && isset( $product['pricing']['amount'] ) && 0 < floatval( $product['pricing']['amount'] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Figure out if a product is registered.
	 *
	 * @access private
	 * @since 1.0
	 * @param array $product The product.
	 * @return bool
	 */
	private function is_registered( $product ) {
		if ( ! isset( $product['info'] ) || ! isset( $product['info']['slug'] ) ) {
			return false;
		}
		$option_slug = sanitize_key( str_replace( array( ' ', '-' ), '_', trim( $product['info']['slug'] ) ) );

		if ( 'valid' === get_option( "edd_ri_{$option_slug}_license_status", '' ) ) {
			return true;
		}
		return false;
	}
}
