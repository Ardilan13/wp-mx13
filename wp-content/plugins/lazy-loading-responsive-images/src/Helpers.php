<?php
/**
 * Helper methods.
 *
 * @package FlorianBrinkmann\LazyLoadResponsiveImages
 */

namespace FlorianBrinkmann\LazyLoadResponsiveImages;

use FlorianBrinkmann\LazyLoadResponsiveImages\Settings as Settings;

/**
 * Class Helpers
 *
 * Class with helper methods.
 *
 * @package FlorianBrinkmann\LazyLoadResponsiveImages
 */
class Helpers {

	/**
	 * Hint if the plugin is disabled for this post.
	 *
	 * @var null|int
	 */
	private $disabled_for_current_post = null;

	/**
	 * Checks if this is a request at the backend.
	 *
	 * @return bool true if is admin request, otherwise false.
	 */
	public function is_admin_request() {
		/*
		 * Get current URL. From wp_admin_canonical_url().
		 *
		 * @link https://stackoverflow.com/a/29976742/7774451
		 */
		$current_url = set_url_scheme(
			sprintf(
				'http://%s%s',
				$_SERVER['HTTP_HOST'],
				$_SERVER['REQUEST_URI']
			)
		);

		/*
		 * Get admin URL and referrer.
		 *
		 * @link https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/pluggable.php#L1076
		 */
		$admin_url = strtolower( admin_url() );
		$referrer  = strtolower( wp_get_referer() );

		// Check if this is a admin request. If true, it
		// could also be a AJAX request.
		if ( 0 === strpos( $current_url, $admin_url ) ) {
			// Check if the user comes from a admin page.
			if ( 0 === strpos( $referrer, $admin_url ) ) {
				return true;
			} else {
				/*
				 * Check for AJAX requests.
				 *
				 * @link https://gist.github.com/zitrusblau/58124d4b2c56d06b070573a99f33b9ed#file-lazy-load-responsive-images-php-L193
				 */
				if ( function_exists( 'wp_doing_ajax' ) ) {
					return ! wp_doing_ajax();
				} else {
					return ! ( defined( 'DOING_AJAX' ) && DOING_AJAX );
				}
			}
		} else {
			if ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) {
				return false;
			}
			return ( isset( $_REQUEST['context'] ) && 'edit' === $_REQUEST['context'] );
		}
	}

	/**
	 * Checks if we are on an AMP page generated from the Automattic plugin.
	 *
	 * @return bool true if is amp page, false otherwise.
	 */
	public function is_amp_page() {
		// Check if Automattic’s AMP plugin is active and we are on an AMP endpoint.
		if ( function_exists( 'is_amp_endpoint' ) && true === is_amp_endpoint() ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if plugin is disabled for current post.
	 *
	 * @return bool true if disabled, false otherwise.
	 */
	public function is_disabled_for_post() {
		// Check if the plugin is disabled.
		if ( null === $this->disabled_for_current_post ) {
			$this->disabled_for_current_post = absint( get_post_meta( get_the_ID(), 'lazy_load_responsive_images_disabled', true ) );
		}

		/**
		 * Filter for disabling Lazy Loader on specific pages/posts/….
		 *
		 * @param boolean True if lazy loader should be disabled, false if not.
		 */
		if ( 1 === $this->disabled_for_current_post || true === apply_filters( 'lazy_loader_disabled', false ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the displayed content is something that the plugin should process.
	 * 
	 * @return bool
	 */
	public function is_post_to_process() {
		if ( $this->is_disabled_for_post() ) {
			return false;
		}

		// Check if we are on a feed page.
		if ( is_feed() ) {
			return false;
		}

		// Check if this content is embedded.
		if ( is_embed() ) {
			return false;
		}

		// Check if this is a request in the backend.
		if ( $this->is_admin_request() ) {
			return false;
		}

		// Check for AMP page.
		if ( $this->is_amp_page() ) {
			return false;
		}

		// Check for Oxygen Builder mode.
		if ( defined( 'SHOW_CT_BUILDER' ) ) {
			return false;
		}

		// Check for TranslatePress editor.
		if ( isset( $_REQUEST['trp-edit-translation'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Sanitize comma separated list of class names.
	 *
	 * @param string $class_names Comma separated list of HTML class names.
	 *
	 * @return string Sanitized comma separated list.
	 */
	public function sanitize_class_name_list( $class_names ) {
		// Get array of the class names.
		$class_names_array = explode( ',', $class_names );

		if ( false === $class_names_array ) {
			return '';
		}

		// Loop through the class names.
		foreach ( $class_names_array as $i => $class_name ) {
			// Save the sanitized class name.
			$class_names_array[ $i ] = sanitize_html_class( $class_name );
		}

		// Implode the class names.
		$class_names = implode( ',', $class_names_array );

		return $class_names;
	}

	/**
	 * Sanitize list of filter names.
	 *
	 * @param string $filters One or more WordPress filters, one per line.
	 *
	 * @return string Sanitized list.
	 */
	public function sanitize_filter_name_list( $filters ) {
		// Get array of the filter names.
		$filters_array = explode( "\n", $filters );

		if ( false === $filters_array ) {
			return '';
		}

		// Loop through the filter names.
		foreach ( $filters_array as $i => $filter ) {
			$function_name_regex = '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/';
			
			$filters_array[$i] = trim( $filters_array[$i] );
			
			// Check if the filter is a valid PHP function name.
			if ( preg_match( $function_name_regex, $filters_array[$i] ) !== 1 ) {
				unset( $filters_array[$i] );
				continue;
			}
		}

		// Implode the filter names.
		$filters = implode( "\n", $filters_array );

		return $filters;
	}

	/**
	 * Sanitize checkbox.
	 *
	 * @link https://github.com/WPTRT/code-examples/blob/master/customizer/sanitization-callbacks.php
	 *
	 * @param bool $checked Whether the checkbox is checked.
	 *
	 * @return bool Whether the checkbox is checked.
	 */
	public function sanitize_checkbox( $checked ) {
		return ( ( isset( $checked ) && true == $checked ) ? true : false );
	}

	/**
	 * Sanitize textarea input.
	 *
	 * @param bool $checked Whether the checkbox is checked.
	 *
	 * @return bool Whether the checkbox is checked.
	 */
	public function sanitize_textarea( $value ) {
		return strip_tags( $value );
	}

	/**
	 * Sanitize hex color value.
	 *
	 * @param string $value The input from the color input.
	 *
	 * @return string The hex value.
	 */
	public function sanitize_hex_color( $value ) {
		// Sanitize the input.
		$sanitized = sanitize_hex_color( $value );
		if ( null !== $sanitized && '' !== $sanitized ) {
			return $value;
		} else {
			$settings = new Settings();
			return $settings->get_loading_spinner_color_default();
		} // End if().
	}

	/**
	 * Enhanced variation of \DOMDocument->saveHTML().
	 *
	 * Fix for cyrillic from https://stackoverflow.com/a/47454019/7774451.
	 * Replacement of doctype, html, and body from archon810\SmartDOMDocument.
	 *
	 * @param \DOMDocument $dom DOMDocument object of the dom.
	 * @param Masterminds\HTML5 $html5 HTML5 object.
	 *
	 * @return string DOM or empty string.
	 */
	public function save_html( \DOMDocument $dom, $html5 ) {
		$xpath      = new \DOMXPath( $dom );
		$first_item = $xpath->query( '/' )->item( 0 );

		return preg_replace(
			array(
				'/^\<\!DOCTYPE html>.*?<html>/si',
				'/<\/html>[\n\r]?$/si',
			),
			'',
			$html5->saveHTML( $first_item )
		);
	}
}
