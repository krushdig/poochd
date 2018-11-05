<?php
/**
 * Load the application.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Bootstrap
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

// Load helper functions.
require_once( ASTOUNDIFY_WPJMLP_PATH . 'app/functions-utility.php' );
require_once( ASTOUNDIFY_WPJMLP_PATH . 'app/functions-package.php' );
require_once( ASTOUNDIFY_WPJMLP_PATH . 'app/functions-user.php' );

/**
 * Initialize plugin.
 *
 * @since 2.0.0
 */
add_action( 'plugins_loaded', function() {

	// Load text domain.
	load_plugin_textdomain( dirname( ASTOUNDIFY_WPJMLP_PLUGIN ), false, dirname( ASTOUNDIFY_WPJMLP_PLUGIN ) . '/resources/languages/' );

	// Bail if required plugin not active.
	if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WP_Job_Manager' ) || function_exists( 'wp_job_manager_wcpl_init' ) ) {
		return;
	}

	// WooCommerce Subscriptions.
	if ( class_exists( '\WC_Subscriptions' ) ) {
		WC_Subscriptions_Payments::get_instance();
	}

	// User Packages.
	User_Packages::init();

	// WP Job Manager.
	Job_Manager_Post_Status::init();
	Job_Manager_Settings::init();
	Job_Manager_Submit_Form::init();
	Job_Manager_WC_Cart::init();
	Job_Manager_WC_Orders::init();
	require_once( ASTOUNDIFY_WPJMLP_PATH . 'app/job-manager/class-wc-product-job-package.php' );
	Job_Manager_WC_Product_Setup::init();
	Job_Manager_Writepanels::init();
	Job_Manager_Edit::init();
	if ( class_exists( '\WC_Subscriptions' ) ) {
		require_once( ASTOUNDIFY_WPJMLP_PATH . 'app/job-manager/class-wc-product-job-package-subscription.php' );
		Job_Manager_WC_Subs_Product_Setup::init();
	}

	// Resume Manager.
	if ( class_exists( '\WP_Resume_Manager' ) ) {
		Resume_Manager_Post_Status::init();
		Resume_Manager_Settings::init();
		Resume_Manager_Submit_Form::init();
		Resume_Manager_WC_Cart::init();
		Resume_Manager_WC_Orders::init();
		require_once( ASTOUNDIFY_WPJMLP_PATH . 'app/resume-manager/class-wc-product-resume-package.php' );
		Resume_Manager_WC_Product_Setup::init();
		Resume_Manager_Writepanels::init();
		Resume_Manager_Edit::init();
		if ( class_exists( '\WC_Subscriptions' ) ) {
			require_once( ASTOUNDIFY_WPJMLP_PATH . 'app/resume-manager/class-wc-product-resume-package-subscription.php' );
			Resume_Manager_WC_Subs_Product_Setup::init();
		}
	}

} );
