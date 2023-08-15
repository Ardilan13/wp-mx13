<?php
/**
 * Plugin Name: AppMySite
 * Plugin URI: https://www.appmysite.com
 * Description: This plugin enables WordPress & WooCommerce users to sync their websites with native iOS and Android apps, created on <a href="https://www.appmysite.com/"><strong>www.appmysite.com</strong></a>
 * Version: 3.9.1
 * Author: AppMySite
 * Text Domain: appmysite
 * Author URI: https://appmysite.com
 * Tested up to: 6.1.1
 * WC tested up to: 7.3.0
 * WC requires at least: 3.8.0
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 **/

	// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die( 'No script kiddies please!' );
}

if ( ! defined( 'AMS_PLUGIN_DIR' ) ) {
	define( 'AMS_PLUGIN_DIR', __FILE__ );
}

	/******************************************************************************
	 * Show warning to all where WordPress version is below minimum requirement.
	 */

	global $wp_version;
	if ( $wp_version <= 4.9 ) {
		function wo_incompatibility_with_wp_version() {
			?>
				<div class="notice notice-error">
					<p><?php esc_html_e( 'AppMySite requires that WordPress 4.9 or greater be used. Update to the latest WordPress version.', 'appmysite' ); ?>
						<a href="<?php echo esc_url( admin_url( 'update-core.php' ) ); ?>"><?php esc_html_e( 'Update Now', 'appmysite' ); ?></a></p>
				</div>
				<?php
		}

		add_action( 'admin_notices', 'wo_incompatibility_with_wp_version' );
	}

	// Include the main AMS_Rest_Routes class.
	if ( ! class_exists( 'AMS_Rest_Routes', false ) ) {
		include_once dirname( AMS_PLUGIN_DIR ) . '/includes/class-ams-rest-routes.php';
		new AMS_Rest_Routes();
	}

	// Include the main AMS_Register_Rest_Fields class.
	if ( ! class_exists( 'AMS_Register_Rest_Fields', false ) ) {
		include_once dirname( AMS_PLUGIN_DIR ) . '/includes/class-ams-rest-register-fields.php';
		new AMS_Register_Rest_Fields();
	}
	
	// Include the main AMS_Filters class.
	if ( ! class_exists( 'AMS_Filters', false ) ) {
		include_once dirname( AMS_PLUGIN_DIR ) . '/includes/class-ams-filters.php';
		new AMS_Filters();
	}
			 			

	// Include the main AMS_Admin_Functions class.
	if ( ! class_exists( 'AMS_Admin_Functions', false ) ) {
		include_once dirname( AMS_PLUGIN_DIR ) . '/includes/class-ams-admin-functions.php';
		new AMS_Admin_Functions();
	}
	
	
	// Include the main AMS_Admin_Scripts class.
	if ( ! class_exists( 'AMS_Admin_Scripts', false ) ) {
		include_once dirname( AMS_PLUGIN_DIR ) . '/includes/class-ams-admin-scripts.php';
		new AMS_Admin_Scripts();
	}
			


