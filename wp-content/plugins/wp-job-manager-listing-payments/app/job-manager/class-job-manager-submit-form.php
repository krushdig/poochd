<?php
/**
 * Job Manager Submit Form Integration.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Job Manager
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * Job Manager Submit Form
 *
 * @since 2.0.0
 */
class Job_Manager_Submit_Form {

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
		// Append Package Name.
		add_filter( 'the_title', array( __CLASS__, 'append_package_name' ) );

		// Load scripts.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'styles' ) );

		// Filter job steps.
		add_filter( 'submit_job_steps', array( __CLASS__, 'submit_job_steps' ), 20 );

		// Posted Data.
		if ( ! empty( $_POST['job_package'] ) ) {
			if ( is_numeric( $_POST['job_package'] ) ) {
				self::$package_id      = absint( $_POST['job_package'] );
				self::$is_user_package = false;
			} else {
				self::$package_id      = absint( substr( $_POST['job_package'], 5 ) );
				self::$is_user_package = true;
			}
		} elseif ( ! empty( $_COOKIE['chosen_package_id'] ) ) {
			self::$package_id      = absint( $_COOKIE['chosen_package_id'] );
			self::$is_user_package = absint( $_COOKIE['chosen_package_is_user_package'] ) === 1;
		}

		// Clean-up URL if auto selected package is not available.
		add_action( 'template_redirect', function() {

			// Auto selected package.
			$auto_select_package = self::auto_select_package();

			// Only in submit job form.
			$submit_page = get_option( 'job_manager_submit_job_form_page_id' );

			// If package not found, clean up incorrect url by redirect to submit form.
			if ( is_page( $submit_page ) && isset( $_GET['choose_package'] ) && $_GET['choose_package'] && ! $auto_select_package ) {
				wp_safe_redirect( esc_url_raw( get_permalink( $submit_page ) ) );
				exit;
			}

			// Straight to checkout if payment required.
			if ( $auto_select_package && 'payment_required' === get_option( 'job_manager_paid_listings_flow' ) ) {
				self::process_package( $auto_select_package, false, false );
			}
		} );
	}

	/**
	 * Replace a page title with the endpoint title.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $title Title.
	 * @return string
	 */
	public static function append_package_name( $title ) {
		// Set package ID using get choose package.
		$auto_select_package = self::auto_select_package();
		if ( $auto_select_package ) {
			self::$package_id = $auto_select_package;
		}

		// Filter title and append package name.
		if ( ( ! empty( $_POST ) || $auto_select_package ) && ! is_admin() && is_main_query() && in_the_loop() && is_page( get_option( 'job_manager_submit_job_form_page_id' ) ) && self::$package_id && 'before' === get_option( 'job_manager_paid_listings_flow' ) && apply_filters( 'wcpl_append_package_name', true ) ) {

			// User package.
			if ( self::$is_user_package ) {
				$package = astoundify_wpjmlp_get_user_package( self::$package_id );
				$title .= ' &ndash; ' . $package->get_title();
			} else { // Regular packages.
				$post = get_post( self::$package_id );
				if ( $post ) {
					$title .= ' &ndash; ' . $post->post_title;
				}
			}

			// Remove filter.
			remove_filter( 'the_title', array( __CLASS__, 'append_package_name' ) );

		} // End if().
		return $title;
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
	 * Change initial job status
	 *
	 * @since 2.0.0
	 *
	 * @param string $status Status.
	 * @param object $job    Job.
	 * @return string
	 */
	public static function submit_job_post_status( $status, $job ) {
		switch ( $job->post_status ) {
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
	 * Change the steps during the submission process.
	 *
	 * @since 2.0.0
	 *
	 * @param  array $steps Form Steps.
	 * @return array
	 */
	public static function submit_job_steps( $steps ) {
		$job_packages = astoundify_wpjmlp_get_job_packages( array(), false );

		if ( $job_packages && apply_filters( 'astoundify_wpjmlp_enable_paid_job_listing_submission', true ) ) {

			$job_packages_ids = array();
			foreach ( $job_packages as $job_package ) {
				$job_packages_ids[] = intval( $job_package->get_id() );
			}

			// We need to hijack the preview submission to redirect to WooCommerce,
			// and add a step to select a package.
			// Add a step to allow the user to choose a package. Comes after preview.
			$steps['wc-choose-package'] = array(
				'name'     => __( 'Choose a package', 'wp-job-manager-listing-payments' ),
				'view'     => array( __CLASS__, 'choose_package' ),
				'handler'  => array( __CLASS__, 'choose_package_handler' ),
				'priority' => 25,
			);

			// If we instead want to show the package selection FIRST, change the priority and add a new handler.
			if ( 'before' === get_option( 'job_manager_paid_listings_flow' ) || 'payment_required' === get_option( 'job_manager_paid_listings_flow' ) ) {
				$steps['wc-choose-package']['priority'] = 5;
				$steps['wc-process-package'] = array(
					'name'     => '',
					'view'     => false,
					'handler'  => array( __CLASS__, 'choose_package_handler' ),
					'priority' => 25,
				);

				// Unset choose package if autoselected.
				$auto_select_package = self::auto_select_package();
				if ( $auto_select_package ) {
					unset( $steps['wc-choose-package'] );
				}

			} else { // If showing the package step after preview, the preview button text should be changed to show this.
				add_filter( 'submit_job_step_preview_submit_text', array( __CLASS__, 'submit_button_text' ), 10 );
			}

			// Remove Submit and Preview Step.
			if ( 'payment_required' === get_option( 'job_manager_paid_listings_flow' ) && true !== self::$is_user_package ) {
				unset( $steps['submit'] );
				unset( $steps['preview'] );
			}

			// We should make sure new jobs are pending payment and not published or pending.
			add_filter( 'submit_job_post_status', array( __CLASS__, 'submit_job_post_status' ), 10, 2 );
		} // End if().
		return $steps;
	}

	/**
	 * Get the package ID being used for job submission, expanding any user package.
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
	 *
	 * @param array $atts Atts.
	 */
	public static function choose_package( $atts = array() ) {
		$form      = \WP_Job_Manager_Form_Submit_Job::instance();
		$job_id    = $form->get_job_id();
		$step      = $form->get_step();
		$form_name = $form->form_name;
		$user_packages = astoundify_wpjmlp_get_user_packages( get_current_user_id(), 'job_listing' );
		$packages  = astoundify_wpjmlp_get_job_packages( isset( $atts['packages'] ) ? explode( ',', $atts['packages'] ) : array() );
		$button_text   = ( 'before' !== get_option( 'job_manager_paid_listings_flow' ) || 'payment_required' !== get_option( 'job_manager_paid_listings_flow' ) ) ? __( 'Submit &rarr;', 'wp-job-manager-listing-payments' ) : __( 'Listing Details &rarr;', 'wp-job-manager-listing-payments' );
		?>
		<form method="post" id="job_package_selection">
			<div class="job_listing_packages_title">
				<input type="submit" name="continue" class="button" value="<?php echo esc_attr( apply_filters( 'submit_job_step_choose_package_submit_text', $button_text ) ); ?>" />
				<input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />
				<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>" />
				<input type="hidden" name="job_manager_form" value="<?php echo esc_attr( $form_name ); ?>" />
				<h2><?php esc_attr_e( 'Choose a package', 'wp-job-manager-listing-payments' ); ?></h2>
			</div>
			<div class="job_listing_packages">
				<?php get_job_manager_template(
					$template_name  = 'package-selection.php',
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
	 * @param  int  $package_id      Package ID.
	 * @param  bool $is_user_package User Package ID.
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

			if ( ! $package->is_type( 'job_package' ) && ! $package->is_type( 'job_package_subscription' ) ) {
				return new \WP_Error( 'error', __( 'Invalid Package', 'wp-job-manager-listing-payments' ) );
			}

			// Don't let them buy the same subscription twice if the subscription is for the package.
			if ( class_exists( 'WC_Subscriptions' ) && is_user_logged_in() && 'job_package_subscription' === $package->get_type() && 'package' === $package->get_package_subscription_type() ) {
				if ( wcs_user_has_subscription( get_current_user_id(), $package_id, 'active' ) ) {
					return new \WP_Error( 'error', __( 'You already have this subscription.', 'wp-job-manager-listing-payments' ) );
				}
			}
		}
		return true;
	}

	/**
	 * Purchase a job package
	 *
	 * @since 2.0.0
	 *
	 * @param  int|string $package_id      Package ID.
	 * @param  bool       $is_user_package Is User Package ID.
	 * @param  int        $job_id          Job ID.
	 * @return bool Did it work or not?
	 */
	private static function process_package( $package_id, $is_user_package, $job_id ) {
		// Make sure the job has the correct status.
		if ( 'preview' === get_post_status( $job_id ) ) {
			// Update job listing.
			$update_job                  = array();
			$update_job['ID']            = $job_id;
			$update_job['post_status']   = 'pending_payment';
			$update_job['post_date']     = current_time( 'mysql' );
			$update_job['post_date_gmt'] = current_time( 'mysql', 1 );
			$update_job['post_author']   = get_current_user_id();
			wp_update_post( $update_job );
		}

		// Process user package.
		if ( $is_user_package ) {

			$user_package = astoundify_wpjmlp_get_user_package( $package_id );
			$package      = wc_get_product( $user_package->get_product_id() );

			// Give job the package attributes.
			update_post_meta( $job_id, '_job_duration', $user_package->get_duration() );
			update_post_meta( $job_id, '_package_id', $user_package->get_product_id() );
			update_post_meta( $job_id, '_user_package_id', $package_id );
			update_post_meta( $job_id, '_featured', $user_package->is_listing_featured() ? 1 : 0 );

			if ( $package && 'job_package_subscription' === $package->get_type() && 'listing' === $package->get_package_subscription_type() ) {
				update_post_meta( $job_id, '_job_expires', '' ); // Never expire automatically.
			}

			// Approve the job.
			if ( in_array( get_post_status( $job_id ), array( 'pending_payment', 'expired' ), true ) ) {
				astoundify_wpjmlp_approve_job_listing_with_package( $job_id, get_current_user_id(), $package_id );
			}

			do_action( 'astoundify_wpjmlp_process_package_for_job_listing', $package_id, $is_user_package, $job_id );

			return true;

		} elseif ( $package_id ) { // Process new package.

			$package = wc_get_product( $package_id );

			// Give job the package attributes.
			update_post_meta( $job_id, '_job_duration', $package->get_duration() );
			update_post_meta( $job_id, '_package_id', $package_id );
			update_post_meta( $job_id, '_featured', $package->is_listing_featured() ? 1 : 0 );

			wp_update_post( array(
				'ID'    => $job_id,
				'menu_order' => $package->is_listing_featured() ? -1 : 0,
			) );

			if ( 'job_package_subscription' === $package->get_type() && 'listing' === $package->get_package_subscription_type() ) {
				update_post_meta( $job_id, '_job_expires', '' ); // Never expire automatically.
			}

			// Add package to the cart.
			WC()->cart->add_to_cart( $package_id, 1, '', '', array(
				'job_id' => $job_id,
			) );

			// Clear cookie.
			wc_setcookie( 'chosen_package_id', '', time() - HOUR_IN_SECONDS );
			wc_setcookie( 'chosen_package_is_user_package', '', time() - HOUR_IN_SECONDS );

			do_action( 'astoundify_wpjmlp_process_package_for_job_listing', $package_id, $is_user_package, $job_id );

			// Redirect to checkout page.
			wp_safe_redirect( esc_url_raw( wc_get_checkout_url() ) );
			exit;
		} // End if().
	}

	/**
	 * Choose package handler
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public static function choose_package_handler() {
		$form = \WP_Job_Manager_Form_Submit_Job::instance();

		// Package ID.
		$auto_select_package = self::auto_select_package();
		if ( $auto_select_package ) {
			self::$package_id = $auto_select_package;
		}

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

		// Process the package unless we're doing this before a job is submitted.
		if ( 'wc-process-package' === $form->get_step_key() || 'before' !== get_option( 'job_manager_paid_listings_flow' ) ) {
			// Product the package.
			if ( self::process_package( self::$package_id, self::$is_user_package, $form->get_job_id() ) ) {
				$form->next_step();
			}
		} else {
			$form->next_step();
		}
	}

	/**
	 * Auto Select Package Using Choose Package URL.
	 *
	 * @since 2.0.1
	 */
	public static function auto_select_package() {
		// Bail if not set.
		if ( ! isset( $_GET['choose_package'] ) || ! $_GET['choose_package'] ) {
			return false;
		}

		// Get packages ID.
		$job_packages = astoundify_wpjmlp_get_job_packages();
		$job_packages_ids = array();

		if ( empty( $job_packages ) ) {
			return false;
		}

		foreach ( $job_packages as $package ) {
			$job_packages_ids[] = $package->get_id();
		}

		// Auto selected package.
		if ( in_array( intval( $_GET['choose_package'] ), $job_packages_ids, true ) ) {
			return intval( $_GET['choose_package'] );
		}

		return false;
	}

}
