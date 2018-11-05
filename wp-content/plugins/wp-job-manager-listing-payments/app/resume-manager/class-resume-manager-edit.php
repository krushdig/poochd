<?php
/**
 * Resume Manager Edit.
 *
 * @since 2.1.0
 *
 * @package Listing Payments
 * @category Resume Manager
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * Resume Manager Edit
 *
 * @since 2.0.0
 */
class Resume_Manager_Edit {

	/**
	 * Init
	 *
	 * @since 2.1.0
	 */
	public static function init() {

		// Edit field.
		add_filter( 'submit_resume_form_fields', array( __CLASS__, 'add_fields' ) );

		// Add Template.
		add_filter( 'job_manager_locate_template', array( __CLASS__, 'add_field_template' ) , 10, 3 );

		// Get current value of front end field.
		add_filter( 'submit_resume_form_fields_get_resume_data', array( __CLASS__, 'add_fields_data' ), 10, 2 );

		// Save Data.
		add_action( 'resume_manager_update_resume_data', array( __CLASS__, 'save_package' ), 10, 2 );
	}

	/**
	 * Add Edit Field If Enabled.
	 *
	 * @since 2.0.0
	 *
	 * @param array $fields Resume Manager Submit Fields.
	 * @return array
	 */
	public static function add_fields( $fields ) {
		// Only in edit form.
		if ( isset( $_GET['action'], $_GET['resume_id'] ) && 'edit' === $_GET['action'] ) {

			// Only if enabled.
			if ( get_option( 'resume_manager_paid_listings_switch_package', false ) ) {

				$fields['resume_fields']['package'] = array(
					'label'       => __( 'Package', 'wp-job-manager-listing-payments' ),
					'type'        => 'resume-payment-package', // "form-fields/resume-payment-package-field.php".
					'required'    => false,
					'placeholder' => '',
					'priority'    => 1,
					'default'     => '',
					'packages'    => array(),
					'resume'      => '',
					'value'       => '',
				);
			}
		}

		return $fields;
	}

	/**
	 * Add Field Template
	 *
	 * @since 2.0.0
	 *
	 * @param string $template      Found template path.
	 * @param string $template_name Loaded template name.
	 * @param string $template_path Template Path.
	 * @return string
	 */
	public static function add_field_template( $template, $template_name, $template_path ) {
		if ( 'form-fields/resume-payment-package-field.php' === $template_name && ! $template ) {
			return ASTOUNDIFY_WPJMLP_TEMPLATE_PATH . $template_name;
		}
		return $template;
	}

	/**
	 * Add fields data value.
	 * This will be loaded in field template file.
	 *
	 * This data cannot be added in "submit_resume_form_fields" because it's too early.
	 * The data is not yet set after saving it.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $fields Resume fields. 
	 * @param object $resume Resume Listing object.
	 * @return array
	 */
	public static function add_fields_data( $fields, $resume ) {

		// Only if package field is set.
		if ( isset( $fields['resume_fields']['package'] ) ) {

			$user_packages = array();

			// Get all user packages.
			$all_packages = astoundify_wpjmlp_get_user_packages( get_current_user_id(), 'resume', true );

			// Current package.
			$current_package_id = absint( get_post_meta( $resume->ID, '_user_package_id', true ) );

			// Add current package as user package.
			if ( $current_package_id && array_key_exists( $current_package_id, $all_packages ) ) {
				$user_packages[ $current_package_id ] = astoundify_wpjmlp_get_package( $all_packages[ $current_package_id ] );
			}

			// Set user packages.
			foreach ( $all_packages as $package_id => $package ) {
				$package = astoundify_wpjmlp_get_package( $package ); // get package object.

				// Only if limit doesn't reached yet.
				if ( ! $package->is_limit_reached() ) {
					$user_packages[ $package_id ] = $package;
				}
			}

			// Remove field if no packages or the single package is current.
			if ( empty( $user_packages ) || ( 1 === count( $user_packages ) && intval( current( $user_packages )->get_id() ) === intval( $current_package_id ) ) ) {
				unset( $fields['resume_fields']['package'] );
			} else {
				$fields['resume_fields']['package']['packages'] = $user_packages;
				$fields['resume_fields']['package']['resume'] = $resume;
				$fields['resume_fields']['package']['value'] = $current_package_id ? $current_package_id : 0;
			}
		}

		return $fields;
	}

	/**
	 * Save Package Data
	 *
	 * @since 2.0.0
	 */
	public static function save_package( $resume_id, $values ) {
		if ( ! isset( $_POST['payment-package'] ) || ! get_option( 'resume_manager_paid_listings_switch_package', false ) ) {
			return;
		}

		// Var.
		$resume        = get_post( $resume_id );
		$new_package   = $_POST['payment-package'];
		$old_package   = get_post_meta( $resume_id, '_user_package_id', true );
		$user_packages = astoundify_wpjmlp_get_user_packages( $resume->post_author, array( 'resume' ) );

		// If user switch to new package, check if new package exists.
		if ( $new_package && $new_package !== $old_package && array_key_exists( $new_package, $user_packages ) ) {

			// Add count to new package.
			astoundify_wpjmlp_increase_package_count( $resume->post_author, $new_package );

			// Update listing based on package.
			update_post_meta( $resume_id, '_resume_duration', $user_packages[ $new_package ]->package_duration );
			update_post_meta( $resume_id, '_featured', $user_packages[ $new_package ]->package_featured ? 1 : 0 );
			$expire_time = calculate_resume_expiry( $resume_id );
			if ( $expire_time ) {
				update_post_meta( $resume_id, '_resume_expires', $expire_time );
			}
			$product = wc_get_product( $user_packages[ $new_package ]->product_id );
			if ( $product && 'resume_package_subscription' === $product->get_type() && 'listing' === $product->get_package_subscription_type() ) {
				update_post_meta( $resume_id, '_resume_expires', '' ); // Never expire automatically in subscription.
			}

			// Update Meta.
			update_post_meta( $resume_id, '_user_package_id', $new_package );
		}
	}

}

