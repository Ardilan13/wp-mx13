<?php

/**
 * Plugin Name: FullPage for Elementor
 * Plugin URI: https://www.meceware.com/docs/fullpage-for-elementor/
 * Author: Mehmet Celik, Álvaro Trigo
 * Author URI: https://www.meceware.com/
 * Version: 2.0.10
 * Description: Create beautiful scrolling fullscreen web sites with Elementor and WordPress, fast and simple. Elementor addon of FullPage JS implementation.
 * Text Domain: mcw-fullpage-elementor
 *
 * @package mcw-fullpage-elementor
 *
 * Elementor tested up to: 3.13.4
 * Elementor Pro tested up to: 3.13.2
**/

/* Copyright 2019-2023 Mehmet Celik */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'McwFullPageElementorGlobals' ) ) {

  final class McwFullPageElementorGlobals {
    // Plugin version
    private static $version = '2.0.10';
    // FullPage JS version
    private static $fullPageVersion = '4.0.20';
    // Tag
    private static $tag = 'mcw-fullpage-elementor';
    // Plugin name
    private static $pluginName = 'FullPage for Elementor';

    private function __construct() { }

    public function __clone() {
      // Cloning instances of the class is forbidden
      _doing_it_wrong( __FUNCTION__, esc_html__( 'NOT ALLOWED', 'mcw-fullpage-elementor' ), self::$version );
    }

    public function __wakeup() {
      // Unserializing instances of the class is forbidden
      _doing_it_wrong( __FUNCTION__, esc_html__( 'NOT ALLOWED', 'mcw-fullpage-elementor' ), self::$version );
    }

    public static function Version() {
      return self::$version;
    }

    public static function Tag() {
      return self::$tag;
    }

    public static function Name() {
      return esc_html__( 'FullPage for Elementor', 'mcw-fullpage-elementor' );
    }

    public static function Url() {
      return trailingslashit( plugin_dir_url( __FILE__ ) );
    }

    public static function Dir() {
      return trailingslashit( plugin_dir_path( __FILE__ ) );
    }

    public static function File() {
      return __FILE__;
    }

    public static function FullPageVersion() {
      return self::$fullPageVersion;
    }

    public static function FullPageScriptName() {
      return self::$tag . '-fullpage';
    }

    public static function GetExtensions() {
      $extensions = array(
        'continuous-horizontal' => array(
          'name' => 'Continuous Horizontal',
          'idc' => 'continuousHorizontal',
        ),
        'drag-and-move' => array(
          'name' => 'Drag and Move',
          'idc' => 'dragAndMove',
        ),
        'drop-effect' => array(
          'name' => 'Drop Effect',
          'idc' => 'dropEffect',
        ),
        'fading-effect' => array(
          'name' => 'Fading Effect',
          'idc' => 'fadingEffect',
        ),
        'interlocked-slides' => array(
          'name' => 'Interlocked Slides',
          'idc' => 'interlockedSlides',
        ),
        'offset-sections' => array(
          'name' => 'Offset Sections',
          'idc' => 'offsetSections',
        ),
        'parallax' => array(
          'name' => 'Parallax',
          'idc' => 'parallax',
        ),
        'reset-sliders' => array(
          'name' => 'Reset Sliders',
          'idc' => 'resetSliders',
        ),
        'responsive-slides' => array(
          'name' => 'Responsive Slides',
          'idc' => 'responsiveSlides',
        ),
        'scroll-horizontally' => array(
          'name' => 'Scroll Horizontally',
          'idc' => 'scrollHorizontally',
        ),
        'scroll-overflow-reset' => array(
          'name' => 'Scroll Overflow Reset',
          'idc' => 'scrollOverflowReset',
        ),
        'water-effect' => array(
          'name' => 'Water Effect',
          'idc' => 'waterEffect',
        ),
      );

      $active = apply_filters(
        'mcw-fullpage-extensions',
        array(
          'continuous-horizontal' => false,
          'drag-and-move' => false,
          'drop-effect' => false,
          'fading-effect' => false,
          'interlocked-slides' => false,
          'offset-sections' => false,
          'parallax' => false,
          'reset-sliders' => false,
          'responsive-slides' => false,
          'scroll-horizontally' => false,
          'scroll-overflow-reset' => false,
          'water-effect' => false,
        )
      );

      foreach ( $extensions as $key => $value ) {
        $extensions[ $key ]['id'] = 'mcw-fullpage-extension-' . $key;
        $extensions[ $key ]['active'] = array_key_exists( $key, $active ) ? $active[ $key ] : false;
      }

      return $extensions;
    }
  }

}

