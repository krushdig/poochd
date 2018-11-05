<?php
/**
 * Job Manager Post Status.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Job Manager
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * Job Manager Post Status
 * - Register Post Status
 * - Status Change Handle
 *
 * @since 2.0.0
 */
class Job_Manager_Post_Status {

	/**
	 * Init
	 *
	 * @since 2.0.0
	 */
	public static function init() {

		// Register Job Manager Post Status.
		add_action( 'init', array( __CLASS__, 'register_status' ) );
		add_filter( 'job_manager_valid_submit_job_statuses', array( __CLASS__, 'add_valid_status' ) );
		add_filter( 'the_job_status', array( __CLASS__, 'set_status_label' ), 10, 2 );

		// Set Job Expiry.
		add_action( 'init', array( __CLASS__, 'set_expiry' ), 12 );
	}

	/**
	 * Register "Pending Payment" Post Status.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public static function register_status() {
		register_post_status( 'pending_payment', array(
			'label'                     => _x( 'Pending Payment', 'job_listing', 'wp-job-manager-listing-payments' ),
			'public'                    => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => true,
			// translators: %s is label count.
			'label_count'               => _n_noop( 'Pending Payment <span class="count">(%s)</span>', 'Pending Payment <span class="count">(%s)</span>', 'wp-job-manager-listing-payments' ),
		) );
	}

	/**
	 * Ensure the submit form lets us continue to edit/process a job with the pending_payment status.
	 *
	 * @since 2.0.0
	 *
	 * @param array $status Valid job statuses.
	 * @return array
	 */
	public static function add_valid_status( $status ) {
		$status[] = 'pending_payment';
		return $status;
	}

	/**
	 * Filter job status name/label.
	 *
	 * @since 2.0.0
	 *
	 * @param string $status Job listing status.
	 * @param object $job    Job object WP_Post.
	 * @return string
	 */
	public static function set_status_label( $status, $job ) {
		if ( 'pending_payment' === $job->post_status ) {
			$status = __( 'Pending Payment', 'wp-job-manager-listing-payments' );
		}
		return $status;
	}

	/**
	 * Set Job Expiry On Status Change
	 *
	 * @since 2.0.0
	 *
	 * @return void.
	 */
	public static function set_expiry() {
		global $job_manager; // Get obj class.
		add_action( 'pending_payment_to_publish', array( $job_manager->post_types, 'set_expiry' ) );
	}

}
