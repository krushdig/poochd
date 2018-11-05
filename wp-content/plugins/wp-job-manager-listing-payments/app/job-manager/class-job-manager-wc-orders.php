<?php
/**
 * Job Manager WooCommerce Orders.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Job Manager
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * Job Manager WooCommerce Orders.
 *
 * @since 2.0.0
 */
class Job_Manager_WC_Orders {

	/**
	 * Init
	 *
	 * @since 2.0.0
	 */
	public static function init() {

		// Thank you page.
		add_action( 'woocommerce_thankyou', array( __CLASS__, 'woocommerce_thankyou' ), 5 );

		// Displaying user packages on the frontend.
		add_action( 'woocommerce_before_my_account', array( __CLASS__, 'my_packages' ) );

		// Process Order.
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'order_paid' ) );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'order_paid' ) );

		// Display Order Meta in Admin.
		add_filter( 'woocommerce_order_item_display_meta_key', array( __CLASS__, 'order_display_meta_key' ), 10, 3 );
		add_filter( 'woocommerce_order_item_display_meta_value', array( __CLASS__, 'order_display_meta_value' ), 10, 3 );
		add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( __CLASS__, 'order_formatted_meta_data' ), 10, 2 );
	}

	/**
	 * Thank you page.
	 *
	 * @since 2.0.0
	 *
	 * @param int $order_id Order ID.
	 */
	public static function woocommerce_thankyou( $order_id ) {
		global $wp_post_types;

		// Order data.
		$order = wc_get_order( $order_id );
		$order_user = $order->get_user();
		$order_user_id = $order_user->ID;

		// Foreach order items.
		foreach ( $order->get_items() as $item ) {

			// Product data.
			$product_id = $item['product_id'];
			$product_obj = wc_get_product( $product_id );

			// Only if product is job packages.
			if ( $product_obj->is_type( array( 'job_package', 'job_package_subscription' ) ) ) {

				// Payment required flow, no jobs is created yet.
				if ( 'payment_required' === get_option( 'job_manager_paid_listings_flow' ) ) {

					if ( get_option( 'job_manager_job_dashboard_page_id' ) ) {
						echo wp_kses_post( '<a class="button" href="' . get_permalink( get_option( 'job_manager_job_dashboard_page_id' ) ) . '">' . __( 'View Dashboard', 'wp-job-manager-listing-payments' ) . '</a> ' );
					}
					if ( get_option( 'job_manager_submit_job_form_page_id' ) ) {
						echo wp_kses_post( ' <a class="button" href="' . get_permalink( get_option( 'job_manager_submit_job_form_page_id' ) ) . '">' . __( 'Submit Listing', 'wp-job-manager-listing-payments' ) . '</a> ' );
					}
				} elseif ( isset( $item['job_id'] ) ) { // Job Created.

					// Set job owner if not yet set.
					$post = get_post( $item['job_id'] );
					if ( ! $post->post_author && $order_user_id ) {
						$arg = array(
							'ID' => $item['job_id'],
							'post_author' => $order_user_id,
						);
						wp_update_post( $arg );
					}

					// Foreach post status.
					switch ( get_post_status( $item['job_id'] ) ) {
						case 'pending' :
							// Translators: %s will be replaced by listing title.
							echo wp_kses_post( wpautop( sprintf( __( '%s has been submitted successfully and will be visible once approved.', 'wp-job-manager-listing-payments' ), get_the_title( $item['job_id'] ) ) ) );
						break;
						case 'pending_payment' :
						case 'expired' :
							// Translators: %s will be replaced by listing title.
							echo wp_kses_post( wpautop( sprintf( __( '%s has been submitted successfully and will be visible once payment has been confirmed.', 'wp-job-manager-listing-payments' ), get_the_title( $item['job_id'] ) ) ) );
						break;
						default :
							// Translators: %s will be replaced by listing title.
							echo wp_kses_post( wpautop( sprintf( __( '%s has been submitted successfully.', 'wp-job-manager-listing-payments' ), get_the_title( $item['job_id'] ) ) ) );
						break;
					}

					echo '<p class="job-manager-submitted-paid-listing-actions">';

					if ( 'publish' === get_post_status( $item['job_id'] ) ) {
						echo wp_kses_post( '<a class="button" href="' . get_permalink( $item['job_id'] ) . '">' . __( 'View Listing', 'wp-job-manager-listing-payments' ) . '</a> ' );
					} elseif ( get_option( 'job_manager_job_dashboard_page_id' ) ) {
						echo wp_kses_post( '<a class="button" href="' . get_permalink( get_option( 'job_manager_job_dashboard_page_id' ) ) . '">' . __( 'View Dashboard', 'wp-job-manager-listing-payments' ) . '</a> ' );
					}

					echo '</p>';

				} // End if().
			} // End if().
		} // End foreach().
	}

	/**
	 * Show my packages.
	 *
	 * @since 2.0.0
	 */
	public static function my_packages() {
		$packages = astoundify_wpjmlp_get_user_packages( get_current_user_id(), 'job_listing' );
		if ( $packages && is_array( $packages ) && count( $packages ) > 0 ) {

			wc_get_template(
				$template_name = 'my-packages.php',
				$args          = array(
					'packages'  => $packages,
					'type'      => 'job_listing',
				),
				$template_path = 'listing-payments/',
				$default_path  = ASTOUNDIFY_WPJMLP_TEMPLATE_PATH
			);
		}
	}

	/**
	 * Triggered when an order is paid.
	 *
	 * @since 2.0.0
	 *
	 * @param int $order_id Order ID.
	 */
	public static function order_paid( $order_id ) {
		// Get the order.
		$order = wc_get_order( $order_id );
		$order_user = $order->get_user();

		if ( get_post_meta( $order_id, 'wc_paid_listings_packages_processed', true ) ) {
			return;
		}
		foreach ( $order->get_items() as $item ) {

			$product = wc_get_product( $item['product_id'] );

			if ( $product->is_type( array( 'job_package' ) ) && $order_user ) {

				// Give packages to user.
				for ( $i = 0; $i < $item['qty']; $i ++ ) {
					$user_package_id = astoundify_wpjmlp_give_user_package( $order_user->ID, $product->get_id(), $order_id );
				}

				// Approve job with new package.
				if ( isset( $item['job_id'] ) ) {
					$job = get_post( $item['job_id'] );

					if ( in_array( $job->post_status, array( 'pending_payment', 'expired' ), true ) ) {
						astoundify_wpjmlp_approve_job_listing_with_package( $job->ID, $order_user->ID, $user_package_id );
					}
				}
			}
		}

		update_post_meta( $order_id, 'wc_paid_listings_packages_processed', true );
	}

	/**
	 * Order Meta Key Display
	 *
	 * @since 2.1.0
	 *
	 * @param string $display_key   Meta Key Display.
	 * @param object $meta Meta     WC Order Item Meta Object.
	 * @param object $wc_order_item WC Order Item Object.
	 * @return string
	 */
	public static function order_display_meta_key( $display_key, $meta, $wc_order_item ) {
		if ( '_job_id' === $meta->key ) {
			$display_key = __( 'Listing', 'wp-job-manager-listing-payments' );
		}
		return $display_key;
	}

	/**
	 * Order Meta Value Display
	 *
	 * @since 2.1.0
	 *
	 * @param string $display_value Meta Value Display.
	 * @param object $meta Meta     WC Order Item Meta Object.
	 * @param object $wc_order_item WC Order Item Object.
	 * @return string
	 */
	public static function order_display_meta_value( $display_value, $meta, $wc_order_item ) {
		if ( '_job_id' === $meta->key ) {
			$display_value = sprintf( '<a href="%1$s">%2$s</a> (%3$s)', get_edit_post_link( $meta->value ), get_the_title( $meta->value ), $meta->value );
		}
		return $display_value;
	}

	/**
	 * Order Formatted Meta Data.
	 * Remove old unused "Listing" title meta data. "_job_id" is enough.
	 *
	 * @since 2.1.0
	 *
	 * @param array  $formatted_meta Meta Value Display.
	 * @param object $wc_order_item  WC Order Item Object.
	 * @return string
	 */
	public static function order_formatted_meta_data( $formatted_meta, $wc_order_item ) {
		$metas = wp_list_pluck( $formatted_meta, 'key' );
		foreach ( $metas as $k => $v ) {
			if ( in_array( $v, array( 'Listing', __( 'Listing', 'wp-job-manager-listing-payments' ) ) ) ) {
				unset( $formatted_meta[ $k ] );
			}
		}
		return $formatted_meta;
	}

}
