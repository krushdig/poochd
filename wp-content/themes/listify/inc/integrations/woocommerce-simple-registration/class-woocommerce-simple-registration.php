<?php
/**
 * WooCommerce - Simple Registration
 *
 * @since 1.8.0
 * @package Listify
 * @subpackage WooCommerce Simple Registration
 */
class Listify_WooCommerce_Simple_Registration extends Listify_Integration {

	/**
	 * Simple Registration instance.
	 *
	 * @var WooCommerce_Simple_Registration
	 * @access public
	 */
	public $wc_simple_registration;

	/**
	 * Define the integration.
	 *
	 * @since 1.8.0
	 * @return void
	 */
	public function __construct() {
		$this->integration = 'woocommerce-simple-registration';
		$this->includes = array();

		if ( ! function_exists( 'init_woocommerce_social_login' ) ) {
			return;
		}

		$this->wc_simple_registration = WooCommerce_Simple_Registration_WC_Social_Login::get_instance();

		parent::__construct();
	}

	/**
	 * Hook in to WordPress
	 *
	 * @since 1.8.0
	 * @return void
	 */
	public function setup_actions() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Everything in this plugin happens after the `init` hook.
	 *
	 * @since 1.8.0
	 * @return void
	 */
	public function init() {
		if ( ! $this->wc_simple_registration->is_displayed_on() ) {
			return;
		}

		// Remove social login in the end of WC form.
		remove_action( 'woocommerce_register_form_end', array( $this->wc_simple_registration, 'render_social_login_buttons' ) );

		// Add it on top of the form and add devider.
		add_action( 'woocommerce_register_form_start', array( $this->wc_simple_registration, 'render_social_login_buttons' ), 5 );
		add_action( 'woocommerce_register_form_start', array( $this, 'render_social_login_buttons_divider' ), 6 );
	}

	/**
	 * Add an "or" divider below the Social Login buttons.
	 *
	 * @since 1.8.0
	 * @return void
	 */
	public function render_social_login_buttons_divider() {
?>

<p class="wc-social-login-divider wc-social-login-divider--register">
	<span><?php echo esc_attr( _x( 'or', 'social login divider', 'listify' ) ); ?></span>
</p>

<?php
	}

}

new Listify_WooCommerce_Simple_Registration();
