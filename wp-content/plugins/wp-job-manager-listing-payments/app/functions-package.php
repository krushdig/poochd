<?php
/**
 * Package Functions.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Core
 * @author Astoundify
 */

/**
 * Get a package.
 *
 * @since 2.0.0
 *
 * @param  object $package Package.
 * @return WC_Paid_Listings_Package
 */
function astoundify_wpjmlp_get_package( $package ) {
	return new \Astoundify\WPJobManager\ListingPayments\Package( $package );
}

/**
 * Approve a listing.
 *
 * @since 2.0.0
 *
 * @param  int $listing_id      Listing ID.
 * @param  int $user_id         User ID.
 * @param  int $user_package_id User Package ID.
 */
function astoundify_wpjmlp_approve_listing_with_package( $listing_id, $user_id, $user_package_id ) {
	if ( astoundify_wpjmlp_package_is_valid( $user_id, $user_package_id ) ) {
		$listing = array(
			'ID'            => $listing_id,
			'post_date'     => current_time( 'mysql' ),
			'post_date_gmt' => current_time( 'mysql', 1 ),
		);

		switch ( get_post_type( $listing_id ) ) {
			case 'job_listing' :
				delete_post_meta( $listing_id, '_job_expires' );
				$listing['post_status'] = get_option( 'job_manager_submission_requires_approval' ) ? 'pending' : 'publish';
			break;
			case 'resume' :
				$listing['post_status'] = get_option( 'resume_manager_submission_requires_approval' ) ? 'pending' : 'publish';
			break;
		}

		// Do update.
		wp_update_post( $listing );
		update_post_meta( $listing_id, '_user_package_id', $user_package_id );
		astoundify_wpjmlp_increase_package_count( $user_id, $user_package_id );
	}
}

/**
 * Approve a job listing.
 *
 * @since 2.0.0
 *
 * @param  int $job_id          Job ID.
 * @param  int $user_id         User ID.
 * @param  int $user_package_id User Package ID.
 */
function astoundify_wpjmlp_approve_job_listing_with_package( $job_id, $user_id, $user_package_id ) {
	astoundify_wpjmlp_approve_listing_with_package( $job_id, $user_id, $user_package_id );
}

/**
 * Approve a resume.
 *
 * @since 2.0.0
 *
 * @param  int $resume_id        Job ID.
 * @param  int $user_id          User ID.
 * @param  int $user_package_id  User Package ID.
 */
function astoundify_wpjmlp_approve_resume_with_package( $resume_id, $user_id, $user_package_id ) {
	astoundify_wpjmlp_approve_listing_with_package( $resume_id, $user_id, $user_package_id );
}

/**
 * See if a package is valid for use.
 *
 * @since 2.0.0
 *
 * @param int $user_id    User ID.
 * @param int $package_id Package ID.
 * @return bool
 */
function astoundify_wpjmlp_package_is_valid( $user_id, $package_id ) {
	global $wpdb;

	$package = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wcpl_user_packages WHERE user_id = %d AND id = %d;", $user_id, $package_id ) );

	if ( ! $package ) {
		return false;
	}

	if ( $package->package_count >= $package->package_limit && 0 !== intval( $package->package_limit ) ) {
		return false;
	}

	return true;
}

/**
 * Increase job count for package.
 *
 * @since 2.0.0
 *
 * @param  int $user_id    User ID.
 * @param  int $package_id Package ID.
 * @return int affected rows
 */
function astoundify_wpjmlp_increase_package_count( $user_id, $package_id ) {
	global $wpdb;

	$packages = astoundify_wpjmlp_get_user_packages( $user_id );

	if ( isset( $packages[ $package_id ] ) ) {
		$new_count = $packages[ $package_id ]->package_count + 1;
	} else {
		$new_count = 1;
	}

	return $wpdb->update(
		"{$wpdb->prefix}wcpl_user_packages",
		array(
			'package_count' => $new_count,
		),
		array(
			'user_id' => $user_id,
			'id'      => $package_id,
		),
		array( '%d' ),
		array( '%d', '%d' )
	);
}

/**
 * Decrease job count for package.
 *
 * @since 2.0.0
 *
 * @param  int $user_id    User ID.
 * @param  int $package_id Package ID.
 * @return int affected rows
 */
function astoundify_wpjmlp_decrease_package_count( $user_id, $package_id ) {
	global $wpdb;

	$packages = astoundify_wpjmlp_get_user_packages( $user_id );

	if ( isset( $packages[ $package_id ] ) ) {
		$new_count = $packages[ $package_id ]->package_count - 1;
	} else {
		$new_count = 0;
	}

	return $wpdb->update(
		"{$wpdb->prefix}wcpl_user_packages",
		array(
			'package_count' => max( 0, $new_count ),
		),
		array(
			'user_id' => $user_id,
			'id'      => $package_id,
		),
		array( '%d' ),
		array( '%d', '%d' )
	);
}


