<?php
/**
 * Resume Post Status.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Resume Manager
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * Resume Manager Post Status
 * Most of post status functionality are using Job Manager system.
 *
 * @since 2.0.0
 */
class Resume_Manager_Post_Status {

	/**
	 * Init.
	 *
	 * @since 1.0.0
	 */
	public static function init() {

		// Register Resume Manager Post Status.
		add_filter( 'resume_manager_valid_submit_resume_statuses', array( __CLASS__, 'add_valid_status' ) );
	}

	/**
	 * Ensure the submit form lets us continue to edit/process a job with the pending_payment status.
	 *
	 * @since 2.0.0
	 *
	 * @param array $status Post Status.
	 * @return array
	 */
	public static function add_valid_status( $status ) {
		$status[] = 'pending_payment';
		return $status;
	}

}
