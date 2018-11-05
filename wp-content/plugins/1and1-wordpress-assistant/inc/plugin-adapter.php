<?php
/** Do not allow direct access! */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Forbidden' );
}

/**
 * Class One_And_One_Plugin_Adapter
 * Enhances the Assistant Interface according to which plugins have been installed
 */
class One_And_One_Plugin_Adapter {

	/**
	 * WooCommerce Plugin
	 * - adds post-setup buttons for WooCommerce
	 */
	public function adapt_woocommerce() {

        /** Add custom HTML for WooCommerce to the WordPress Assistant */
        add_action( 'oneandone_post_setup_custom', function() {

	        printf(
		        '<a href="%1$s" class="button button-primary" title="%2$s" target="_parent">%2$s</a><p>%3$s</p>',
		        admin_url( 'index.php?page=wc-setup' ),
	            __( 'setup_assistant_woocommerce_configuration', '1and1-wordpress-wizard' ),
	            __( 'setup_assistant_woocommcerce_installation_ready', '1and1-wordpress-wizard' )
			);
		} );
	}
}
