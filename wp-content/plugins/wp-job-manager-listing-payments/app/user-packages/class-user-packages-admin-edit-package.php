<?php
/**
 * Edit User Package.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category User Packages
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * Edit User Package.
 *
 * @since 2.0.0
 */
class User_Packages_Admin_Edit_Package {

	/**
	 * Package ID.
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	private $package_id;

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->package_id = isset( $_REQUEST['package_id'] ) ? absint( $_REQUEST['package_id'] ) : 0;

		if ( ! empty( $_POST['save_package'] ) && ! empty( $_POST['wc_paid_listings_packages_nonce'] ) && wp_verify_nonce( $_POST['wc_paid_listings_packages_nonce'], 'save' ) ) {
			$this->save();
		}
	}

	/**
	 * Output the form
	 *
	 * @since 2.0.0
	 */
	public function form() {
		global $wpdb;

		$user_string = '';
		$user_id     = '';
		$package     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wcpl_user_packages WHERE id = %d;", $this->package_id ) );

		if ( $this->package_id && $package ) {
			$package_type     = $package->package_type;
			$package_limit    = $package->package_limit;
			$package_count    = $package->package_count;
			$package_duration = $package->package_duration;
			$package_featured = $package->package_featured;
			$user_id          = $package->user_id ? $package->user_id : '';
			$product_id       = $package->product_id;
			$order_id         = $package->order_id;

			if ( ! empty( $user_id ) ) {
				$user        = get_user_by( 'id', $user_id );
				$user_string = esc_html( $user->display_name ) . ' (#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email ) . ')';
			}
		} else {
			$package_type     = '';
			$package_limit    = '';
			$package_count    = '';
			$package_duration = '';
			$package_featured = '';
			$product_id       = '';
			$order_id         = '';
		}
		?>
		<table class="form-table">
			<tr>
				<th>
					<label for="package_type"><?php esc_html_e( 'Package Type', 'wp-job-manager-listing-payments' ); ?></label>
				</th>
				<td>
					<select name="package_type" id="package_type" style="padding:0;">
						<option value="job_listing" <?php selected( $package_type, 'job_listing' ); ?>><?php esc_html_e( 'Listing Payment Package', 'wp-job-manager-listing-payments' ); ?></option>
						<?php if ( class_exists( 'WP_Resume_Manager' ) ) { ?>
							<option value="resume" <?php selected( $package_type, 'resume' ); ?>><?php esc_html_e( 'Resume Payment Package', 'wp-job-manager-listing-payments' ); ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<label for="package_limit"><?php esc_html_e( 'Listing Limit', 'wp-job-manager-listing-payments' ); ?></label>
					<img class="help_tip tips" data-tip="<?php esc_attr_e( 'How many listings should this package allow the user to post?', 'wp-job-manager-listing-payments' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16">
				</th>
				<td>
					<input type="number" step="1" name="package_limit" id="package_limit" class="input-text regular-text" placeholder="<?php esc_attr_e( 'Unlimited', 'wp-job-manager-listing-payments' ); ?>" value="<?php echo esc_attr( $package_limit ); ?>" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="package_count"><?php esc_html_e( 'Listing Count', 'wp-job-manager-listing-payments' ); ?></label>
					<img class="help_tip tips" data-tip="<?php esc_attr_e( 'How many listings has the user already posted with this package?', 'wp-job-manager-listing-payments' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16">
				</th>
				<td>
					<input type="number" step="1" name="package_count" id="package_count" value="<?php echo esc_attr( $package_count ); ?>" class="input-text regular-text" placeholder="0" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="package_duration"><?php esc_html_e( 'Listing Duration', 'wp-job-manager-listing-payments' ); ?></label>
					<img class="help_tip tips" data-tip="<?php esc_attr_e( 'How many days should listings posted with this package be active?', 'wp-job-manager-listing-payments' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16">
				</th>
				<td>
					<input type="number" step="1" name="package_duration" id="package_duration" value="<?php echo esc_attr( $package_duration ); ?>" class="input-text regular-text" placeholder="<?php esc_attr_e( 'Default', 'wp-job-manager-listing-payments' ); ?>" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="package_featured"><?php esc_html_e( 'Feature Listings?', 'wp-job-manager-listing-payments' ); ?></label>
				</th>
				<td>
					<input type="checkbox" name="package_featured" id="package_featured" class="input-text" <?php checked( $package_featured, '1' ); ?> />
				</td>
			</tr>
			<tr>
				<th>
					<label for="user_id"><?php esc_html_e( 'User', 'wp-job-manager-listing-payments' ); ?></label>
				</th>
				<td>
					<select class="wc-customer-search" id="user_id" name="user_id" data-placeholder="<?php esc_attr_e( 'Guest', 'wp-job-manager-listing-payments' ); ?>" data-allow_clear="true">
						<option value="<?php echo esc_attr( $user_id ); ?>" selected="selected"><?php echo esc_html( htmlspecialchars( $user_string ) ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<label for="product_id"><?php esc_html_e( 'Product', 'wp-job-manager-listing-payments' ); ?></label>
					<img class="help_tip tips" data-tip="<?php esc_attr_e( 'Optionally link this package to a product.', 'wp-job-manager-listing-payments' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16">
				</th>
				<td>
					<select name="product_id" class="wc-enhanced-select" data-allow_clear="true" data-placeholder="<?php esc_attr_e( 'Choose a product&hellip;', 'wp-job-manager-listing-payments' ) ?>" style="width:25em">
						<?php
							echo '<option value=""></option>';

							$args = array(
								'limit'     => -1,
								'status'    => array( 'publish' ),
								'order'     => 'ASC',
								'orderby'   => 'title',
								'type'      => array( 'job_package', 'job_package_subscription', 'resume_package', 'resume_package_subscription' ),
							);
							$products = wc_get_products( $args );

							if ( $products ) {
								foreach ( $products as $product ) {
									echo '<option value="' . absint( $product->get_id() ) . '" ' . selected( $product_id, $product->get_id() ) . '>' . esc_html( $product->get_title() ) . '</option>';
								}
							}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<label for="order_id"><?php esc_html_e( 'Order ID', 'wp-job-manager-listing-payments' ); ?></label>
					<img class="help_tip tips" data-tip="<?php esc_attr_e( 'Optionally link this package to an order.', 'wp-job-manager-listing-payments' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16">
				</th>
				<td>
					<input type="number" step="1" name="order_id" id="order_id" value="<?php echo esc_attr( $order_id ); ?>" class="input-text regular-text" placeholder="<?php esc_attr_e( 'N/A', 'wp-job-manager-listing-payments' ); ?>" />
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="hidden" name="package_id" value="<?php echo esc_attr( $this->package_id ); ?>" />
			<input type="submit" class="button button-primary" name="save_package" value="<?php esc_attr_e( 'Save Package', 'wp-job-manager-listing-payments' ); ?>" />
		</p>
		<?php
	}

	/**
	 * Save the new key
	 *
	 * @since 2.0.0
	 */
	public function save() {
		global $wpdb;

		try {
			$package_type     = wc_clean( $_POST['package_type'] );
			$package_limit    = absint( $_POST['package_limit'] );
			$package_count    = absint( $_POST['package_count'] );
			$package_duration = absint( $_POST['package_duration'] );
			$package_featured = isset( $_POST['package_featured'] ) ? 1 : 0;
			$user_id          = absint( $_POST['user_id'] );
			$product_id       = absint( $_POST['product_id'] );
			$order_id         = absint( $_POST['order_id'] );

			if ( $this->package_id ) {
				$wpdb->update(
					"{$wpdb->prefix}wcpl_user_packages",
					array(
						'user_id'          => $user_id,
						'product_id'       => $product_id,
						'order_id'         => $order_id,
						'package_count'    => $package_count,
						'package_duration' => $package_duration ? $package_duration : '',
						'package_limit'    => $package_limit,
						'package_featured' => $package_featured,
						'package_type'     => $package_type,
					),
					array(
						'id' => $this->package_id,
					)
				);

				do_action( 'wcpl_admin_updated_package', $this->package_id );
			} else {
				$wpdb->insert(
					"{$wpdb->prefix}wcpl_user_packages",
					array(
						'user_id'          => $user_id,
						'product_id'       => $product_id,
						'order_id'         => $order_id,
						'package_count'    => $package_count,
						'package_duration' => $package_duration ? $package_duration : '',
						'package_limit'    => $package_limit,
						'package_featured' => $package_featured,
						'package_type'     => $package_type,
					)
				);

				$this->package_id = $wpdb->insert_id;

				do_action( 'wcpl_admin_created_package', $this->package_id );
			} // End if().

			echo sprintf( '<div class="updated"><p>%s</p></div>', esc_html__( 'Package successfully saved', 'wp-job-manager-listing-payments' ) );

		} catch ( Exception $e ) {
			echo sprintf( '<div class="error"><p>%s</p></div>', esc_html( $e->getMessage() ) );
		} // End try().
	}
}
