<?php
/**
 * Job Manager WooCommerce Cart.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Job Manager
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * Job Manager WooCommerce Cart.
 *
 * @since 2.0.0
 */
class Job_Manager_WC_Cart {

	/**
	 * Init
	 *
	 * @since 2.0.0
	 */
	public static function init() {

		// Force to use simple add to cart.
		add_action( 'woocommerce_job_package_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );

		// Subscription add to cart.
		add_action( 'woocommerce_job_package_subscription_add_to_cart', 'WC_Subscriptions::subscription_add_to_cart', 30 );

		// Add cart item from session.
		add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'get_cart_item_from_session' ), 10, 2 );

		// Order Item Meta.
		add_action( 'woocommerce_new_order_item', array( __CLASS__, 'order_item_meta' ), 10, 3 );

		// Get Item Data.
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'get_item_data' ), 10, 2 );

		// Force Registration During Checkout.
		add_filter( 'option_woocommerce_enable_signup_and_login_from_checkout', array( __CLASS__, 'enable_signup_and_login_from_checkout' ) );
		add_filter( 'option_woocommerce_enable_guest_checkout', array( __CLASS__, 'enable_guest_checkout' ) );

		// Enable simple billing on $0 checkout.
		add_filter( 'woocommerce_billing_fields',  array( __CLASS__, 'enable_simple_billing_checkout' ) );

	}


	/**
	 * Get the data from the session on page load
	 *
	 * @since 2.0.0
	 *
	 * @param array $cart_item Cart Item.
	 * @param array $values    Cart Values.
	 * @return array
	 */
	public static function get_cart_item_from_session( $cart_item, $values ) {
		if ( ! empty( $values['job_id'] ) ) {
			$cart_item['job_id'] = $values['job_id'];
		}
		return $cart_item;
	}

	/**
	 * Order Item Meta function for storing the meta in the order line items.
	 *
	 * @since 2.1.0
	 *
	 * @param int    $item_id   Item ID.
	 * @param object $item      Values.
	 * @param int    $order_id  Order ID.
	 * @return void
	 */
	public static function order_item_meta( $item_id, $item, $order_id ) {
		if ( isset( $item->legacy_values['job_id'] ) && 'payment_required' !== get_option( 'job_manager_paid_listings_flow' ) ) {
			wc_add_order_item_meta( $item_id, '_job_id', $item->legacy_values['job_id'] );
		}
	}

	/**
	 * Output job name in cart
	 *
	 * @since 2.0.0
	 *
	 * @param  array $data       Cart Data.
	 * @param  array $cart_item  Cart Item.
	 * @return array
	 */
	public static function get_item_data( $data, $cart_item ) {
		if ( isset( $cart_item['job_id'] ) && 'payment_required' !== get_option( 'job_manager_paid_listings_flow' ) ) {
			$job = get_post( absint( $cart_item['job_id'] ) );

			$data[] = array(
				'name'  => __( 'Listing', 'wp-job-manager-listing-payments' ),
				'value' => $job->post_title,
			);
		}
		return $data;
	}

	/**
	 * Enable signup and login form in checkout.
	 *
	 * @since 2.0.0
	 *
	 * @param string $value Value.
	 * @return string
	 */
	public static function enable_signup_and_login_from_checkout( $value ) {
		remove_filter( 'option_woocommerce_enable_guest_checkout', array( __CLASS__, 'enable_guest_checkout' ) );
		$woocommerce_enable_guest_checkout = get_option( 'woocommerce_enable_guest_checkout' );
		add_filter( 'option_woocommerce_enable_guest_checkout', array( __CLASS__, 'enable_guest_checkout' ) );

		if ( 'yes' === $woocommerce_enable_guest_checkout && self::cart_contains_job_package() ) {
			return 'yes';
		}
		return $value;
	}

	/**
	 * Disable guest checkout if contain job package.
	 *
	 * @since 2.0.0
	 *
	 * @param string $value Value.
	 * @return string
	 */
	public static function enable_guest_checkout( $value ) {
		if ( self::cart_contains_job_package() ) {
			return 'no';
		}
		return $value;
	}

	/**
	 * Enable Simple Billing Checkout.
	 *
	 * @since 2.1.0
	 *
	 * @param array $fields Billing Fields.
	 * @return array
	 */
	public static function enable_simple_billing_checkout( $fields ) {
		// Only if enabled in settings.
		$enable = get_option( 'job_manager_paid_listings_simple_checkout', false );
		if ( ! $enable ) {
			return $fields;
		}

		// Only if cart contain job packages.
		if ( ! self::cart_contains_job_package() ) {
			return $fields;
		}

		// Only if cart is $0.
		global $woocommerce;
		if ( 0 !== intval( $woocommerce->cart->total ) ) {
			return $fields;
		}

		// Remove fields except name, phone and email.
		unset( $fields['billing_country'] );
		unset( $fields['billing_company'] );
		unset( $fields['billing_address_1'] );
		unset( $fields['billing_address_2'] );
		unset( $fields['billing_city'] );
		unset( $fields['billing_state'] );
		unset( $fields['billing_postcode'] );

		return $fields;
	}

	/**
	 * Utility Functions to checks an cart to see if it contains a job_package.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public static function cart_contains_job_package() {
		global $woocommerce;

		if ( ! empty( $woocommerce->cart->cart_contents ) ) {
			foreach ( $woocommerce->cart->cart_contents as $cart_item ) {
				$product = $cart_item['data'];
				if ( $product->is_type( 'job_package' ) && ! $product->is_type( 'job_package_subscription' ) ) {
					return true;
				}
			}
		}
		return false;
	}

}
