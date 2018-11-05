<?php
/**
 * User Functions.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Core
 * @author Astoundify
 */

/**
 * Get a users packages from the DB.
 *
 * @since 2.0.0
 *
 * @param  int          $user_id       User Id.
 * @param  string|array $package_type  Package Type.
 * @param  bool         $package_limit True to query all packages including inactive/fully used packages. Default to False.
 * @return array Of objects.
 */
function astoundify_wpjmlp_get_user_packages( $user_id, $package_type = '', $package_limit = false ) {
	global $wpdb;

	if ( ! $package_type ) { // No package type set, load all.
		$package_type = array( 'resume', 'job_listing' );
	} elseif( ! is_array( $package_type ) ) { // Format it as array if it's a string.
		$package_type = array( $package_type );
	}

	if ( $package_limit ) {
		$packages = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wcpl_user_packages WHERE user_id = %d AND package_type IN ( '" . implode( "','", $package_type ) . "' );", $user_id ), OBJECT_K );
	} else {
		$packages = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wcpl_user_packages WHERE user_id = %d AND package_type IN ( '" . implode( "','", $package_type ) . "' ) AND ( package_count < package_limit OR package_limit = 0 );", $user_id ), OBJECT_K );
	}

	return $packages;
}

/**
 * Get a package.
 *
 * @since 2.0.0
 *
 * @param  int $package_id Package ID.
 * @return object
 */
function astoundify_wpjmlp_get_user_package( $package_id ) {
	global $wpdb;

	$package = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wcpl_user_packages WHERE id = %d;", $package_id ) );
	return astoundify_wpjmlp_get_package( $package );
}

/**
 * Give a user a package.
 *
 * @since 2.0.0
 *
 * @param  int $user_id    User ID.
 * @param  int $product_id Product ID.
 * @param  int $order_id   Order ID.
 * @return int|false
 */
function astoundify_wpjmlp_give_user_package( $user_id, $product_id, $order_id = 0 ) {
	global $wpdb;

	$package = wc_get_product( $product_id );

	if ( ! $package->is_type( 'job_package' ) && ! $package->is_type( 'resume_package' ) && ! $package->is_type( 'job_package_subscription' ) && ! $package->is_type( 'resume_package_subscription' ) ) {
		return false;
	}

	$wpdb->insert(
		"{$wpdb->prefix}wcpl_user_packages",
		array(
			'user_id'          => $user_id,
			'product_id'       => $product_id,
			'order_id'         => $order_id,
			'package_count'    => 0,
			'package_duration' => $package->get_duration(),
			'package_limit'    => $package->get_limit(),
			'package_featured' => $package->is_listing_featured() ? 1 : 0,
			'package_type'     => $package->is_type( array( 'resume_package', 'resume_package_subscription' ) ) ? 'resume' : 'job_listing',
		)
	);

	return $wpdb->insert_id;
}


/**
 * Get listing IDs for a user package.
 *
 * @since 2.0.0
 *
 * @param int $user_package_id User Package ID.
 * @return array
 */
function astoundify_wpjmlp_get_listings_for_package( $user_package_id ) {
	global $wpdb;

	return $wpdb->get_col( $wpdb->prepare( "
		SELECT post_id FROM {$wpdb->postmeta}
		LEFT JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID
		WHERE meta_key = '_user_package_id'
		AND meta_value = %s;
	", $user_package_id ) );
}
