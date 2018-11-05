<?php
/**
 * Payment Package Field.
 *
 * @since 2.0.0
 *
 * @var string $key   The value is "package".
 * @var array  $field Field data.
 */

$packages = $field['packages'];
$current_package_id = $field['value'];
?>

<ul class="job-packages-field">

	<?php foreach ( $packages as $package ) : ?>

		<li class="job-package-field">

			<p>
				<input class="job-package-field-input" type="radio" <?php checked( $package->get_id(), $current_package_id ); ?> name="payment-package" value="<?php echo esc_attr( $package->get_id() ); ?>" id="user-package-<?php echo esc_attr( $package->get_id() ); ?>" /> 
				<label for="user-package-<?php echo esc_attr( $package->get_id() ); ?>"><?php echo esc_html( $package->get_title() ); ?> </label> 
				<?php if ( intval( $package->get_id() ) === intval( $current_package_id ) ) : ?> 
					<?php esc_html_e( '(Current)', 'wp-job-manager-listing-payments' ); ?>
				<?php endif; ?>
			</p>

			<p>
				<?php
				// Translators: %s number of listings remaining.
				echo esc_html( sprintf( _n( '%s listing remaining in this package.', '%s listings remaining in this package.', $package->get_remaining_count(), 'wp-job-manager-listing-payments' ), $package->get_limit() ? $package->get_remaining_count() : __( 'Unlimited', 'wp-job-manager-listing-payments' ) ) );

				echo '&nbsp;';

				// Expiry date.
				if ( $package->get_duration() ) :
					// Translators: %s date the listing will expire if switched.
					$duration = sprintf( __( 'Listing will expire on %s', 'wp-job-manager-listing-payments' ), astoundify_wpjmlp_get_expiry_date( $package->get_duration() ) );
				else :
					$duration = __( 'Listing will never expire','wp-job-manager-listing-payments' );
				endif;

				// Featured info.
				if ( $package->is_listing_featured() ) :
					$featured = __( 'and will be featured.', 'wp-job-manager-listing-payments' );
				else :
					$featured = __( 'and will not be featured.', 'wp-job-manager-listing-payments' );
				endif;

				// Translators: %1$s: duration string %2%s featured string.
				echo esc_html( sprintf( __( '%1$s %2$s', 'wp-job-manager-listing-payments' ), $duration, $featured ) );
			?>
			</p>

		</li>

	<?php endforeach; ?>

</ul><!-- .job-packages-field -->