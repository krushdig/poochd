<?php
/**
 * Resume Package Product Type.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Resume Manager
 * @author Astoundify
 */

/**
 * Register Resume Package Product Type
 *
 * This class cannot use namespace. WC will load this class based on package name.
 *
 * @since 2.0.0
 */
class WC_Product_Resume_Package extends WC_Product {

	/**
	 * Stores product data.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	protected $extra_data = array(
		'resume_duration'       => '',
		'resume_limit'          => '',
		'resume_featured'       => '',
		'resume_purchase_limit' => '',
	);

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 *
	 * @param object $product Product.
	 */
	public function __construct( $product ) {
		$this->product_type = 'resume_package';
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
		return 'resume_package';
	}

	/**
	 * We want to sell jobs one at a time
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	public function is_sold_individually() {
		return apply_filters( 'wcpl_' . $this->get_type() . '_is_sold_individually', true );
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
		return apply_filters( 'woocommerce_product_add_to_cart_url', $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id ), $this );
	}

	/**
	 * Get the add to cart button text
	 *
	 * @since 2.0.0
	 *
	 * @access public
	 * @return string
	 */
	public function add_to_cart_text() {
		$text = $this->is_purchasable() && $this->is_in_stock() ? __( 'Add to cart', 'wp-job-manager-listing-payments' ) : __( 'Read More', 'wp-job-manager-listing-payments' );

		return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
	}

	/**
	 * Resume Packages can always be purchased regardless of price.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	public function is_purchasable() {
		return apply_filters( 'woocommerce_is_purchasable', true, $this );
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
	 * Return listing duration granted
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_duration() {
		return $this->get_resume_duration();
	}

	/**
	 * Get the listing duration.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_resume_duration() {
		if ( $this->get_prop( 'resume_duration' ) ) {
			return $this->get_prop( 'resume_duration' );
		} else {
			return get_option( 'resume_manager_submission_duration' );
		}
	}

	/**
	 * Set the listing duration.
	 *
	 * @since 2.0.0
	 *
	 * @param int $duration Duration.
	 */
	public function set_resume_duration( $duration ) {
		$this->set_prop( 'resume_duration', $duration );
	}

	/**
	 * Return resume limit
	 *
	 * @since 2.0.0
	 *
	 * @return int 0 if unlimited
	 */
	public function get_limit() {
		return $this->get_resume_limit();
	}

	/**
	 * Get the resume listing limit.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_resume_limit() {
		if ( $this->get_prop( 'resume_limit' ) ) {
			return $this->get_prop( 'resume_limit' );
		} else {
			return 0;
		}
	}

	/**
	 * Set the resume listing limit.
	 *
	 * @since 2.0.0
	 *
	 * @param int $limit Limit.
	 */
	public function set_resume_limit( $limit ) {
		$this->set_prop( 'resume_limit', $limit );
	}

	/**
	 * Return if featured
	 *
	 * @since 2.0.0
	 *
	 * @return int 0 if unlimited
	 */
	public function is_listing_featured() {
		return 'yes' === $this->get_resume_featured();
	}

	/**
	 * Get resume featured status.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function get_resume_featured() {
		return $this->get_prop( 'resume_featured' );
	}

	/**
	 * Set resume featured status.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $featured Featured.
	 */
	public function set_resume_featured( $featured ) {
		$this->set_prop( 'resume_featured', $featured );
	}

	/**
	 * Return purchase limit
	 *
	 * @since 2.1.0
	 *
	 * @return int 0 if unlimited
	 */
	public function get_purchase_limit() {
		return $this->get_resume_purchase_limit();
	}

	/**
	 * Get purchase limit.
	 *
	 * @since 2.1.0
	 *
	 * @return int
	 */
	public function get_resume_purchase_limit() {
		if ( $this->get_prop( 'resume_purchase_limit' ) ) {
			return $this->get_prop( 'resume_purchase_limit' );
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
	public function set_resume_purchase_limit( $limit ) {
		$this->set_prop( 'resume_purchase_limit', $limit );
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
