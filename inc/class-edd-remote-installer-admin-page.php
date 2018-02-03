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
		add_options_page( $this->args['title'], $this->args['title'], $this->args['permissions'], $this->args['slug'], array( $this, 'page_content' ) );
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

						// Get the tags.
						$tags = array();
						if ( isset( $product['info']['tags'] ) && $product['info']['tags'] && is_array( $product['info']['tags'] ) ) {
							foreach ( $product['info']['tags'] as $tag ) {
								$tags[] = $tag['slug'];
							}
						}
						?>
						<div class="eddri-product <?php echo esc_attr( implode( ' ', $tags ) ); ?>">
							<div class="thumb">
								<img src="<?php echo esc_url_raw( $product['info']['thumbnail'] ); ?>">
							</div>
							<h4><?php echo esc_html( $product['info']['title'] ); ?></h4>
							<div class="actions">
								<?php
								// Add buttons.
								$this->the_actions( $product['info'] );
								?>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
		<div class="edd-ri-overlay-installer hidden">
			<div class="inner">
				<p class="centered">
					<a href="#" class="edd-ri-close-overlay">
						<?php esc_attr_e( 'Close', 'eddri' ); ?>
					</a>
				</a>
				<h2><?php esc_attr_e( 'Install Plugin', 'eddri' ); ?></h2>
				<p class="free"></p>
				<p class="billable">
					<?php esc_attr_e( 'If you have a license key for this product, please enter it below and then register your site. Once this is done you will be able to install this plugin on your site. If you do not have a valid license for this product, you can click the "Buy Now" link to purchase it.', 'eddri' ); ?>
				</p>
				<p class="license">
					<input type="text" class="edd-ri-license" placeholder="<?php esc_attr_e( 'Enter License Key', 'eddri' ); ?>">
				</p>
				<p class="license-actions">
					<a class="edd-ri-buy-now button button-secondary"><?php esc_attr_e( 'Buy Now', 'eddri' ); ?></a>
					<a class="edd-ri-action button button-primary"><?php esc_attr_e( 'Register', 'eddri' ); ?></a>
					<a class="edd-ri-install button button-primary"><?php esc_attr_e( 'Install', 'eddri' ); ?></a>
				</p>
				<p class="edd-ri-result"></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Build the action buttons.
	 *
	 * @since 1.0
	 * @access private
	 * @param array $product The product arguments.
	 * @return void
	 */
	private function the_actions( $product = array() ) {

		// The option-name we'll be using for licences etc.
		$option_name = sanitize_key( str_replace( array( ' ', '-' ), '_', trim( $product['slug'] ) ) );

		// Build the buy link.
		$buy_url = add_query_arg(
			array(
				'edd_action' => 'add_to_cart',
				'download_id' => absint( $product['id'] ),
			),
			esc_url_raw( $this->args['api_url'] )
		);

		// Build the install button.
		$button_args = array(
			'class'            => 'button button-primary edd-ri-install',
			'data-api_uri'     => trailingslashit( $this->args['api_url'] ),
			'data-slug'        => $product['slug'],
			'data-option_slug' => $option_name,
			'data-item_name'   => $product['title'],
			'data-buy_uri'     => esc_url_raw( $buy_url ),
			'data-license'     => get_option( 'edd_ri_' . $option_name . '_license', '' ),
			'data-nonce'       => wp_create_nonce( 'edd_ri' ),
		);

		// The button.
		$this->the_button( $button_args, esc_attr__( 'Download', 'eddri' ) );
	}

	/**
	 * Renders a button.
	 *
	 * @access private
	 * @since 1.0
	 * @param array  $attrs The button attributes.
	 * @param string $text  The button text.
	 * @return void
	 */
	private function the_button( $attrs, $text ) {
		$button = '<button';
		foreach ( $attrs as $key => $value ) {
			$button .= ' ' . $key . '="' . $value . '"';
		}
		$button .= '>' . $text . '</button>';
		echo $button; // WPCS: XSS ok.
	}
}
