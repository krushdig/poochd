<?php
/**
 * Plugin Activation.
 * Everything related on plugin activation.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Bootstrap
 * @author Astoundify
 */

// Activation hook.
register_activation_hook( ASTOUNDIFY_WPJMLP_FILE, 'astoundify_wpjmlp_install' );

/**
 * Install the plugin.
 * - Create User Package Table
 * - Save Version Number
 *
 * @since 2.0.0
 */
function astoundify_wpjmlp_install() {
	global $wpdb;
	$wpdb->hide_errors();

	$collate = '';
	if ( $wpdb->has_cap( 'collation' ) ) {
		if ( ! empty($wpdb->charset ) ) {
			$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty($wpdb->collate ) ) {
			$collate .= " COLLATE $wpdb->collate";
		}
	}

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	// Create Table for user packages.
	$sql = "
CREATE TABLE {$wpdb->prefix}wcpl_user_packages (
  id bigint(20) NOT NULL auto_increment,
  user_id bigint(20) NOT NULL,
  product_id bigint(20) NOT NULL,
  order_id bigint(20) NOT NULL default 0,
  package_featured int(1) NULL,
  package_duration bigint(20) NULL,
  package_limit bigint(20) NOT NULL,
  package_count bigint(20) NOT NULL,
  package_type varchar(100) NOT NULL,
  PRIMARY KEY  (id)
) $collate;
";
	dbDelta( $sql );

	// Update version number.
	update_option( 'wpjmlp_db_version', ASTOUNDIFY_WPJMLP_VERSION );

	// Create term.
	add_action( 'shutdown', 'astoundify_wpjmlp_delayed_install' );
}


/**
 * Installer (delayed).
 * Create WooCommerce Product Type (terms).
 *
 * @since 2.0.0
 */
function astoundify_wpjmlp_delayed_install() {
	if ( ! get_term_by( 'slug', sanitize_title( 'job_package' ), 'product_type' ) ) {
		wp_insert_term( 'job_package', 'product_type' );
	}
	if ( ! get_term_by( 'slug', sanitize_title( 'resume_package' ), 'product_type' ) ) {
		wp_insert_term( 'resume_package', 'product_type' );
	}
	if ( ! get_term_by( 'slug', sanitize_title( 'job_package_subscription' ), 'product_type' ) ) {
		wp_insert_term( 'job_package_subscription', 'product_type' );
	}
	if ( ! get_term_by( 'slug', sanitize_title( 'resume_package_subscription' ), 'product_type' ) ) {
		wp_insert_term( 'resume_package_subscription', 'product_type' );
	}
}


// Disable WC Paid Listing Main Loader.
add_action( 'plugins_loaded', 'astoundify_wpjmlp_wcpl_compat_init', 9 );

/**
 * Remove WC Paid Listing Init Hook.
 *
 * @since 2.0.0
 */
function astoundify_wpjmlp_wcpl_compat_init(){

	// Disable WCPL Main Loader.
	remove_action( 'plugins_loaded', 'wp_job_manager_wcpl_init', 10 );

	// Add notice if active.
	add_action( 'admin_notices', 'astoundify_wpjmlp_wcpl_compat_notice' );
}


/**
 * Admin Notice About "WP Job Manager WC Paid Listing".
 *
 * @since 2.0.0
 */
function astoundify_wpjmlp_wcpl_compat_notice() {
	if ( ! function_exists( 'wp_job_manager_wcpl_init' ) ) {
		return;
	}
?>
<div class="notice notice-error">
	<?php echo wpautop( "<strong>" . __( 'Paid Listing Functionality is Disabled.', 'wp-job-manager-listing-payments' ) . "</strong> " . __( 'Please deactivate WC Paid Listings Plugin to use Listing Payments for WP Job Manager.', 'wp-job-manager-listing-payments' ) ); ?>
</div>
<?php
}
