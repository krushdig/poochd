<?php
/**
 * User Packages.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category User Packages
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * User Packages Admin
 *
 * @since 2.0.0
 */
class User_Packages {

	/**
	 * Init.
	 *
	 * @since 2.0.0
	 */
	public static function init() {

		// Register As WC & JM Screen ID: for scripts, etc.
		add_filter( 'woocommerce_screen_ids', array( __CLASS__, 'add_screen_ids' ) );
		add_filter( 'job_manager_admin_screen_ids', array( __CLASS__, 'add_screen_ids' ) );

		// Add Admin Page.
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_page' ), 20 );

		// Parse Query.
		add_filter( 'parse_query', array( __CLASS__, 'parse_query' ) );

		// User deletion.
		add_action( 'delete_user', array( __CLASS__, 'delete_user_packages' ) );
	}

	/**
	 * Screen IDS.
	 *
	 * @since 2.0.0
	 *
	 * @param array $ids Ids.
	 * @return array
	 */
	public static function add_screen_ids( $ids ) {
		$ids[] = 'users_page_wc_paid_listings_packages';
		return $ids;
	}

	/**
	 * Add menu items
	 *
	 * @since 2.0.0
	 */
	public static function register_admin_page() {
		$page = add_submenu_page(
			$parent_slug  = 'users.php',
			$page_title   = __( 'Listing Packages', 'wp-job-manager-listing-payments' ),
			$menu_title   = __( 'Listing Packages', 'wp-job-manager-listing-payments' ),
			$capability   = 'manage_options',
			$menu_slug    = 'wc_paid_listings_packages',
			$cb_function  = array( __CLASS__, 'admin_page_callback' )
		);

		// Column Style.
		add_action( "admin_head-{$page}", function() {
			printf( '<style id="astoundify-wpjmlp-column-style">%s</style>', '#id.column-id { width:60px; }' );
		} );
	}


	/**
	 * Manage Packages Admin Page.
	 *
	 * @since 2.0.0
	 */
	public static function admin_page_callback() {
		global $wpdb;

		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : '';

		// Delete action.
		if ( 'delete' === $action && current_user_can( 'manage_options' ) ) {

			// Single delete.
			if ( ! empty( $_GET['delete_nonce'] ) && wp_verify_nonce( $_GET['delete_nonce'], 'delete' ) ) {
				$package_id = absint( $_REQUEST['package_id'] );
				$wpdb->delete( "{$wpdb->prefix}wcpl_user_packages", array(
					'id' => $package_id,
				) );
				$wpdb->delete( $wpdb->postmeta, array(
					'meta_key'   => '_user_package_id',
					'meta_value' => $package_id,
				) );
				echo sprintf( '<div class="updated"><p>%s</p></div>', esc_html__( 'Package successfully deleted', 'wp-job-manager-listing-payments' ) );
			} elseif ( isset( $_POST['package'] ) && ! empty( $_POST['package'] ) && is_array( $_POST['package'] ) && ! empty( $_POST['delete_bulk_nonce'] ) && wp_verify_nonce( $_POST['delete_bulk_nonce'], __FILE__ ) ) { // Bulk delete.
				$del_count = 0;
				foreach ( $_POST['package'] as $package_id ) {
					$del = $wpdb->delete( "{$wpdb->prefix}wcpl_user_packages", array(
						'id' => $package_id,
					) );
					if ( $del ) {
						$del_count = $del_count + 1;
					}
					$wpdb->delete( $wpdb->postmeta, array(
						'meta_key'   => '_user_package_id',
						'meta_value' => $package_id,
					) );
				} // End foreach().
				// Translators: %s is Deleted package count.
				$notice = sprintf( _n( '%s Package successfully deleted', '%s Packages successfully deleted', $del_count, 'wp-job-manager-listing-payments' ), $del_count );
				echo sprintf( '<div class="updated"><p>%s</p></div>', esc_html( $notice ) );
			}
		}

		// Add / Edit User Package.
		if ( 'add' === $action || 'edit' === $action ) {
			$edit_package = new User_Packages_Admin_Edit_Package();
			?>
			<div class="woocommerce wrap">
				<?php if ( 'add' === $action ) { ?>
					<h1><?php esc_html_e( 'Add User Package', 'wp-job-manager-listing-payments' ); ?></h1>
				<?php } elseif ( 'edit' === $action ) { ?>
					<h1><?php esc_html_e( 'Edit User Package', 'wp-job-manager-listing-payments' ); ?></h1>
				<?php } ?>
				<form id="package-edit-form" method="post">
					<input type="hidden" name="page" value="wc_paid_listings_packages" />
					<?php $edit_package->form() ?>
					<?php wp_nonce_field( 'save', 'wc_paid_listings_packages_nonce' ); ?>
				</form>
			</div>
			<?php
		} else { // User Package List Table.
			$table = new User_Packages_Admin_List_Packages();
			$table->prepare_items();
			$add_package_url = add_query_arg( array(
				'page'   => 'wc_paid_listings_packages',
				'action' => 'add',
			), admin_url( 'users.php' ) );
			?>
			<div class="woocommerce wrap">
				<h1><?php esc_html_e( 'Listing Packages', 'wp-job-manager-listing-payments' ); ?> <a href="<?php echo esc_url( $add_package_url ); ?>" class="page-title-action"><?php esc_html_e( 'Add User Package', 'wp-job-manager-listing-payments' ); ?></a></h1>
				<form id="package-management" method="post">
					<input type="hidden" name="page" value="wc_paid_listings_packages" />
					<?php $table->display() ?>
					<?php wp_nonce_field( 'save', 'wc_paid_listings_packages_nonce' ); ?>
					<?php wp_nonce_field( __FILE__, 'delete_bulk_nonce' ); ?>
				</form>
			</div>
			<?php
		}
	}


	/**
	 * Filters and sorting handler
	 *
	 * @since 2.0.0
	 *
	 * @param object $query Query.
	 */
	public static function parse_query( $query ) {
		global $typenow, $wp_query;
		if ( 'job_listing' === $typenow || 'resume' === $typenow ) {
			if ( isset( $_GET['package'] ) ) {
				$query->query_vars['meta_key']   = '_user_package_id';
				$query->query_vars['meta_value'] = absint( $_GET['package'] );
			}
		}

		return $query;
	}


	/**
	 * Delete packages on user deletion.
	 *
	 * @since 2.0.0
	 *
	 * @param int $user_id User ID.
	 */
	public static function delete_user_packages( $user_id ) {
		global $wpdb;

		if ( $user_id ) {
			$wpdb->delete(
				"{$wpdb->prefix}wcpl_user_packages",
				array(
					'user_id' => $user_id,
				)
			);
		}
	}

}
