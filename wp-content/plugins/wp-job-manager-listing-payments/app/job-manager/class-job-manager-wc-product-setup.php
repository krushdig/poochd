<?php
/**
 * Job Manager WooCommerce Product Setup.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Job Manager
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * Job Manager WC Product Type Setup
 *
 * @since 2.0.0
 */
class Job_Manager_WC_Product_Setup {

	/**
	 * Init
	 *
	 * @since 2.0.0
	 */
	public static function init() {

		// Add Product Type.
		add_filter( 'product_type_selector', array( __CLASS__, 'add_product_type_selector' ) );

		// Process Package.
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_product_meta' ) );

		// Product data options.
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'product_data_options' ) );
	}

	/**
	 * Add the product type selector
	 *
	 * @since 2.0.0
	 *
	 * @param array $types Product types.
	 */
	public static function add_product_type_selector( $types ) {
		$types['job_package'] = __( 'Listing Payment Package', 'wp-job-manager-listing-payments' );
		return $types;
	}

	/**
	 * Save Job Package data for the product
	 *
	 * @since 2.0.0
	 *
	 * @param object $product WC Product Object.
	 * @return void
	 */
	public static function save_product_meta( $product ) {
		// Only for job package type.
		if ( 'job_package' !== $product->get_type() ) {
			return;
		}

		// Set Duration.
		if ( isset( $_POST['_job_listing_duration'] ) ) {
			$product->set_job_listing_duration( absint( $_POST['_job_listing_duration'] ) ? absint( $_POST['_job_listing_duration'] ) : '' );
		}

		// Set Listing Limit.
		if ( isset( $_POST['_job_listing_limit'] ) ) {
			$product->set_job_listing_limit( absint( $_POST['_job_listing_limit'] ) ? absint( $_POST['_job_listing_limit'] ) : '' );
		}

		// Set Purchase Limit.
		if ( isset( $_POST['_job_listing_purchase_limit'] ) ) {
			$product->set_job_listing_purchase_limit( absint( $_POST['_job_listing_purchase_limit'] ) ? absint( $_POST['_job_listing_purchase_limit'] ) : '' );
		}

		// Featured.
		if ( isset( $_POST['_job_listing_featured'] ) && 'yes' === $_POST['_job_listing_featured'] ) {
			$product->set_job_listing_featured( 'yes' );
		} else {
			$product->set_job_listing_featured( 'no' );
		}
	}

	/**
	 * Show the job package product options.
	 *
	 * @since 2.0.0
	 */
	public static function product_data_options() {
		$product = wc_get_product();
		?>
		<div class="options_group show_if_job_package">

			<?php woocommerce_wp_text_input( array(
				'id'                => '_job_listing_limit',
				'label'             => __( 'Listing limit', 'wp-job-manager-listing-payments' ),
				'description'       => __( 'The number of listings a user can post with this package.', 'wp-job-manager-listing-payments' ),
				'value'             => method_exists( $product, 'get_limit' ) ? $product->get_limit() : '',
				'placeholder'       => __( 'Unlimited', 'wp-job-manager-listing-payments' ),
				'type'              => 'number',
				'desc_tip'          => true,
				'custom_attributes' => array(
					'min'    => '',
					'step'   => '1',
				),
			) ); ?>

			<?php woocommerce_wp_text_input( array(
				'id'             => '_job_listing_duration',
				'label'          => __( 'Listing duration', 'wp-job-manager-listing-payments' ),
				'description'    => __( 'The number of days that the listing will be active.', 'wp-job-manager-listing-payments' ),
				'value'          => method_exists( $product, 'get_duration' ) ? $product->get_duration() : '',
				'placeholder'    => get_option( 'job_manager_submission_duration' ),
				'desc_tip'       => true,
				'type'           => 'number',
				'custom_attributes' => array(
					'min'    => '',
					'step'   => '1',
				),
			) ); ?>

			<?php woocommerce_wp_text_input( array(
				'id'             => '_job_listing_purchase_limit',
				'label'          => __( 'Purchase Limit', 'wp-job-manager-listing-payments' ),
				'description'    => __( 'Number of times a user can purchase this package. Zero for no limit.', 'wp-job-manager-listing-payments' ),
				'value'          => method_exists( $product, 'get_purchase_limit' ) ? $product->get_purchase_limit() : '',
				'placeholder'    => '',
				'desc_tip'       => true,
				'type'           => 'number',
				'custom_attributes' => array(
					'min'    => '',
					'step'   => '1',
				),
			) ); ?>

			<?php woocommerce_wp_checkbox( array(
				'id'             => '_job_listing_featured',
				'label'          => __( 'Feature Listings?', 'wp-job-manager-listing-payments' ),
				'description'    => __( 'Feature this listing - it will be styled differently and sticky.', 'wp-job-manager-listing-payments' ),
				'value'          => method_exists( $product, 'is_listing_featured' ) ? ( $product->is_listing_featured() ? 'yes' : 'no' ) : '',
			) ); ?>

			<script type="text/javascript">
				jQuery( function($) {
					$( '.pricing' ).addClass( 'show_if_job_package' );
					$( '._tax_status_field' ).closest( 'div' ).addClass( 'show_if_job_package' );
					$( '#product-type' ).change();
				} );
			</script>

		</div>
		<?php
	}

}
