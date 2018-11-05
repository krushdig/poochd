<?php
/**
 * Utility Functions.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Core
 * @author Astoundify
 */

/**
 * Calculates and returns expiry date from a duration.
 *
 * @since 2.0.0
 *
 * @param  int $duration Duration in day.
 * @return string
 */
function astoundify_wpjmlp_get_expiry_date( $duration ) {
	return date( get_option('date_format'), strtotime( "+{$duration} days", current_time( 'timestamp' ) ) );
}
