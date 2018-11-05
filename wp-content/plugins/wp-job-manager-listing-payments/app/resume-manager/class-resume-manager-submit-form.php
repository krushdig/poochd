<?php
/**
 * Resume Manager Submit Form.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Resume Manager
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * Submit Form Integration.
 *
 * @since 2.0.0
 */
class Resume_Manager_Submit_Form {

	/**
	 * Package ID
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	private static $package_id = 0;

	/**
	 * Is User Package
	 *
	 * @since 2.0.0
	 *
	 * @var bool
	 */
	private static $is_user_package = false;

	/**
	 * Init
	 *
	 * @since 2.0.0
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'styles' ) );
		add_filter( 'submit_resume_steps', array( __CLASS__, 'submit_resume_steps' ), 10 );

		// Posted Data.
		if ( ! empty( $_POST['resume_package'] ) ) {
			if ( is_numeric( $_POST['resume_package'] ) ) {
				self::$package_id      = absint( $_POST['resume_package'] );
				self::$is_user_package = false;
			} else {
				self::$package_id      = absint( substr( $_POST['resume_package'], 5 ) );
				self::$is_user_package = true;
			}
		} elseif ( ! empty( $_COOKIE['chosen_package_id'] ) ) {
			self::$package_id      = absint( $_COOKIE['chosen_package_id'] );
			self::$is_user_package = absint( $_COOKIE['chosen_package_is_user_package'] ) === 1;
		}
	}

	/**
	 * Add form styles.
	 *
	 * @since 2.0.0
	 */
	public static function styles() {
		wp_enqueue_style( 'astoundify-wpjmlp-packages', ASTOUNDIFY_WPJMLP_URL . 'public/css/packages.min.css' );
	}

	/**
	 * Change submit button text
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public static function submit_button_text() {
		return __( 'Choose a package &rarr;', 'wp-job-manager-listing-payments' );
	}

	/**
	 * Change initial job status.
	 *
	 * @since 2.0.0
	 *
	 * @param string $status Status.
	 * @param object $resume Resume.
	 * @return string
	 */
	public static function submit_resume_post_status( $status, $resume ) {
		switch ( $resume->post_status ) {
			case 'preview' :
				$status = 'pending_payment';
			break;
			case 'expired' :
				$status = 'expired';
			break;
			default :
				$status = $status;
			break;
		}
		return $status;
	}

	/**
	 * Change the steps during the submission process
	 *
	 * @since 2.0.0
	 *
	 * @param  array $steps Form Steps.
	 * @return array
	 */
	public static function submit_resume_steps( $steps ) {

		if ( astoundify_wpjmlp_get_resume_packages( array(), false ) && apply_filters( 'astoundify_wpjmlp_enable_paid_resume_submission', true ) ) {
			// We need to hijack the preview submission to redirect to WooCommerce and add a step to select a package.
			// Add a step to allow the user to choose a package. Comes after preview.
			$steps['wc-choose-package'] = array(
				'name'     => __( 'Choose a package', 'wp-job-manager-listing-payments' ),
				'view'     => array( __CLASS__, 'choose_package' ),
				'handler'  => array( __CLASS__, 'choose_package_handler' ),
				'priority' => 25,
			);

			// If we instead want to show the package selection FIRST, change the priority and add a new handler.
			if ( 'before' === get_option( 'resume_manager_paid_listings_flow' ) || 'payment_required' === get_option( 'resume_manager_paid_listings_flow' ) ) {
				$steps['wc-choose-package']['priority'] = 5;
				$steps['preview']['handler'] = array( __CLASS__, 'preview_handler' );
				$steps['wc-process-package'] = array(
					'name'     => '',
					'view'     => false,
					'handler'  => array( __CLASS__, 'choose_package_handler' ),
					'priority' => 25,
				);
			} else { // If showing the package step after preview, the preview button text should be changed to show this.
				add_filter( 'submit_resume_step_preview_submit_text', array( __CLASS__, 'submit_button_text' ), 10 );
			}

			// Remove Submit and Preview Step.
			if ( 'payment_required' === get_option( 'resume_manager_paid_listings_flow' ) && true !== self::$is_user_package ) {
				unset( $steps['submit'] );
				unset( $steps['preview'] );
			}

			// We should make sure new jobs are pending payment and not published or pending.
			add_filter( 'submit_resume_post_status', array( __CLASS__, 'submit_resume_post_status' ), 10, 2 );
		}
		return $steps;
	}

