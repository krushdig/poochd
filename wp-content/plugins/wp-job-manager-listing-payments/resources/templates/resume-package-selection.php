<?php
/**
 * Resume Package Selection.
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
	<ul class="resume_packages">
		<?php if ( $user_packages ) : ?>
			<?php $checked = $get_package ? 0 : 1; // get package do not target user package ?>
			<li class="package-section"><?php esc_html_e( 'Your Packages:', 'wp-job-manager-listing-payments' ); ?></li>
			<?php foreach ( $user_packages as $key => $package ) :
				$package = astoundify_wpjmlp_get_package( $package );
				?>
				<li class="user-resume-package">
					<input type="radio" <?php checked( $checked, 1 ); ?> name="resume_package" value="user-<?php echo $key; ?>" id="user-package-<?php echo $package->get_id(); ?>" />
					<label for="user-package-<?php echo $package->get_id(); ?>"><?php echo $package->get_title(); ?></label><br/>
					<?php
						if ( $package->get_limit() ) {
							printf( _n( '%s resume posted out of %d', '%s resumes posted out of %s', $package->get_count(), 'wp-job-manager-listing-payments' ), $package->get_count(), $package->get_limit() );
						} else {
							printf( _n( '%s resume posted', '%s resumes posted', $package->get_count(), 'wp-job-manager-listing-payments' ), $package->get_count() );
						}

						if ( $package->get_duration() ) {
							printf( ' ' . _n( 'listed for %s day', 'listed for %s days', $package->get_duration(), 'wp-job-manager-listing-payments' ), $package->get_duration() );
						}

						$checked = 0;
					?>
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php if ( $packages ) : ?>
			<?php $checked = $get_package ? $get_package : $checked;?>
			<li class="package-section"><?php _e( 'Purchase Package:', 'wp-job-manager-listing-payments' ); ?></li>
			<?php foreach ( $packages as $key => $package ) :
				$product = wc_get_product( $package );
				if ( ! $product->is_type( array( 'resume_package', 'resume_package_subscription' ) ) ) {
					continue;
				}
				$pid = $get_package ? $product->get_id() : 1;
				?>
				<li class="resume-package">
					<input type="radio" <?php checked( $checked, $pid ); ?> name="resume_package" value="<?php echo $product->get_id(); ?>" id="package-<?php echo $product->get_id(); ?>" />
					<label for="package-<?php echo $product->get_id(); ?>"><?php echo $product->get_title(); ?></label><br/>
					<?php
						printf( _n( '%s to post %d resume', '%s to post %s resumes', $product->get_limit(), 'wp-job-manager-listing-payments' ) . ' ', $product->get_price_html() ? $product->get_price_html() : __( 'Free', 'wp-job-manager-listing-payments' ), $product->get_limit() ? $product->get_limit() : __( 'unlimited', 'wp-job-manager-listing-payments' ) );

						if ( $product->get_duration() ) {
							printf( ' ' . _n( 'listed for %s day', 'listed for %s days', $product->get_duration(), 'wp-job-manager-listing-payments' ), $product->get_duration() );
						}

						if( !$get_package ){
							$checked = 0;
						}
					?>
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
	</ul>
<?php else : ?>

	<p><?php _e( 'No packages found', 'wp-job-manager-listing-payments' ); ?></p>

<?php endif; ?>
