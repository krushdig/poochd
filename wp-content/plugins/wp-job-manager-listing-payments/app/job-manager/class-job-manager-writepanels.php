<?php
/**
 * Job Manager WritePanels
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Job Manager
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * Job Manager WritePanels
 *
 * @since 2.0.0
 */
class Job_Manager_Writepanels {

	/**
	 * Init
	 *
	 * @since 2.0.0
	 */
	public static function init() {

		// Add meta boxes.
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );

		// Save post meta on the 'save_post' hook.
		add_action( 'save_post', array( __CLASS__, 'save_meta_box_data' ), 10, 2 );
	}


	/**
	 * Register Meta Boxes
	 *
	 * @since 2.0.0
	 */
	public static function add_meta_boxes() {
		add_meta_box(
			$id         = 'astoundify-wpjmlp-paid-listing-data',
			$title      = __( 'Listing Payment', 'wp-job-manager-listing-payments' ),
			$callback   = array( __CLASS__, 'listing_payments_data_meta_box' ),
			$screen     = array( 'job_listing' ),
			$context    = 'side'
		);
	}

	/**
	 * Meta Box Callback
	 *
	 * @since 2.0.0
	 *
	 * @param object $post Post object WP_Post.
	 * @param array  $box Meta box.
	 */
	public static function listing_payments_data_meta_box( $post, $box ) {
		global $wpdb;
		$post_id = $post->ID;
		$user_id = empty( $post_id ) ? get_current_user_id() : $post->post_author;

		// User listing package ID.
		$_user_package_id = get_post_meta( $post_id, '_user_package_id', true );
		$_user_package_id_display = $_user_package_id ? "#{$_user_package_id}" : 'N/A';
		$user_packages = astoundify_wpjmlp_get_user_packages( $user_id, array( 'job_listing' ), true );

		if ( $_user_package_id && array_key_exists( $_user_package_id, $user_packages ) ) {
			$user_package = $user_packages[ $_user_package_id ];
			if ( isset( $user_package->product_id ) ) {
				$_user_package_id_display .= ' - ' . get_the_title( $user_package->product_id );
			}
			$edit_url = add_query_arg( array(
				'page'       => 'wc_paid_listings_packages',
				'action'     => 'edit',
				'package_id' => $_user_package_id,
			), admin_url( 'users.php' ) );
			$_user_package_id_display = '<a target="_blank" href="' . esc_url( $edit_url ) . '">' . $_user_package_id_display . '</a>';
		}

		// Add package URL.
		$add_package_url = '';
		if ( current_user_can( 'manage_options' ) ) {
			$add_package_url = add_query_arg( array(
				'page'   => 'wc_paid_listings_packages',
				'action' => 'add',
			), admin_url( 'users.php' ) );
		}

		// Package/Product ID.
		$_package_id = get_post_meta( $post_id, '_package_id', true );
		$_package_id_display = $_package_id ? "#{$_package_id}" : 'N/A';
		$_package_title = get_the_title( $_package_id );
		if ( $_package_id && $_package_title ) {
			$_package_id_display = $_package_id_display . ' - ' . $_package_title;
		}
		$_package_edit_link = get_edit_post_link( $_package_id );
		if ( $_package_id && $_package_edit_link ) {
			$_package_id_display = '<a target="_blank" href="' . esc_url( $_package_edit_link ) . '">' . $_package_id_display . '</a>';
		}
?>
	<p><strong><?php esc_html_e( 'Listing Package:', 'wp-job-manager-listing-payments' ) ?></strong></p>

	<p id="astoundify_wpjmlp_user_package_id_select">
		<?php if ( $user_packages ) : ?>
			<select id="_user_package_id" name="_user_package_id" class="widefat" autocomplete="off">
				<option value="" <?php selected( '', $_user_package_id ); ?>><?php esc_html_e( '&mdash; Select &mdash;', 'wp-job-manager-listing-payments' ); ?></option>
				<?php foreach ( $user_packages as $k => $user_package ) :
					$option = "#{$user_package->id}";
					if ( isset( $user_package->product_id ) && $user_package->product_id ) {
						$option .= ' - ' . get_the_title( $user_package->product_id );
					}
					?>
					<option value="<?php echo esc_attr( $user_package->id ); ?>" <?php selected( $user_package->id, $_user_package_id ); ?>><?php echo esc_html( $option ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php endif; ?>
	</p>
	<?php if ( $add_package_url ) : ?>
		<p><a target="_blank" href="<?php echo esc_url( $add_package_url ); ?>"><?php esc_html_e( 'Create a user package', 'wp-job-manager-listing-payments' ); ?></a></p>
	<?php endif; ?>

	<p><strong><?php esc_attr_e( 'Package Product:', 'wp-job-manager-listing-payments' ) ?></strong></p>

	<p id="astoundify_wpjmlp_package_id_info">
		<?php echo wp_kses_post( $_package_id_display ); ?>
	</p>

	<?php wp_nonce_field( 'astoundify_wpjmlp_save_mb_nonce', 'astoundify-wpjmlp-paid-listing-data_nonce' ); ?>
<?php
	}


	/**
	 * Save Meta Box Data.
	 *
	 * @since 2.0.0
	 *
	 * @param int    $post_id Post ID.
	 * @param object $post    Post object WP_Post.
	 */
	public static function save_meta_box_data( $post_id, $post ) {
		global $wpdb;

		// Check if it's valid nonce.
		if ( ! isset( $_POST['astoundify-wpjmlp-paid-listing-data_nonce'] ) || ! wp_verify_nonce( $_POST['astoundify-wpjmlp-paid-listing-data_nonce'], 'astoundify_wpjmlp_save_mb_nonce' ) ) {
			return $post_id;
		}
		$request = stripslashes_deep( $_POST );
		// No Auto Save.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		// Check user caps.
		$post_type = get_post_type_object( $post->post_type );
		if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
			return $post_id;
		}

		// User Package ID.
		if ( isset( $request['_user_package_id'] ) ) {

			$new_package = $request['_user_package_id'];
			$old_package = get_post_meta( $post_id, '_user_package_id', true );

			// Get user packages.
			$user_packages = astoundify_wpjmlp_get_user_packages( $post->post_author, array( 'job_listing' ), true );

			// Updating.
			if ( $new_package !== $old_package ) {

				// Update Package Data.
				if ( $old_package && array_key_exists( $old_package, $user_packages ) ) {
					astoundify_wpjmlp_decrease_package_count( $post->post_author, $old_package );
				}
				if ( $new_package && array_key_exists( $new_package, $user_packages ) ) {
					astoundify_wpjmlp_increase_package_count( $post->post_author, $new_package );

					// Update listing based on package.
					update_post_meta( $post_id, '_job_duration', $user_packages[ $new_package ]->package_duration );
					update_post_meta( $post_id, '_featured', $user_packages[ $new_package ]->package_featured ? 1 : 0 );

					$expire_time = calculate_job_expiry( $post_id );
					if ( $expire_time ) {
						update_post_meta( $post_id, '_job_expires', $expire_time );
					}
					$product = wc_get_product( $user_packages[ $new_package ]->product_id );
					if ( $product && 'job_package_subscription' === $product->get_type() && 'listing' === $product->get_package_subscription_type() ) {
						update_post_meta( $post_id, '_job_expires', '' ); // Never expire automatically.
					}
				}
				// Update Meta.
				if ( $new_package ) {
					update_post_meta( $post_id, '_user_package_id', $new_package );
				} else {
					delete_post_meta( $post_id, '_user_package_id' );
				}
			}
			// Always Sync Listing Package ID with User Package Product ID.
			if ( $new_package && isset( $user_packages[ $new_package ]->product_id ) ) {
				update_post_meta( $post_id, '_package_id', intval( $user_packages[ $new_package ]->product_id ) );
			}
		} // End if().

	}

}