	/**
	 * Get the package ID being used for resume submission, expanding any user package.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public static function get_package_id() {
		if ( self::$is_user_package ) {
			$package = astoundify_wpjmlp_get_user_package( self::$package_id );
			return $package->get_product_id();
		}
		return self::$package_id;
	}

	/**
	 * Choose package form.
	 *
	 * @since 2.0.0
	 */
	public static function choose_package() {
		$form      = \WP_Resume_Manager_Form_Submit_Resume::instance();
		$resume_id = $form->get_resume_id();
		$job_id    = $form->get_job_id();
		$step      = $form->get_step();
		$form_name = $form->form_name;
		$packages      = astoundify_wpjmlp_get_resume_packages();
		$user_packages = astoundify_wpjmlp_get_user_packages( get_current_user_id(), 'resume' );
		$button_text   = ( 'before' !== get_option( 'resume_manager_paid_listings_flow' ) || 'payment_required' !== get_option( 'job_manager_paid_listings_flow' ) ) ? __( 'Submit &rarr;', 'wp-job-manager-listing-payments' ) : __( 'Listing Details &rarr;', 'wp-job-manager-listing-payments' );
		?>
		<form method="post" id="job_package_selection">
			<div class="job_listing_packages_title">
				<input type="submit" name="continue" class="button" value="<?php echo esc_attr( apply_filters( 'submit_job_step_choose_package_submit_text', $button_text ) ); ?>" />
				<input type="hidden" name="resume_id" value="<?php echo esc_attr( $resume_id ); ?>" />
				<input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />
				<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>" />
				<input type="hidden" name="resume_manager_form" value="<?php echo esc_attr( $form_name ); ?>" />
				<h2><?php esc_html_e( 'Choose a package', 'wp-job-manager-listing-payments' ); ?></h2>
			</div>
			<div class="job_listing_packages">
				<?php get_job_manager_template(
					$template_name  = 'resume-package-selection.php',
					$args           = array(
						'packages'      => $packages,
						'user_packages' => $user_packages,
					),
					$template_path  = 'listing-payments',
					$default_path   = ASTOUNDIFY_WPJMLP_TEMPLATE_PATH
				); ?>
			</div>
		</form>
		<?php
	}

	/**
	 * Validate package
	 *
	 * @since 2.0.0
	 *
	 * @param int  $package_id      Package ID.
	 * @param bool $is_user_package Is user package ID.
	 * @return bool|WP_Error
	 */
	private static function validate_package( $package_id, $is_user_package ) {
		if ( empty( $package_id ) ) {
			return new \WP_Error( 'error', __( 'Invalid Package', 'wp-job-manager-listing-payments' ) );
		} elseif ( $is_user_package ) {
			if ( ! astoundify_wpjmlp_package_is_valid( get_current_user_id(), $package_id ) ) {
				return new \WP_Error( 'error', __( 'Invalid Package', 'wp-job-manager-listing-payments' ) );
			}
		} else {
			$package = wc_get_product( $package_id );

			if ( ! $package->is_type( 'resume_package' ) && ! $package->is_type( 'resume_package_subscription' ) ) {
				return new \WP_Error( 'error', __( 'Invalid Package', 'wp-job-manager-listing-payments' ) );
			}

			// Don't let them buy the same subscription twice.
			if ( class_exists( 'WC_Subscriptions' ) && is_user_logged_in() && 'resume_package_subscription' === $package->get_type() && 'package' === $package->get_package_subscription_type() ) {
				if ( wcs_user_has_subscription( get_current_user_id(), $package_id, 'active' ) ) {
					return new \WP_Error( 'error', __( 'You already have this subscription.', 'wp-job-manager-listing-payments' ) );
				}
			}
		}
		return true;
	}

