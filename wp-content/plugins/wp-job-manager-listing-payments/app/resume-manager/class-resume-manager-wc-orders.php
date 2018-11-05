<?php
/**
 * Resume Manager WooCommerce Orders.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Resume Manager
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * Resume Manager WooCommerce Orders.
 *
 * @since 2.0.0
 */
class Resume_Manager_WC_Orders {

	/**
	 * Init.
	 *
	 * @since 2.0.0
	 */
	public static function init() {

		// Thank you page.
		add_action( 'woocommerce_thankyou', array( __CLASS__, 'woocommerce_thankyou' ), 5 );

		// Displaying user packages on the frontend.
		add_action( 'woocommerce_before_my_account', array( __CLASS__, 'my_packages' ) );

		// Process order.
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'order_paid' ) );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'order_paid' ) );
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

		$order = wc_get_order( $order_id );
		$order_status = $order->get_status();

		foreach ( $order->get_items() as $item ) {
			$product_id = $item['product_id'];
			$product_obj = wc_get_product( $product_id );
			if ( $product_obj->is_type( array( 'resume_package', 'resume_package_subscription' ) ) ) {

				if ( 'payment_required' === get_option( 'resume_manager_paid_listings_flow' ) ) {

					if ( get_option( 'resume_manager_candidate_dashboard_page_id' ) ) {
						echo '<a class="button" href="' . esc_url( get_permalink( get_option( 'resume_manager_candidate_dashboard_page_id' ) ) ) . '">' . esc_html_e( 'View Dashboard', 'wp-job-manager-listing-payments' ) . '</a> ';
					}
					if ( get_option( 'resume_manager_submit_resume_form_page_id' ) && in_array( $order_status, array( 'processing', 'completed' ), true ) ) {
						echo ' <a class="button" href="' . esc_url( get_permalink( get_option( 'resume_manager_submit_resume_form_page_id' ) ) ) . '">' . esc_html_e( 'Submit Your Resume', 'wp-job-manager-listing-payments' ) . '</a> ';
					}
				}
			} elseif ( isset( $item['resume_id'] ) ) {
				$resume = get_post( $item['resume_id'] );

				switch ( get_post_status( $item['resume_id'] ) ) {
					case 'pending' :
						// Translators: %s is Resume title.
						echo wp_kses_post( wpautop( sprintf( esc_html_e( '%s has been submitted successfully and will be visible once approved.', 'wp-job-manager-listing-payments' ), get_the_title( $item['resume_id'] ) ) ) );
					break;
					case 'pending_payment' :
					case 'expired' :
						// Translators: %s is Resume title.
						echo wp_kses_post( wpautop( sprintf( esc_html_e( '%s has been submitted successfully and will be visible once payment has been confirmed.', 'wp-job-manager-listing-payments' ), get_the_title( $item['resume_id'] ) ) ) );
					break;
					default :
						// Translators: %s is Resume title.
						echo wp_kses_post( wpautop( sprintf( esc_html_e( '%s has been submitted successfully.', 'wp-job-manager-listing-payments' ), get_the_title( $item['resume_id'] ) ) ) );
					break;
				}

				echo '<p class="job-manager-submitted-paid-listing-actions">';

				if ( 'publish' === get_post_status( $item['resume_id'] ) ) {
					echo '<a class="button" href="' . esc_url( get_permalink( $item['resume_id'] ) ) . '">' . esc_html_e( 'View Listing', 'wp-job-manager-listing-payments' ) . '</a> ';
				} elseif ( get_option( 'resume_manager_candidate_dashboard_page_id' ) ) {
					echo '<a class="button" href="' . esc_url( get_permalink( get_option( 'resume_manager_candidate_dashboard_page_id' ) ) ) . '">' . esc_html_e( 'View Dashboard', 'wp-job-manager-listing-payments' ) . '</a> ';
				}

				if ( ! empty( $resume->_applying_for_job_id ) ) {
					// Translators: %s is Job title.
					echo '<a class="button" href="' . esc_url( get_permalink( absint( $resume->_applying_for_job_id ) ) ) . '">' . sprintf( esc_html_e( 'Apply for "%s"', 'wp-job-manager-listing-payments' ), get_the_title( absint( $resume->_applying_for_job_id ) ) ) . '</a> ';
				}

				echo '</p>';
			} // End if().
		} // End foreach().
	}

	/**
	 * Show my packages.
	 *
	 * @since 2.0.0
	 */
	public static function my_packages() {
		$packages = astoundify_wpjmlp_get_user_packages( get_current_user_id(), 'resume' );
		if ( $packages && is_array( $packages ) && count( $packages ) > 0 ) {

			wc_get_template(
				$template_name = 'my-packages.php',
				$args          = array(
					'packages'  => $packages,
					'type'      => 'resume',
				),
				$template_path = 'listing-payments/',
				$default_path  = ASTOUNDIFY_WPJMLP_TEMPLATE_PATH
			);
		}
	}

	/**
	 * Triggered when an order is paid
	 *
	 * @since 2.0.0
	 *
	 * @param  int $order_id Order ID.
	 */
	public static function order_paid( $order_id ) {
		// Get the order.
		$order = wc_get_order( $order_id );
		$order_user = $order->get_user();

		if ( get_post_meta( $order_id, 'wc_paid_listings_resume_packages_processed', true ) ) {
			return;
		}
		foreach ( $order->get_items() as $item ) {
			$product = wc_get_product( $item['product_id'] );

			if ( $product->is_type( array( 'resume_package' ) ) && $order_user ) {

				// Give packages to user.
				for ( $i = 0; $i < $item['qty']; $i ++ ) {
					$user_package_id = astoundify_wpjmlp_give_user_package( $order_user->ID, $product->get_id(), $order_id );
				}

				// Approve resume with new package.
				if ( isset( $item['resume_id'] ) ) {
					$resume = get_post( $item['resume_id'] );

					if ( in_array( $resume->post_status, array( 'pending_payment', 'expired' ), true ) ) {
						astoundify_wpjmlp_approve_resume_with_package( $resume->ID, $order_user->ID, $user_package_id );
					}
				}
			}
		}

		update_post_meta( $order_id, 'wc_paid_listings_resume_packages_processed', true );
	}

}
