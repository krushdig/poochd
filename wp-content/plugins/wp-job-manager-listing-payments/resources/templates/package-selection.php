<?php
/**
 * Package Selection.
 * Shows packages selection to purchase.
 *
 * @version 2.0.0
 * @since 2.0.0
 *
 * @var array $packages      WC Products.
 * @var array $user_packages User Packages.
 *
 * @package Listing Payments
 * @category Template
 * @author Astoundify
 */
?>

<?php if ( $packages || $user_packages ) :
	$get_package = isset( $_GET['selected_package'] ) ? intval( $_GET['selected_package'] ) : 0;
	$checked = 1;
	?>
	<ul class="job_packages">
		<?php if ( $user_packages ) : ?>
			<?php $checked = $get_package ? 0 : 1; // Get package do not target user package. ?>
			<li class="package-section"><?php esc_html_e( 'Your Packages:', 'wp-job-manager-listing-payments' ); ?></li>
			<?php foreach ( $user_packages as $key => $package ) :
				$package = astoundify_wpjmlp_get_package( $package );
				?>
				<li class="user-job-package">
					<label for="user-package-<?php echo esc_attr( $package->get_id() ); ?>"><?php echo esc_html( $package->get_title() ); ?></label><br/>
					<span class="radio_border"><input type="radio" <?php checked( $checked, 1 ); ?> name="job_package" value="user-<?php echo esc_attr( $key ); ?>" id="user-package-<?php echo esc_attr( $package->get_id() ); ?>" />
					
					<b class="radio-icon" for="f-option"></b>
					<div class="check"><div class="inside"></div></div>
					
					<?php
						if ( $package->get_limit() ) {
							printf( _n( '%s job posted out of %d', '%s jobs posted out of %d', $package->get_count(), 'wp-job-manager-listing-payments' ), $package->get_count(), $package->get_limit() );
						} else {
							printf( _n( '%s job posted', '%s jobs posted', $package->get_count(), 'wp-job-manager-listing-payments' ), $package->get_count() );
						}

						if ( $package->get_duration() ) {
							printf(  ', ' . _n( 'listed for %s day', 'listed for %s days', $package->get_duration(), 'wp-job-manager-listing-payments' ), $package->get_duration() );
						}

						$checked = 0;
					?></span> 
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php if ( $packages ) : ?>
			<?php $checked = $get_package ? $get_package : $checked;?>
			<li class="package-section"><?php _e( 'Purchase Package:', 'wp-job-manager-listing-payments' ); ?></li>
			<?php foreach ( $packages as $key => $package ) :
				$product = wc_get_product( $package );
				if ( ! $product->is_type( array( 'job_package', 'job_package_subscription' ) ) || ! $product->is_purchasable() ) {
					continue;
				}
				$pid = $get_package ? $product->get_id() : 1;
				$post = get_post( $product->get_id() ); 
				$excerpt = $post->post_excerpt;
				?>
				<li class="job-package">
					<label for="package-<?php echo $product->get_id(); ?>"><?php echo $product->get_title(); ?></label><br/> 
					<span class="radio_border"><input type="radio" <?php checked( $checked, $pid ); ?> name="job_package" value="<?php echo $product->get_id(); ?>" id="package-<?php echo $product->get_id(); ?>" />
					
					<b class="radio-icon" for="f-option"></b>
					<div class="check"><div class="inside"></div></div>
					
					<?php if ( ! empty( $excerpt ) ) : ?>
						<?php echo apply_filters( 'woocommerce_short_description', $excerpt ) ?>
					<?php else :
						printf( _n( '%s for %s job', '%s for %s jobs', $product->get_limit(), 'wp-job-manager-listing-payments' ) . ' ', $product->get_price_html() ? $product->get_price_html() : __( 'Free', 'wp-job-manager-listing-payments' ), $product->get_limit() ? $product->get_limit() : __( 'unlimited', 'wp-job-manager-listing-payments' ) );
						echo $product->get_duration() ? sprintf( _n( 'listed for %s day', 'listed for %s days', $product->get_duration(), 'wp-job-manager-listing-payments' ), $product->get_duration() ) : '';
					endif; ?>
					<?php if( !$get_package ){
						$checked = 0;
					}?></span>
				</li>

			<?php endforeach; ?>
		<?php endif; ?>
	</ul>
<?php else : ?>

	<p><?php esc_html_e( 'No packages found', 'wp-job-manager-listing-payments' ); ?></p>

<?php endif; ?>
