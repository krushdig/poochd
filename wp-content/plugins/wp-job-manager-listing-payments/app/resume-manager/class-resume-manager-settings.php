<?php
/**
 * Resume Settings.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Resume Manager
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * Resume Manager Settings
 *
 * @since 2.0.0
 */
class Resume_Manager_Settings {

	/**
	 * Init.
	 *
	 * @since 2.0.0
	 */
	public static function init() {

		// Resume Manager Settings.
		add_filter( 'resume_manager_settings', array( __CLASS__, 'settings' ) );

		// Force "User Requires Account" Option.
		add_filter( 'option_resume_manager_user_requires_account', array( __CLASS__, 'force_user_requires_account' ) );
	}

	/**
	 * Add Settings
	 *
	 * @since 2.0.0
	 *
	 * @param  array $settings Settings Args.
	 * @return array
	 */
	public static function settings( $settings = array() ) {

		// Get "user requires account" field key.
		$key = false;
		foreach ( $settings['resume_submission'][1] as $k => $field ) {
			if ( isset( $field['name'] ) && 'resume_manager_user_requires_account' === $field['name'] ) {
				$key = $k;
			}
		}

		// Disable "user requires account" settings.
		if ( false !== $key ) {
			$settings['resume_submission'][1][ $key ]['type'] = 'astoundify_wpjmlp_notice';
			$settings['resume_submission'][1][ $key ]['desc'] = '<label><input type="checkbox" disabled="disabled" checked="checked" value="1" id="setting-job_manager_user_requires_account"> ' . __( 'Submitting listings requires an account', 'wp-job-manager-listing-payments' ) . '</label> <p class="description">' . __( 'If disabled, non-logged in users will be able to submit listings without creating an account. Please note that this will prevent non-registered users from being able to edit their listings at a later date.', 'wp-job-manager-listing-payments' ) . '<br />' . __( 'User is required to have an account to use Paid Listings feature.', 'wp-job-manager-listing-payments' ) . '</p>';
		}

		// Add Listing Flow Option.
		$settings['resume_submission'][1][] = array(
			'name'      => 'resume_manager_paid_listings_flow',
			'std'       => '',
			'label'     => __( 'Payment Flow', 'wp-job-manager-listing-payments' ),
			'desc'      => __( 'Define when the user should choose a package for submission.', 'wp-job-manager-listing-payments' ),
			'type'      => 'select',
			'options'   => array(
				''                  => __( 'Choose a package after entering resume details', 'wp-job-manager-listing-payments' ),
				'before'            => __( 'Choose a package before entering resume details', 'wp-job-manager-listing-payments' ),
				'payment_required'  => __( 'Payment required before entering job details', 'wp-job-manager-listing-payments' ),
			),
		);

		// Payment settings.
		$settings['astoundify_wpjmlp'] = array(
			__( 'Payments', 'wp-job-manager-listing-payments' ),
			array(),
		);

		$settings['astoundify_wpjmlp'][1][] = array(
			'name'          => 'resume_manager_paid_listings_simple_checkout',
			'type'          => 'checkbox',
			'std'           => '',
			'placeholder'   => '',
			'label'         => __( 'Checkout', 'wp-job-manager-listing-payments' ),
			'cb_label'      => __( 'Enable Simple Checkout', 'wp-job-manager-listing-payments' ),
			'desc'          => __( 'Use simple checkout for free listing packages. This removes all required billing and shipping fields.', 'wp-job-manager-listing-payments' ),
			'attributes'    => array(),
		);

		$settings['astoundify_wpjmlp'][1][] = array(
			'name'          => 'resume_manager_paid_listings_switch_package',
			'type'          => 'checkbox',
			'std'           => '',
			'placeholder'   => '',
			'label'         => __( 'Switch Package', 'wp-job-manager-listing-payments' ),
			'cb_label'      => __( 'Allow user to switch listing packages.', 'wp-job-manager-listing-payments' ),
			'desc'          => __( 'Allow user to switch to different package when editing listing.', 'wp-job-manager-listing-payments' ),
			'attributes'    => array(),
		);

		return $settings;
	}

	/**
	 * Force User Requires Account.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $value Value.
	 */
	public static function force_user_requires_account( $value ) {
		return 1;
	}

}
