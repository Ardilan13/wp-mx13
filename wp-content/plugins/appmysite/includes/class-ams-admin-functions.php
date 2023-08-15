<?php
 /**
 * Plugin info.
 * @package    AppMySite
 * @author     AppMySite <support@appmysite.com>
 * @copyright  Copyright (c) 2023 - 2024, AppMySite
 * @link       https://appmysite.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
 
if ( !class_exists( 'AMS_Admin_Functions' ) ) {
		
	final class AMS_Admin_Functions{
		
		/**
		 * AMS_Admin_Scripts Constructor.
		 **/
		
		function __construct() {
			
			$this->ams_plugin_deactivation_survey();

			add_action( 'admin_menu', array( &$this, 'ams_admin_menu' ) );
			
		}
		
		function ams_plugin_deactivation_survey(){
			
			require_once untrailingslashit( dirname( AMS_PLUGIN_DIR )) . '/includes/ams-plugin-deactivation-survey.php';
				
			add_action( 'wp_ajax_ams_deactivation_form_submit', 'ams_deactivation_form_submit' );
			
		}

		// adds ams menu item to wordpress admin dashboard
		function ams_admin_menu() {
			
			add_menu_page( __( 'AppMySite Dashboard' ),
			__( 'AppMySite' ),
			'manage_options',
			'ams-home',
			array( &$this, 'ams_admin_menu_page' ),'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCI+CiAgPGRlZnM+CiAgICA8Y2xpcFBhdGggaWQ9ImNsaXAtcGF0aCI+CiAgICAgIDxyZWN0IGlkPSJSZWN0YW5nbGVfMjQ3NDEiIGRhdGEtbmFtZT0iUmVjdGFuZ2xlIDI0NzQxIiB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC0xODM3IC0xNzMwNCkiIGZpbGw9IiNmZmYiLz4KICAgIDwvY2xpcFBhdGg+CiAgPC9kZWZzPgogIDxnIGlkPSJNYXNrX0dyb3VwXzI1MDMwIiBkYXRhLW5hbWU9Ik1hc2sgR3JvdXAgMjUwMzAiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE4MzcgMTczMDQpIiBjbGlwLXBhdGg9InVybCgjY2xpcC1wYXRoKSI+CiAgICA8cGF0aCBpZD0iVW5pb25fMjUzNyIgZGF0YS1uYW1lPSJVbmlvbiAyNTM3IiBkPSJNLjY2MiwxNC40NTNhLjY2My42NjMsMCwwLDEtLjYxMS0uOTE4TDUuNDIuNjI4QTEuMDE4LDEuMDE4LDAsMCwxLDYuMzU3LDBoMy43ODNhLjY1My42NTMsMCwwLDEsLjU4Ny4zNjRjLjAwNy4wMTQuMDEzLjAyOS4wMi4wNDRhLjY2NS42NjUsMCwwLDEsLjAyMy4wNjhsNC4wMzgsOS43MTEsMS4xLDIuNjI3YTEuMTgsMS4xOCwwLDAsMS0xLjA4NiwxLjYzNUgxMC44M2ExLjUwNSwxLjUwNSwwLDAsMS0xLjIzLS42MzdMNy42MTYsMTEuMDA3YS41Mi41MiwwLDAsMSwuNDIzLS44MkgxMC4yTDguNTQzLDYuMjA2bC0zLjIsNy43MDhhLjg3MS44NzEsMCwwLDEtLjguNTM4WiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTE4MzQuOTk4IC0xNzMwMS4yMjcpIiBmaWxsPSIjZmZmIiBzdHJva2U9InJnYmEoMCwwLDAsMCkiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgc3Ryb2tlLXdpZHRoPSIxIi8+CiAgPC9nPgo8L3N2Zz4='
			);
			
		}

		function ams_admin_menu_page() {
			// Load home page
			require_once untrailingslashit( dirname( AMS_PLUGIN_DIR )) . '/includes/views/ams-home.php'; 
		}
				
			
	}

}