	/**
	 * Purchase a job package.
	 *
	 * @since 2.0.0
	 *
	 * @param  int|string $package_id      Package ID.
	 * @param  bool       $is_user_package Is user package.
	 * @param  int        $resume_id       Resume ID.
	 * @return bool Did it work or not?
	 */
	private static function process_package( $package_id, $is_user_package, $resume_id ) {
		// Make sure the job has the correct status.
		if ( 'preview' === get_post_status( $resume_id ) ) {
			// Update job listing.
			$update                  = array();
			$update['ID']            = $resume_id;
			$update['post_status']   = 'pending_payment';
			$update['post_date']     = current_time( 'mysql' );
			$update['post_date_gmt'] = current_time( 'mysql', 1 );
			$update['post_author']   = get_current_user_id();
			wp_update_post( $update );
		}

		if ( $is_user_package ) {
			$user_package = astoundify_wpjmlp_get_user_package( $package_id );
			$package      = wc_get_product( $user_package->get_product_id() );

			// Give resume the package attributes.
			update_post_meta( $resume_id, '_resume_duration', $user_package->get_duration() );
			update_post_meta( $resume_id, '_package_id', $user_package->get_product_id() );
			update_post_meta( $resume_id, '_user_package_id', $package_id );
			update_post_meta( $resume_id, '_featured', $user_package->is_listing_featured() ? 1 : 0 );

			if ( $package && 'resume_package_subscription' === $package->get_type() && 'listing' === $package->get_package_subscription_type() ) {
				update_post_meta( $job_id, '_resume_expires', '' ); // Never expire automatically.
			}

			// Approve the resume.
			if ( in_array( get_post_status( $resume_id ), array( 'pending_payment', 'expired' ), true ) ) {
				astoundify_wpjmlp_approve_resume_with_package( $resume_id, get_current_user_id(), $package_id );
			}

			do_action( 'astoundify_wpjmlp_process_package_for_resume', $package_id, $is_user_package, $resume_id );

			return true;
		} elseif ( $package_id ) {
			$package = wc_get_product( $package_id );

			// Give resume the package attributes.
			update_post_meta( $resume_id, '_resume_duration', $package->get_duration() );
			update_post_meta( $resume_id, '_package_id', $package->get_product_id() );
			update_post_meta( $resume_id, '_featured', $package->is_listing_featured() ? 1 : 0 );

			// Add package to the cart.
			WC()->cart->add_to_cart( $package_id, 1, '', '', array(
				'resume_id' => $resume_id,
			) );

			// Clear cookie.
			wc_setcookie( 'chosen_package_id', '', time() - HOUR_IN_SECONDS );
			wc_setcookie( 'chosen_package_is_user_package', '', time() - HOUR_IN_SECONDS );

			do_action( 'astoundify_wpjmlp_process_package_for_resume', $package_id, $is_user_package, $resume_id );

			// Redirect to checkout page.
			wp_safe_redirect( esc_url_raw( wc_get_checkout_url() ) );
			exit;
		} // End if().
	}

	/**
	 * Choose package handler.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public static function choose_package_handler() {
		$form = \WP_Resume_Manager_Form_Submit_Resume::instance();

		// Validate Selected Package.
		$validation = self::validate_package( self::$package_id, self::$is_user_package );

		// Error? Go back to choose package step.
		if ( is_wp_error( $validation ) ) {
			$form->add_error( $validation->get_error_message() );
			$form->set_step( array_search( 'wc-choose-package', array_keys( $form->get_steps() ), true ) );
			return false;
		}

		// Store selection in cookie.
		wc_setcookie( 'chosen_package_id', self::$package_id );
		wc_setcookie( 'chosen_package_is_user_package', self::$is_user_package ? 1 : 0 );

		// Process the package unless we're doing this before a resume is submitted.
		if ( 'wc-process-package' === $form->get_step_key() || 'before' !== get_option( 'resume_manager_paid_listings_flow' ) ) {
			// Product the package.
			if ( self::process_package( self::$package_id, self::$is_user_package, $form->get_resume_id() ) ) {
				$form->next_step();
			}
		} else {
			$form->next_step();
		}
	}


	/**
	 * Preview Step Form handler.
	 *
	 * @since 2.0.0
	 */
	public static function preview_handler() {
		$form = \WP_Resume_Manager_Form_Submit_Resume::instance();
		if ( ! $_POST ) {
			return;
		}

		// Edit = show submit form again.
		if ( ! empty( $_POST['edit_resume'] ) ) {
			$form->previous_step();
		}

		// Continue = change job status then show next screen.
		if ( ! empty( $_POST['continue'] ) ) {
			$resume = get_post( $form->get_resume_id() );

			if ( in_array( $resume->post_status, array( 'preview', 'expired' ), true ) ) {
				// Reset expiry.
				delete_post_meta( $resume->ID, '_resume_expires' );

				// Update listing.
				$update_resume                  = array();
				$update_resume['ID']            = $resume->ID;
				$update_resume['post_date']     = current_time( 'mysql' );
				$update_resume['post_date_gmt'] = current_time( 'mysql', 1 );
				$update_resume['post_author']   = get_current_user_id();
				$update_resume['post_status']   = apply_filters( 'submit_resume_post_status', get_option( 'resume_manager_submission_requires_approval' ) ? 'pending' : 'publish', $resume );

				wp_update_post( $update_resume );
			}

			$form->next_step();
		}
	}

}
