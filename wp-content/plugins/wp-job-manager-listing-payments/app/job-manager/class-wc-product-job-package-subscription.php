<?php
/**
 * WooCommerce Subscription Product: Job Package Subscription
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Job Manager
 * @author Astoundify
 */

/**
 * Register WC Subscription Product: Job Package Subscription
 *
 * This class cannot use namespace. WC will load this class based on package name.
 *
 * @since 2.0.0
 */
class WC_Product_Job_Package_Subscription extends WC_Product_Subscription {

	/**
	 * Stores product data.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	protected $extra_data = array(
		'package_subscription_type'   => '',
		'job_listing_duration'        => '',
		'job_listing_limit'           => '',
		'job_listing_featured'        => '',
		'job_listing_purchase_limit'  => '',
	);

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 *
	 * @param object $product Product.
	 */
	public function __construct( $product ) {
		parent::__construct( $product );
	}


	/**
	 * Get internal type.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_type() {
		return 'job_package_subscription';
	}

	/**
	 * Checks the product type.
	 * Backwards compat with downloadable/virtual.
	 *
	 * @since 2.0.0
	 *
	 * @access public
	 * @param mixed $type Array or string of types.
	 * @return bool
	 */
	public function is_type( $type ) {
		return ( 'job_package_subscription' === $type || ( is_array( $type ) && in_array( 'job_package_subscription', $type, true ) ) ) ? true : parent::is_type( $type );
	}

	/**
	 * We want to sell jobs one at a time.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	public function is_sold_individually() {
		return true;
	}

	/**
	 * Get the add to url used mainly in loops.
	 *
	 * @since 2.0.0
	 *
	 * @access public
	 * @return string
	 */
	public function add_to_cart_url() {
		$url = $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id );

		return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
	}

	/**
	 * Jobs are always virtual
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	public function is_virtual() {
		return true;
	}

	/**
	 * Get job listing package subscription type
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_job_listing_package_subscription_type() {
		return $this->get_package_subscription_type();
	}

	/**
	 * Get package subscription type
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_package_subscription_type() {
		return 'package' === $this->get_prop( 'package_subscription_type' ) ? 'package' : 'listing';
	}

	/**
	 * Set listing duration.
	 *
	 * @since 2.0.0
	 *
	 * @param string $package_subscription_type Package Subscription Type.
	 */
	public function set_package_subscription_type( $package_subscription_type ) {
		$this->set_prop( 'package_subscription_type', $package_subscription_type );
	}

	/**
	 * Return resume duration granted
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_job_listing_duration() {
		return $this->get_prop( 'job_listing_duration' );
	}

	/**
	 * Return job listing duration granted
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_duration() {
		if ( 'listing' === $this->get_package_subscription_type() ) {
			return false;
		} elseif ( $this->get_job_listing_duration() ) {
			return $this->get_job_listing_duration();
		} else {
			return get_option( 'job_manager_submission_duration' );
		}
	}

	/**
	 * Set listing duration.
	 *
	 * @since 2.0.0
	 *
	 * @param int $duration Listing Duration.
	 */
	public function set_job_listing_duration( $duration ) {
		$this->set_prop( 'job_listing_duration', $duration );
	}

	/**
	 * Return job listing limit
	 *
	 * @since 2.0.0
	 *
	 * @return int 0 if unlimited.
	 */
	public function get_limit() {
		return $this->get_job_listing_limit();
	}

	/**
	 * Get listing limit.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_job_listing_limit() {
		if ( $this->get_prop( 'job_listing_limit' ) ) {
			return $this->get_prop( 'job_listing_limit' );
		} else {
			return 0;
		}
	}

	/**
	 * Set listing limit
	 *
	 * @since 2.0.0
	 *
	 * @param int $limit Limit.
	 */
	public function set_job_listing_limit( $limit ) {
		$this->set_prop( 'job_listing_limit', $limit );
	}

	/**
	 * Return if featured
	 *
	 * @since 2.0.0
	 */
	public function is_listing_featured() {
		return 'yes' === $this->get_job_listing_featured();
	}

	/**
	 * Get featured status.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function get_job_listing_featured() {
		return $this->get_prop( 'job_listing_featured' );
	}

	/**
	 * Set featured status.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $featured Featured.
	 */
	public function set_job_listing_featured( $featured ) {
		$this->set_prop( 'job_listing_featured', $featured );
	}

	/**
	 * Return purchase limit
	 *
	 * @since 2.1.0
	 *
	 * @return int 0 if unlimited
	 */
	public function get_purchase_limit() {
		return $this->get_job_listing_purchase_limit();
	}

	/**
	 * Get purchase limit.
	 *
	 * @since 2.1.0
	 *
	 * @return int
	 */
	public function get_job_listing_purchase_limit() {
		if ( $this->get_prop( 'job_listing_purchase_limit' ) ) {
			return $this->get_prop( 'job_listing_purchase_limit' );
		} else {
			return 0;
		}
	}

	/**
	 * Set purchase limit
	 *
	 * @since 2.1.0
	 *
	 * @param int $limit Purchase Limit.
	 */
	public function set_job_listing_purchase_limit( $limit ) {
		$this->set_prop( 'job_listing_purchase_limit', $limit );
	}

	/**
	 * Get product id
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_product_id() {
		return $this->id;
	}
}
