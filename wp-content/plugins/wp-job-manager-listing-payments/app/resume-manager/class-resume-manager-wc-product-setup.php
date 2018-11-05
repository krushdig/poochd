<?php
/**
 * Resume Manager WooCommerce Product Setup.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Resume Manager
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * Resume Manager WC Product Type Setup
 *
 * @since 1.0.0
 */
class Resume_Manager_WC_Product_Setup {

	/**
	 * Init.
	 *
	 * @since 2.0.0
	 */
	public static function init() {

		// Add Product Type.
		add_filter( 'product_type_selector', array( __CLASS__, 'add_product_type_selector' ) );

		// Process package.
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_product_meta' ) );

		// Product data options.
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'product_data_options' ) );

	}

	/**
	 * Add the product type.
	 *
	 * @since 2.0.0
	 *
	 * @param array $types Types.
	 * @return array
	 */
	public static function add_product_type_selector( $types ) {
		$types['resume_package'] = __( 'Resume Payment Package', 'wp-job-manager-listing-payments' );
		return $types;
	}


	/**
	 * Save Job Package data for the product.
	 *
	 * @since 2.0.0
	 *
	 * @param object $product WC Product Object.
	 * @param return void
	 */
	public static function save_product_meta( $product ) {
		// Only for resume package type.
		if ( 'resume_package' !== $product->get_type() ) {
			return;
		}

		// Set Duration.
		if ( isset( $_POST['_resume_duration'] ) ) {
			$product->set_resume_duration( absint( $_POST['_resume_duration'] ) ? absint( $_POST['_resume_duration'] ) : '' );
		}

		// Set Listing Limit.
		if ( isset( $_POST['_resume_limit'] ) ) {
			$product->set_resume_limit( absint( $_POST['_resume_limit'] ) ? absint( $_POST['_resume_limit'] ) : '' );
		}

		// Set Purchase Limit.
		if ( isset( $_POST['_resume_purchase_limit'] ) ) {
			$product->set_resume_purchase_limit( absint( $_POST['_resume_purchase_limit'] ) ? absint( $_POST['_resume_purchase_limit'] ) : '' );
		}

		// Featured.
		if ( isset( $_POST['_resume_featured'] ) && 'yes' === $_POST['_resume_featured'] ) {
			$product->set_resume_featured( 'yes' );
		} else {
			$product->set_resume_featured( 'no' );
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
		<div class="options_group show_if_resume_package">

			<?php woocommerce_wp_text_input( array(
				'id'             => '_resume_limit',
				'label'          => __( 'Resume posting limit', 'wp-job-manager-listing-payments' ),
				'description'    => __( 'The number of resumes a user can post with this package.', 'wp-job-manager-listing-payments' ),
				'value'          => method_exists( $product, 'get_limit' ) ? $product->get_limit() : '',
				'placeholder'    => __( 'Unlimited', 'wp-job-manager-listing-payments' ),
				'type'           => 'number',
				'desc_tip'       => true,
				'custom_attributes' => array(
					'min'     => '',
					'step'    => '1',
				),
			) ); ?>

			<?php woocommerce_wp_text_input( array(
				'id'             => '_resume_duration',
				'label'          => __( 'Resume listing duration', 'wp-job-manager-listing-payments' ),
				'description'    => __( 'The number of days that the resume will be active.', 'wp-job-manager-listing-payments' ),
				'value'          => method_exists( $product, 'get_duration' ) ? $product->get_duration() : '',
				'placeholder'    => get_option( 'resume_manager_submission_duration' ),
				'desc_tip'       => true,
				'type'           => 'number',
				'custom_attributes' => array(
					'min'    => '',
					'step'   => '1',
				),
			) ); ?>

			<?php woocommerce_wp_text_input( array(
				'id'             => '_resume_purchase_limit',
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
				'id'             => '_resume_featured',
				'label'          => __( 'Feature Listings?', 'wp-job-manager-listing-payments' ),
				'description'    => __( 'Feature this resume - it will be styled differently and sticky.', 'wp-job-manager-listing-payments' ),
				'value'          => method_exists( $product, 'is_listing_featured' ) ? ( $product->is_listing_featured() ? 'yes' : 'no' ) : '',
			) ); ?>

			<script type="text/javascript">
				jQuery(function(){
					jQuery( '.pricing' ).addClass( 'show_if_resume_package' );
					jQuery( '._tax_status_field' ).closest( 'div' ).addClass( 'show_if_resume_package' );
					jQuery( '#product-type' ).change();
				});
			</script>
		</div>
		<?php
	}

}
