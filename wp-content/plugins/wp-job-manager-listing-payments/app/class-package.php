<?php
/**
 * Package.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Core
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * Package Class.
 *
 * @since 2.0.0
 */
class Package {

	/**
	 * Package
	 *
	 * @since 2.0.0
	 *
	 * @var object
	 */
	private $package;

	/**
	 * Product
	 *
	 * @since 2.0.0
	 *
	 * @var object
	 */
	private $product;

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 *
	 * @param object $package Current package.
	 */
	public function __construct( $package ) {
		$this->package = $package;
	}

	/**
	 * Get package ID
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->package->id;
	}

	/**
	 * Get product post
	 *
	 * @since 2.0.0
	 *
	 * @return WP_Post
	 */
	public function get_product() {
		if ( empty( $this->product ) && $this->get_product_id() ) {
			$this->product = get_post( $this->get_product_id() );
		}
		return $this->product;
	}

	/**
	 * Get product id
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_product_id() {
		return $this->package->product_id;
	}

	/**
	 * Get title for package
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_title() {
		$product = $this->get_product();
		return $product ? $product->post_title : '#' . $this->get_id();
	}

	/**
	 * Is this package for features jobs/resumes?
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	public function is_listing_featured() {
		return 1 === absint( $this->package->package_featured );
	}

	/**
	 * Get limit.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_limit() {
		return $this->package->package_limit;
	}

	/**
	 * Get count.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_count() {
		return $this->package->package_count;
	}

	/**
	 * Get duration.
	 *
	 * @since 2.0.0
	 *
	 * @return int|bool
	 */
	public function get_duration() {
		if ( 'job_listing' === $this->get_type() ) {
			$default = get_option( 'job_manager_submission_duration', 30 );
		} else {
			$default = get_option( 'resume_manager_submission_duration', '' );
		}
		return $this->package->package_duration ? $this->package->package_duration : $default;
	}

	/**
	 * Get order id
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_order_id() {
		return $this->package->order_id;
	}

	/**
	 * Get Type
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->package->package_type;
	}

	/**
	 * Get the number of listings remaining.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function get_remaining_count() {
		return absint( $this->get_limit() - $this->get_count() );
	}

	/**
	 * Check if a package can still be used.
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	public function is_limit_reached() {
		if ( ! $this->get_limit() ) {
			return false; // Unlimited package.
		}
		return absint( $this->get_remaining_count() ) ? false : true;
	}
}