/**
 * Get Job Packages
 * Return array of product ID.
 *
 * @since 2.0.0
 *
 * @param array $post__in Post In Args.
 * @param int   $user_id  User ID to check purchase limit. False to not check. Null to use current user.
 * @return object WP_Query.
 */
function astoundify_wpjmlp_get_job_packages( $post__in = array(), $user_id = null ) {
	$args = array(
		'status'  => array( 'publish' ),
		'limit'   => -1,
		'order'   => 'asc',
		'orderby' => 'menu_order',
		'type'    => array( 'job_package', 'job_package_subscription' ),
	);

	if ( ! empty( $post__in ) ) {
		$args['include'] = $post__in;
	}

	$packages = wc_get_products( apply_filters( 'astoundify_wpjmlp_get_job_packages_wc_products_args', $args ) );

	// Purchase limit.
	if ( is_user_logged_in() && $packages && false !== $user_id ) {

		$user_id = $user_id ? $user_id : get_current_user_id();

		// Get User Packages (including inactive package/fully used package).
		$user_packages = astoundify_wpjmlp_get_user_packages( $user_id, 'job_listing', true );

		if ( $user_packages && is_array( $user_packages ) ) {

			// Simplify package.
			$user_packages = wp_list_pluck( $user_packages, 'product_id' );

			// Loop each packages to check purchase limit.
			foreach( $packages as $key => $package ) {

				$package = wc_get_product( $package );
				$purchase_limit = 0;
				if ( method_exists( $package, 'get_purchase_limit' ) ) {
					$purchase_limit = absint( $package->get_purchase_limit() );
				}

				if ( $purchase_limit ) {

					// Get user packages for current products.
					$purchases = array();
					foreach ( $user_packages as $package_id => $product_id ) {
						if ( intval( $product_id ) === intval( $package->get_product_id() ) ) {
							$purchases[ $package_id ] = $product_id;
						}
					}

					// Check limit with user packages and remove product if exceeded.
					if ( count( $purchases ) >= $purchase_limit ) {
						unset( $packages[ $key ] );
					}
				}
			}
		}
	}

	return apply_filters( 'astoundify_wpjmlp_job_packages', $packages, $args, $user_id );
}


/**
 * Get Resume Packages
 * Return array of product ID
 *
 * @since 2.0.0
 *
 * @param array $post__in Post In Args.
 * @param int   $user_id  User ID to check purchase limit. False to not check. Null to use current user.
 * @return object WP_Query.
 */
function astoundify_wpjmlp_get_resume_packages( $post__in = array(), $user_id = null ) {
	$args = array(
		'status'  => array( 'publish' ),
		'limit'   => -1,
		'order'   => 'asc',
		'orderby' => 'menu_order',
		'type'    => array( 'resume_package', 'resume_package_subscription' ),
	);

	if ( ! empty( $post__in ) ) {
		$args['include'] = $post__in;
	}

	$args = apply_filters( 'astoundify_wpjmlp_get_resume_packages_wc_products_args', $args );
	$packages = wc_get_products( $args );

	// Purchase limit.
	if ( is_user_logged_in() && $packages && false !== $user_id ) {

		$user_id = $user_id ? $user_id : get_current_user_id();

		// Get User Packages (including inactive package/fully used package).
		$user_packages = astoundify_wpjmlp_get_user_packages( $user_id, 'resume', true );

		if ( $user_packages && is_array( $user_packages ) ) {

			// Simplify package.
			$user_packages = wp_list_pluck( $user_packages, 'product_id' );

			// Loop each packages to check purchase limit.
			foreach( $packages as $key => $package ) {

				$package = wc_get_product( $package );
				$purchase_limit = 0;
				if ( method_exists( $package, 'get_purchase_limit' ) ) {
					$purchase_limit = absint( $package->get_purchase_limit() );
				}

				if ( $purchase_limit ) {

					// Get user packages for current products.
					$purchases = array();
					foreach ( $user_packages as $package_id => $product_id ) {
						if ( intval( $product_id ) === intval( $package->get_product_id() ) ) {
							$purchases[ $package_id ] = $product_id;
						}
					}

					// Check limit with user packages and remove product if exceeded.
					if ( count( $purchases ) >= $purchase_limit ) {
						unset( $packages[ $key ] );
					}
				}
			}
		}
	}

	return apply_filters( 'astoundify_wpjmlp_resume_packages', $packages, $args, $user_id );
}

