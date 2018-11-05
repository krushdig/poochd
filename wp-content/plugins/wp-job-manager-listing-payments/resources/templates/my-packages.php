<?php
/**
 * My Packages.
 * Shows packages on the account page.
 *
 * @version 2.0.0
 * @since 2.0.0
 *
 * @var array  $packages User Packages.
 * @var string $type     Job Listing/Resume Listing. "job_listing" / "resume".
 *
 * @package Listing Payments
 * @category Template
 * @author Astoundify
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Title.
$title = __( 'My Packages', 'wp-job-manager-listing-payments' );

if ( 'job_listing' === $type ) {
	$title = __( 'My Listing Packages', 'wp-job-manager-listing-payments' );
} elseif ( 'resume' === $type ) {
	$title = __( 'My Resume Packages', 'wp-job-manager-listing-payments' );
}

$title = apply_filters( 'woocommerce_my_account_astoundify_wpjmlp_packages_title', $title, $type );
?>

<h2><?php echo esc_html( $title ); ?></h2>

<table class="shop_table my_account_job_packages my_account_astoundify_wpjmlp_packages">
	<thead>
		<tr>
			<th scope="col"><?php esc_html_e( 'Package Name', 'wp-job-manager-listing-payments' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Remaining', 'wp-job-manager-listing-payments' ); ?></th>
			<?php if ( 'job_listing' === $type ) : ?>
				<th scope="col"><?php esc_html_e( 'Listing Duration', 'wp-job-manager-listing-payments' ); ?></th>
			<?php endif; ?>
			<th scope="col"><?php esc_html_e( 'Featured?', 'wp-job-manager-listing-payments' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $packages as $package ) :
			$package = astoundify_wpjmlp_get_package( $package );
			?>
			<tr>
				<td><?php echo esc_html( $package->get_title() ); ?></td>
				<td><?php echo esc_html( $package->get_limit() ? absint( $package->get_limit() - $package->get_count() ) : __( 'Unlimited', 'wp-job-manager-listing-payments' ) ); ?></td>
				<?php if ( 'job_listing' === $type ) : ?>
					<td><?php
						// Translators: %d Package duration in days.
						echo esc_html( $package->get_duration() ? sprintf( _n( '%d day', '%d days', $package->get_duration(), 'wp-job-manager-listing-payments' ), $package->get_duration() ) : '-' );
					?></td>
				<?php endif; ?>
				<td><?php echo esc_html( $package->is_listing_featured() ? __( 'Yes', 'wp-job-manager-listing-payments' ) : __( 'No', 'wp-job-manager-listing-payments' ) ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
