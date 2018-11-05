<?php
/**
 * Plugin Name: Listing Payments for WP Job Manager
 * Plugin URI: https://astoundify.com/products/wp-job-manager-listing-payments/
 * Description: Sell listings via WooCommerce.
 * Version: 2.2.0
 * Author: Astoundify
 * Author URI: https://astoundify.com
 * Requires at least: 4.7.0
 * Tested up to: 4.9.0
 * WC requires at least: 3.0.0
 * WC tested up to: 3.2.0
 * Text Domain: wp-job-manager-listing-payments
 * Domain Path: resources/languages/
 *
 *    Copyright: 2017 Astoundify
 *    License: GNU General Public License v3.0
 *    License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 *    Copyright: 2015 Automattic
 *    License: GNU General Public License v3.0
 *    License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Listing Payments
 * @category Core
 * @author Astoundify
 */

// Do not access this file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activation PHP Notice
 *
 * @since 2.0.0
 */
function astoundify_wpjmlp_php_notice() {
	// translators: %1$s minimum PHP version, %2$s current PHP version.
	$notice = sprintf( __( 'Listing Payments for WP Job Manager requires at least PHP %1$s. You are running PHP %2$s. Please upgrade and try again.', 'wp-job-manager-listing-payments' ), '<code>5.4.0</code>', '<code>' . PHP_VERSION . '</code>' );
?>

<div class="notice notice-error">
	<p><?php echo wp_kses_post( $notice, array( 'code' ) ); ?></p>
</div>

<?php
}

// Check for PHP version..
if ( version_compare( PHP_VERSION, '5.4', '<' ) ) {
	add_action( 'admin_notices', 'astoundify_wpjmlp_php_notice' );

	return;
}

// Plugin can be loaded... define some constants.
define( 'ASTOUNDIFY_WPJMLP_VERSION', '2.2.0' );
define( 'ASTOUNDIFY_WPJMLP_FILE', __FILE__ );
define( 'ASTOUNDIFY_WPJMLP_PLUGIN', plugin_basename( __FILE__ ) );
define( 'ASTOUNDIFY_WPJMLP_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'ASTOUNDIFY_WPJMLP_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'ASTOUNDIFY_WPJMLP_TEMPLATE_PATH', trailingslashit( ASTOUNDIFY_WPJMLP_PATH . 'resources/templates' ) );

/**
 * Load Activation.
 *
 * @since 2.0.0
 */
require_once( ASTOUNDIFY_WPJMLP_PATH . 'bootstrap/activation.php' );

/**
 * Plugin Updater.
 *
 * @since 2.0.0
 */
function astoundify_wpjmlp_updater() {
	require_once( ASTOUNDIFY_WPJMLP_PATH . 'vendor/astoundify/plugin-updater/astoundify-pluginupdater.php' );

	new Astoundify_PluginUpdater( __FILE__ );

	if ( defined( 'JOB_MANAGER_VERSION' ) ) {
		new Astoundify_PluginUpdater_Integration_WPJobManager( __FILE__ );
	}
}
add_action( 'admin_init', 'astoundify_wpjmlp_updater', 9 );

/**
 * Load auto loader.
 *
 * @since 2.0.0
 */
require_once( ASTOUNDIFY_WPJMLP_PATH . 'bootstrap/autoload.php' );

/**
 * Start the application.
 *
 * @since 2.0.0
 */
require_once( ASTOUNDIFY_WPJMLP_PATH . 'bootstrap/app.php' );
