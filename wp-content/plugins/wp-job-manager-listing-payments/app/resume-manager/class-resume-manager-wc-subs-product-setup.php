<?php
/**
 * Resume Manager WC Subscriptions Product Setup.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Resume Manager
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * Resume Manager WC Subscriptions Product Setup.
 *
 * @since 1.0.0
 */
class Resume_Manager_WC_Subs_Product_Setup {

	/**
	 * Init.
	 *
	 * @since 2.0.0
	 */
	public static function init() {

		// Add product type.
		add_filter( 'woocommerce_subscription_product_types', array( __CLASS__, 'add_subscription_product_types' ) );
		add_filter( 'product_type_selector', array( __CLASS__, 'add_product_type_selector' ) );

		// Process package.
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_product_meta' ) );

		// Product data options.
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'product_data_options' ), 9 );
	}


	/**
	 * Types for subscriptions.
	 *
	 * @since 2.0.0
	 *
	 * @param array $types Types.
	 * @return array
	 */
	public static function add_subscription_product_types( $types ) {
		$types[] = 'resume_package_subscription';
		return $types;
	}

	/**
	 * Add the product type selector.
	 *
	 * @since 2.0.0
	 *
	 * @param array $types Types.
	 * @return array
	 */
	public static function add_product_type_selector( $types ) {
		$types['resume_package_subscription'] = __( 'Resume Payment Package Subscription', 'wp-job-manager-listing-payments' );
		return $types;
	}

	/**
	 * Save Job Package data for the product.
	 *
	 * @since 2.0.0
	 *
	 * @param object $product WC Product Object.
	 */
	public static function save_product_meta( $product ) {
		// Only for resume package subscription type.
		if ( 'resume_package_subscription' !== $product->get_type() ) {
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

		// Subscription type.
		if ( isset( $_POST['_resume_package_subscription_type'] ) ) {
			$type = esc_attr( $_POST['_resume_package_subscription_type'] );
			$product->set_package_subscription_type( $type ? $type : 'package' );
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
		<div class="options_group show_if_resume_package_subscription">

			<?php woocommerce_wp_select( array(
				'id'             => '_resume_package_subscription_type',
				'wrapper_class'  => 'show_if_resume_package_subscription',
				'label'          => __( 'Subscription Type', 'wp-job-manager-listing-payments' ),
				'description'    => __( 'Choose how subscriptions affect this package', 'wp-job-manager-listing-payments' ),
				'value'          => method_exists( $product, 'get_package_subscription_type' ) ? $product->get_package_subscription_type() : '',
				'desc_tip'       => true,
				'options'        => array(
					'package'       => __( 'Link the subscription to the package (renew listing limit every subscription term)', 'wp-job-manager-listing-payments' ),
					'listing'       => __( 'Link the subscription to posted listings (renew posted listings every subscription term)', 'wp-job-manager-listing-payments' ),
				),
			) ); ?>

			<script type="text/javascript">
				jQuery( function($){
					$( '.options_group.show_if_resume_package' ).addClass( 'show_if_resume_package_subscription' );
					$( '._tax_status_field' ).closest( 'div' ).addClass( 'show_if_resume_package_subscription' );
					$( '.show_if_subscription, .grouping' ).addClass( 'show_if_resume_package_subscription' );
					$('#_resume_package_subscription_type').change(function(){
						if ( jQuery(this).val() === 'listing' ) {
							jQuery('#_resume_duration').closest('.form-field').hide().val('');
						} else {
							jQuery('#_resume_duration').closest('.form-field').show();
						}
					}).change();
					$( '#product-type' ).change( function() {
						if ( 'resume_package_subscription' !== $( '#product-type' ).val() ) {
							$( '#_resume_package_subscription_type' ).val( 'package' ).trigger( 'change' );
						}
					} );
				});
			</script>

		</div>
		<?php
	}

}
