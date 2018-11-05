<?php
/**
 * Job Manager Job Status.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Job Manager
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * Job Manager Settings
 *
 * @since 2.0.0
 */
class Job_Manager_Settings {

	/**
	 * Init
	 *
	 * @since 2.0.0
	 */
	public static function init() {

		// Register Custom Field.
		add_filter( 'wp_job_manager_admin_field_astoundify_wpjmlp_notice', array( __CLASS__, 'settings_field_notice_cb' ), 10, 4 );

		// Job Manager Settings.
		add_filter( 'job_manager_settings', array( __CLASS__, 'settings' ) );
	}

	/**
	 * Notice Settings Field Callback
	 *
	 * @since 2.0.0
	 *
	 * @param array  $option       Option.
	 * @param array  $attributes   Attributes.
	 * @param mixed  $value        Value.
	 * @param string $placeholder  Placeholder.
	 * @return void
	 */
	public static function settings_field_notice_cb( $option, $attributes, $value, $placeholder ) {
		echo wp_kses_post( $option['desc'] );
	}

	/**
	 * Add Settings.
	 *
	 * @since 2.0.0
	 *
	 * @param  array $settings Setttings Args.
	 * @return array
	 */
	public static function settings( $settings = array() ) {

		// Add Listing Flow Option.
		$settings['job_submission'][1][] = array(
			'name'      => 'job_manager_paid_listings_flow',
			'std'       => '',
			'label'     => __( 'Payment Flow', 'wp-job-manager-listing-payments' ),
			'desc'      => __( 'Define when the user should choose a package for submission.', 'wp-job-manager-listing-payments' ),
			'type'      => 'select',
			'options'   => array(
				''                  => __( 'Choose a package after entering details', 'wp-job-manager-listing-payments' ),
				'before'            => __( 'Choose a package before entering details', 'wp-job-manager-listing-payments' ),
				'payment_required'  => __( 'Payment required before entering details', 'wp-job-manager-listing-payments' ),
			),
		);

		// Payment settings.
		$settings['astoundify_wpjmlp'] = array(
			__( 'Payments', 'wp-job-manager-listing-payments' ),
			array(),
		);

		$settings['astoundify_wpjmlp'][1][] = array(
			'name'          => 'job_manager_paid_listings_simple_checkout',
			'type'          => 'checkbox',
			'std'           => '',
			'placeholder'   => '',
			'label'         => __( 'Checkout', 'wp-job-manager-listing-payments' ),
			'cb_label'      => __( 'Enable Simple Checkout', 'wp-job-manager-listing-payments' ),
			'desc'          => __( 'Use simple checkout for free listing packages. This removes all required billing and shipping fields.', 'wp-job-manager-listing-payments' ),
			'attributes'    => array(),
		);

		$settings['astoundify_wpjmlp'][1][] = array(
			'name'          => 'job_manager_paid_listings_switch_package',
			'type'          => 'checkbox',
			'std'           => '',
			'placeholder'   => '',
			'label'         => __( 'Switch Package', 'wp-job-manager-listing-payments' ),
			'cb_label'      => __( 'Allow user to switch listing packages.', 'wp-job-manager-listing-payments' ),
			'desc'          => __( 'Allow user to switch to different package when editing listing.', 'wp-job-manager-listing-payments' ),
			'attributes'    => array(),
		);

		$settings['astoundify_wpjmlp'][1][] = array(
			'name'          => 'wp-job-manager-listing-payments',
			'type'          => 'wp-job-manager-listing-payments_license',
			'std'           => '',
			'placeholder'   => '',
			'label'         => __( 'License Key', 'wp-job-manager-listing-payments' ),
			'desc'          => __( 'Enter the license key you received with your purchase receipt to continue receiving plugin updates.', 'wp-job-manager-listing-payments' ),
			'attributes'    => array(),
		);

		return $settings;
	}

}