if ( ! class_exists( 'McwFullPageElementor' ) ) {

  final class McwFullPageElementor {
    private $tag;
    // Plugin name
    private $pluginName;

    // Elementor constants
    private $elementorMinimumVersion = '3.5.0';
    private $elementorFilePath = 'elementor/elementor.php';

    // Notice user meta
    private $noticeMeta = '-license-user-meta';

    // Object instance
    private static $instance = null;
    private $pluginSettings = null;

    public function __clone() {
      // Cloning instances of the class is forbidden
      _doing_it_wrong( __FUNCTION__, esc_html__( 'NOT ALLOWED', 'mcw-fullpage-elementor' ), McwFullPageElementorGlobals::Version() );
    }

    public function __wakeup() {
      // Unserializing instances of the class is forbidden
      _doing_it_wrong( __FUNCTION__, esc_html__( 'NOT ALLOWED', 'mcw-fullpage-elementor' ), McwFullPageElementorGlobals::Version() );
    }

    private function __construct() {
      // Get tag
      $this->tag = McwFullPageElementorGlobals::Tag();
      $this->pluginName = McwFullPageElementorGlobals::Name();
      // Init Plugin
      add_action( 'plugins_loaded', array( $this, 'OnPluginsLoaded' ), 100 );
    }

    public static function instance() {
      if ( is_null( self::$instance ) ) {
        self::$instance = new self();
      }

      return self::$instance;
    }

    public function OnPluginsLoaded() {
      // Check if elementor is installed/activated
      if ( ! did_action( 'elementor/loaded' ) ) {
        add_action( 'admin_notices', array( $this, 'OnAdminNoticeElementorMissing' ) );
        return;
      }

      // Check the elementor version
      if ( ! version_compare( ELEMENTOR_VERSION, $this->elementorMinimumVersion, '>=' ) ) {
        add_action( 'admin_notices', array( $this, 'OnAdminNoticeFailedElementorVersion' ) );
        return;
      }

      require_once McwFullPageElementorGlobals::Dir() . 'models/plugin-settings.php';
      $this->pluginSettings = new McwFullPageElementorPluginSettings( $this->tag );

      load_plugin_textdomain( 'mcw-fullpage-elementor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

      // Enqueue admin scripts
      add_action( 'admin_enqueue_scripts', array( $this, 'OnAdminEnqueueScripts' ) );

      // Admin notices
      add_action( 'admin_notices', array( $this, 'OnAdminNotices' ) );
      add_action( 'wp_ajax_' . $this->tag . '-admin-notice', array( $this, 'OnAjaxAdminNoticeRequest' ) );

      // Add elementor init action
      register_deactivation_hook( McwFullPageElementorGlobals::File(), array( $this, 'OnDeactivate' ) );

      require_once McwFullPageElementorGlobals::Dir() . 'models/page-settings.php';
      new McwFullPageElementorPageSettings( $this->tag, $this->pluginSettings );
    }

    // Either Elementor is not activated or not installed
    public function OnAdminNoticeElementorMissing() {
      $screen = get_current_screen();
      if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
        return;
      }

      $url = '';
      $message = '';
      $button = '';

      if ( $this->IsElementorInstalled() ) {
        if ( ! current_user_can( 'activate_plugins' ) ) {
          return;
        }

        $url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $this->elementorFilePath . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $this->elementorFilePath );
        /* translators: 1: Plugin Name */
        $message = sprintf( esc_html__( '%s requires Elementor plugin activated.', 'mcw-fullpage-elementor' ), '<strong>' . McwFullPageElementorGlobals::Name() . '</strong>' );
        $button = esc_html__( 'Activate Elementor Now', 'mcw-fullpage-elementor' );
      } else {
        $url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
        /* translators: 1: Plugin Name */
        $message = sprintf( esc_html__( '%s requires Elementor plugin installed and activated.', 'mcw-fullpage-elementor' ), '<strong>' . McwFullPageElementorGlobals::Name() . '</strong>' );
        $button = esc_html__( 'Install Elementor Now', 'mcw-fullpage-elementor' );
      }

      ?>
      <div class="error" style="display:flex;align-items:center;">
        <div class="mcw-fp-img-wrap" style="display:flex;align-items:center;padding:0.7em;">
          <img src="<?php echo McwFullPageElementorGlobals::Url() . 'assets/logo/logo-32.png'; ?>">
        </div>
        <div class="mcw-fp-img-text">
          <p>
            <?php echo $message; ?>
          </p>
          <p><a href="<?php echo $url; ?>" class="button-primary"><?php echo $button; ?></a></p>
        </div>
      </div>
      <?php
    }

    // Elementor version check notice
    public function OnAdminNoticeFailedElementorVersion() {
      if ( ! current_user_can( 'update_plugins' ) ) {
        return;
      }

      $url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $this->elementorFilePath, 'upgrade-plugin_' . $this->elementorFilePath );
      /* translators: 1: Plugin Name */
      /* translators: 2: Elementor Minimum Version */
      $message = sprintf( esc_html__( '%s requires Elementor version at least %s. Please update Elementor to continue!', 'mcw-fullpage-elementor' ), McwFullPageElementorGlobals::Name(), $this->elementorMinimumVersion );
      $button = esc_html__( 'Update Elementor Now', 'mcw-fullpage-elementor' );

      ?>
      <div class="error" style="display:flex;align-items:center;">
        <div class="mcw-fp-img-wrap" style="display:flex;align-items:center;padding:0.7em;">
          <img src="<?php echo McwFullPageElementorGlobals::Url() . 'assets/logo/logo-32.png'; ?>">
        </div>
        <div class="mcw-fp-img-text">
          <p>
            <?php echo $message; ?>
          </p>
          <p><a href="<?php echo $url; ?>" class="button-primary"><?php echo $button; ?></a></p>
        </div>
      </div>
      <?php
    }

    public function OnAdminEnqueueScripts() {
      if ( $this->pluginSettings->GetLicenseState() ) {
        return;
      }

      wp_enqueue_script(
        $this->tag . '-notice-js',
        McwFullPageElementorGlobals::Url() . 'assets/notice/notice.min.js',
        array( 'jquery', 'common' ),
        McwFullPageElementorGlobals::Version(),
        true
      );

      wp_localize_script(
        $this->tag . '-notice-js',
        'McwFullPageElementor',
        array(
          'nonce'   => wp_create_nonce( $this->tag . '-admin-notice-nonce' ),
          'ajaxurl' => admin_url( 'admin-ajax.php' ),
        )
      );
    }

    public function OnAdminNotices() {
      // Do not show in the settings page.
      if ( $this->pluginSettings->IsSettingsPage() ) {
        return;
      }

      if ( $this->pluginSettings->GetLicenseState() ) {
        return;
      }

      // Check the user transient.
      $currentUser = wp_get_current_user();
      $meta = get_user_meta( $currentUser->ID, $this->noticeMeta, true );
      if ( isset( $meta ) && ! empty( $meta ) && ( $meta > new DateTime( 'now' ) ) ) {
        return;
      }

      // No license is given
      $message = '';
      $url = menu_page_url( $this->tag, false );
      $button = esc_html__( 'Activate Now!', 'mcw-fullpage-elementor' );
      $inner = '';
      // No license is given
      if ( ! $this->pluginSettings->GetLicenseKey() ) {
        /* translators: 1: Plugin Name */
        $message = sprintf( esc_html__( '%s plugin requires the license key to be activated. Please enter your license key!', 'mcw-fullpage-elementor' ), '<strong>' . $this->pluginName . '</strong>' );
        $inner = 'class="' . $this->tag . '-notice notice notice-info is-dismissible" data-notice="no-license"';
      } else {
        /* translators: 1: Plugin Name */
        $message = sprintf( esc_html__( '%s plugin is NOT activated. Please check your license key!', 'mcw-fullpage-elementor' ), '<strong>' . $this->pluginName . '</strong>' );
        $inner = 'class="' . $this->tag . '-notice notice notice-error is-dismissible" data-notice="no-active-license"';
      }

      ?>
      <div <?php echo $inner; ?> style="display:flex;align-items:center;">
        <div class="mcw-fp-img-wrap" style="display:flex;align-items:center;padding:0.7em;">
          <img src="<?php echo McwFullPageElementorGlobals::Url() . 'assets/logo/logo-32.png'; ?>">
        </div>
        <div class="mcw-fp-img-text">
          <p>
            <?php echo $message; ?>
          </p>
          <p><a href="<?php echo $url; ?>" class="button-primary"><?php echo $button; ?></a></p>
        </div>
      </div>
      <?php
    }

    public function OnAjaxAdminNoticeRequest() {
      $notice = sanitize_text_field( $_POST['notice'] );

      check_ajax_referer( $this->tag . '-admin-notice-nonce', 'nonce' );

      if ( ( 'no-license' === $notice ) || ( 'no-active-license' === $notice ) ) {
        $currentUser = wp_get_current_user();
        update_user_meta( $currentUser->ID, $this->noticeMeta, ( new DateTime( 'now' ) )->modify( '+12 hours' ) );
      }

      wp_die();
    }

    // Returns true if Elementor is installed
    private function IsElementorInstalled() {
      $installed_plugins = get_plugins();
      return isset( $installed_plugins[ $this->elementorFilePath ] );
    }

    public function OnDeactivate() {
      if ( $this->IsElementorInstalled() ) {
        Elementor\Plugin::$instance->files_manager->clear_cache();
      }
    }
  }

}

McwFullPageElementor::instance();
