<?php

/* Copyright 2019-2023 Mehmet Celik */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

use Elementor\Controls_Manager as ControlsManager;

require_once McwFullPageElementorGlobals::Dir() . 'models/local.php';

if ( ! class_exists( 'McwFullPageElementorPageSettings' ) ) {

  class McwFullPageElementorPageSettings {
    // Tag name
    private $tag;
    // Group slug
    private $slug;
    private $tab;
    // FullPage wrapper class name
    private $wrapper = 'mcw-fp-wrapper';
    // Global variables
    protected $pageSettingsModel = array();
    private $pluginSettings = null;
    private $navigationCSS = null;
    private $extensions = null;

    public function __construct( $tag, $pluginSettings ) {
      require McwFullPageElementorGlobals::Dir() . 'models/navigation-css.php';

      // Get tag
      $this->tag = $tag;
      $this->slug = $this->GetId( 'fp-settings' );
      $this->tab = ControlsManager::TAB_SETTINGS;
      $this->pluginSettings = $pluginSettings;

      $this->navigationCSS = new McwFullPageElementorNavCSS();

      // Editor style and script
      add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'OnElementorEditorAfterEnqueueStyles' ) );
      add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'OnElementorEditorAfterEnqueueScripts' ) );

      // Enqueue FullPage scripts and styles
      add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'OnElementorBeforeEnqueueScripts' ) );
      add_action( 'elementor/frontend/before_enqueue_styles', array( $this, 'OnElementorBeforeEnqueueStyles' ) );
      add_action( 'elementor/frontend/after_enqueue_scripts', array( $this, 'OnElementorAfterEnqueueScripts' ) );
      add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'OnElementorAfterEnqueueStyles' ) );

      // Template redirect
      add_action( 'template_redirect', array( $this, 'OnTemplateRedirect' ) );
      // Template include
      add_filter( 'template_include', array( $this, 'OnTemplateInclude' ) );
      // Remove unwanted JS from header
      add_action( 'wp_print_scripts', array( $this, 'OnWpPrintScripts' ) );
      // Add extra body class
      add_filter( 'body_class', array( $this, 'OnBodyClass' ) );

      // Register controls
      add_action( 'elementor/controls/register', array( $this, 'OnElementorControlsRegistered' ), 10, 1 );

      // FullPage settings tab
      add_action( 'elementor/element/wp-post/document_settings/after_section_end', array( $this, 'OnElementorAfterSectionEnd' ), 10, 2 );
      add_action( 'elementor/element/wp-page/document_settings/after_section_end', array( $this, 'OnElementorAfterSectionEnd' ), 10, 2 );
      // TODO: add custom post types ? add_action( 'elementor/element/page/document_settings/after_section_end', array( $this, 'OnElementorAfterSectionEnd' ), 10, 2);

      // Section options
      add_action( 'elementor/element/section/section_layout/after_section_end', array( $this, 'OnElementorSectionAfterSectionEnd' ), 10, 2 );
      add_action( 'elementor/frontend/section/before_render', array( $this, 'OnElementorSectionBeforeRender' ), 10, 1 );

      add_action( 'elementor/element/container/section_layout/after_section_end', array( $this, 'OnElementorContainerAfterSectionEnd' ), 10, 2 );
      add_action( 'elementor/frontend/container/before_render', array( $this, 'OnElementorSectionBeforeRender' ), 10, 1 );

      add_filter( 'elementor/frontend/builder_content_data', array( $this, 'OnElementorBuilderContentData' ), 10, 2 );

      add_action( 'elementor/element/after_add_attributes', array( $this, 'OnAfterAddAttributes' ), 10, 1 );

      // Parse CSS
      add_action( 'elementor/element/parse_css', array( $this, 'OnElementorParseCSS' ), 10, 2 );
      add_action( 'elementor/css-file/post/parse', array( $this, 'OnElementorPostParseCSS' ), 10, 1 );

      add_filter( 'elementor/frontend/the_content', array( $this, 'OnElementorContent' ), 10, 1 );
    }

    public function OnElementorEditorAfterEnqueueStyles() {
      wp_enqueue_style(
        $this->tag . '-editor',
        McwFullPageElementorGlobals::Url() . 'assets/editor/editor.min.css',
        '',
        McwFullPageElementorGlobals::Version(),
        'all'
      );
    }

    public function OnElementorEditorAfterEnqueueScripts() {
      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return;
      }

      wp_enqueue_script(
        $this->tag . '-editor-js',
        McwFullPageElementorGlobals::Url() . 'assets/editor/editor.min.js',
        array( 'jquery' ),
        McwFullPageElementorGlobals::Version(),
        true
      );
    }

    public function OnElementorBeforeEnqueueScripts() {
      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return;
      }

      if ( $this->IsBuilderEnabled() ) {
        wp_enqueue_script(
          $this->tag . '-frontend-js',
          McwFullPageElementorGlobals::Url() . 'assets/editor/frontend.min.js',
          array( 'jquery', 'elementor-frontend' ),
          McwFullPageElementorGlobals::Version(),
          true
        );
      }
    }

    public function OnElementorBeforeEnqueueStyles() {
      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return;
      }

      if ( $this->IsBuilderEnabled() ) {
        wp_enqueue_style(
          $this->tag . '-frontend',
          McwFullPageElementorGlobals::Url() . 'assets/editor/frontend.min.css',
          array(),
          McwFullPageElementorGlobals::Version(),
          'all'
        );
      }
    }

    public function OnElementorAfterEnqueueScripts() {
      if ( ! $this->IsFullPageEnabled( false ) ) {
        return;
      }

      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return;
      }

      if ( $this->IsBuilderEnabled() ) {
        return;
      }

      // Enqueue scripts
      $dep = array();
      if ( $this->IsFieldEnabled( 'jquery' ) ) {
        $dep[] = 'jquery';
      }

      if ( ! $this->IsFieldEnabled( 'css-easing-enable' ) ) {
        wp_enqueue_script(
          $this->tag . '-easing-js',
          McwFullPageElementorGlobals::Url() . 'fullpage/vendors/easings.min.js',
          $dep,
          '1.3',
          true
        );
        $dep[] = $this->tag . '-easing-js';
      }

      // Add filter
      if ( has_filter( 'mcw-fullpage-enqueue' ) ) {
        $dep = apply_filters( 'mcw-fullpage-enqueue', $dep, $this );
      }

      // Add fullpage JS file
      $jsPath = ( ! McwFullPageCommonLocal::GetState( $this->tag ) || $this->IsFieldEnabled( 'enable-extensions' ) ) ? 'fullpage/fullpage.extensions.min.js' : 'fullpage/fullpage.min.js';

      wp_enqueue_script(
        McwFullPageElementorGlobals::FullPageScriptName() . '-js',
        McwFullPageElementorGlobals::Url() . $jsPath,
        $dep,
        McwFullPageElementorGlobals::FullPageVersion(),
        true
      );
    }

    public function OnElementorAfterEnqueueStyles() {
      if ( ! $this->IsFullPageEnabled() ) {
        return;
      }

      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return;
      }

      if ( $this->IsBuilderEnabled() ) {
        return;
      }

      // Enqueue styles
      wp_enqueue_style(
        McwFullPageElementorGlobals::FullPageScriptName(),
        McwFullPageElementorGlobals::Url() . 'fullpage/fullpage.min.css',
        array(),
        McwFullPageElementorGlobals::FullPageVersion(),
        'all'
      );

      wp_enqueue_style(
        $this->tag . '-frontend',
        McwFullPageElementorGlobals::Url() . 'assets/frontend/frontend.min.css',
        array(),
        McwFullPageElementorGlobals::Version(),
        'all'
      );

      $nav = $this->GetSectionNavStyle();
      if ( $nav ) {
        wp_enqueue_style(
          $this->tag . '-section-navigation',
          McwFullPageElementorGlobals::Url() . 'fullpage/nav/section/' . $nav . '.min.css',
          array( McwFullPageElementorGlobals::FullPageScriptName() ),
          McwFullPageElementorGlobals::FullPageVersion(),
          'all'
        );
      }

      $nav = $this->GetSlideNavStyle();
      if ( $nav ) {
        wp_enqueue_style(
          $this->tag . '-slide-navigation',
          McwFullPageElementorGlobals::Url() . 'fullpage/nav/slide/' . $nav . '.min.css',
          array( McwFullPageElementorGlobals::FullPageScriptName() ),
          McwFullPageElementorGlobals::FullPageVersion(),
          'all'
        );
      }
    }

    // Called by template_redirect action
    public function OnTemplateRedirect() {
      if ( ! $this->IsFullPageEnabled() ) {
        return;
      }

      if ( is_archive() ) {
        return;
      }

      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return;
      }

      $path = $this->GetTemplatePath( true );

      if ( false === $path ) {
        return;
      }

      include $path;
      exit();
    }

    // Called by template_include filter
    public function OnTemplateInclude( $template ) {
      if ( ! $this->IsFullPageEnabled() ) {
        return $template;
      }

      if ( is_archive() ) {
        return $template;
      }

      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return $template;
      }

      $path = $this->GetTemplatePath( false );

      if ( false === $path ) {
        return $template;
      }

      return $path;
    }

    // Remove unwanted JS from header
    // Called by wp_print_scripts action
    public function OnWpPrintScripts() {
      // Get post
      global $post;
      // Get global scripts
      global $wp_scripts;

      // Get post
      global $post;
      if ( ! ( isset( $post ) && is_object( $post ) ) ) {
        return;
      }

      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return;
      }

      // Check if fullpage is enabled
      if ( ! $this->IsFullPageEnabled() ) {
        return;
      }

      if ( $this->IsBuilderEnabled() ) {
        return;
      }

      // Check if remove theme js is enabled
      if ( $this->IsFieldEnabled( 'remove-theme-js' ) ) {
        // Error handling
        if ( isset( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
          // Get theme URL
          $themeUrl = get_bloginfo( 'template_directory' );

          // Remove theme related scripts
          foreach ( $wp_scripts->registered as $key => $script ) {
            if ( isset( $script->src ) ) {
              if ( false !== stristr( $script->src, $themeUrl ) ) {
                // Remove theme js
                unset( $wp_scripts->registered[ $key ] );
                // Remove from queue
                if ( isset( $wp_scripts->queue ) ) {
                  $wp_scripts->queue = array_diff( $wp_scripts->queue, array( $key ) );
                  $wp_scripts->queue = array_values( $wp_scripts->queue );
                }
              }
            }
          }
        }
      }

      // Check if remove js is enabled
      $removeJS = array_filter( explode( ',', $this->GetFieldValue( 'remove-js', '' ) ) );
      if ( isset( $removeJS ) && is_array( $removeJS ) && ! empty( $removeJS ) ) {
        // Error handling
        if ( isset( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
          // Remove scripts
          foreach ( $wp_scripts->registered as $key => $script ) {
            if ( isset( $script->src ) ) {
              foreach ( $removeJS as $remove ) {
                if ( ! isset( $remove ) ) {
                  continue;
                }
                // Trim js
                $remove = trim( $remove );
                // Check if script includes the removed JS
                if ( stristr( $script->src, $remove ) !== false ) {
                  // Remove js
                  unset( $wp_scripts->registered[ $key ] );
                  // Remove from queue
                  if ( isset( $wp_scripts->queue ) ) {
                    $wp_scripts->queue = array_diff( $wp_scripts->queue, array( $key ) );
                    $wp_scripts->queue = array_values( $wp_scripts->queue );
                  }
                }
              }
            }
          }
        }
      }
    }

    public function OnBodyClass( $classes ) {
      if ( ! $this->IsFullPageEnabled() ) {
        return $classes;
      }

      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return $classes;
      }

      $extra = 'wp-';
      if ( ! McwFullPageCommonLocal::GetState( $this->tag ) ) {
        $extra .= 'fullpage';
        $classes[] = $extra . '-js';
      }

      if ( 'off' !== $this->GetFieldValue( 'nav', 'right', 'section-navigation' ) && $this->IsFieldEnabled( 'nav-big', 'section-navigation' ) ) {
        $classes[] = 'fp-big-nav';
      }

      if ( 'off' !== $this->GetFieldValue( 'nav', 'off', 'slide-navigation' ) && $this->IsFieldEnabled( 'nav-big', 'slide-navigation' ) ) {
        $classes[] = 'fp-big-slide-nav';
      }

      if ( 'modern' === $this->GetFieldValue( 'arrow-style', 'modern', 'control-arrows-options' ) ) {
        $classes[] = 'fp-control-arrow-modern';
      }

      return $classes;
    }

    public function OnElementorControlsRegistered( $page ) {
      require McwFullPageElementorGlobals::Dir() . 'models/controls/control-arrows.php';
      require McwFullPageElementorGlobals::Dir() . 'models/controls/nav-colors.php';
      require McwFullPageElementorGlobals::Dir() . 'models/controls/nav-tooltip-colors.php';
      require McwFullPageElementorGlobals::Dir() . 'models/controls/nav-section.php';
      require McwFullPageElementorGlobals::Dir() . 'models/controls/nav-slide.php';

      // Control Arrows group
      $page->add_group_control(
        McwFullPageElementorControlArrowsControl::get_type(),
        new McwFullPageElementorControlArrowsControl(
          array(
            // navigation, navigationPosition
            $this->GetId( 'arrow-style' ) => array(
              'label' => esc_html__( 'Control Arrows Style', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::SELECT,
              'label_block' => true,
              'options' => array(
                'off' => esc_html__( 'Default', 'mcw-fullpage-elementor' ),
                'modern' => esc_html__( 'Modern', 'mcw-fullpage-elementor' ),
              ),
              'default' => 'modern',
              'description' => esc_html__( 'Determines the style of the control arrow.', 'mcw-fullpage-elementor' ),
            ),
            // Main color
            $this->GetId( 'arrow-color-main' ) => array(
              'label' => esc_html__( 'Control Arrows Color', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::COLOR,
              'alpha' => false,
              'description' => esc_html__( 'Determines the color of the control arrow.', 'mcw-fullpage-elementor' ),
            ),
          )
        )
      );

      // Navigation colors group
      $page->add_group_control(
        McwFullPageElementorNavColorsControl::get_type(),
        new McwFullPageElementorNavColorsControl(
          array(
            // Main color
            $this->GetId( 'color-main' ) => array(
              'label' => esc_html__( 'Main Color', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::COLOR,
              'alpha' => false,
              'responsive' => true,
              'description' => esc_html__( 'The color of bullets when this section is active.', 'mcw-fullpage-elementor' ),
            ),
            // Hover color
            $this->GetId( 'color-hover' ) => array(
              'label' => esc_html__( 'Hover Color', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::COLOR,
              'alpha' => false,
              'responsive' => true,
              'description' => esc_html__( 'The hover color of bullets when this section is active. This color may not be used in some of the navigation styles.', 'mcw-fullpage-elementor' ),
            ),
            // Active color
            $this->GetId( 'color-active' ) => array(
              'label' => esc_html__( 'Active Color', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::COLOR,
              'alpha' => false,
              'responsive' => true,
              'description' => esc_html__( 'The active color of bullets when this section is active. This color may not be used in some of the navigation styles.', 'mcw-fullpage-elementor' ),
            ),
          )
        )
      );

      // Tooltip colors group
      $page->add_group_control(
        McwFullPageElementorNavTooltipColorsControl::get_type(),
        new McwFullPageElementorNavTooltipColorsControl(
          array(
            // Main color
            $this->GetId( 'color-tooltip-background' ) => array(
              'label' => esc_html__( 'Tooltip Background Color', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::COLOR,
              'alpha' => false,
              'responsive' => true,
              'description' => esc_html__( 'The background color of the navigation tooltip.', 'mcw-fullpage-elementor' ),
            ),
            // Hover color
            $this->GetId( 'color-tooltip-text' ) => array(
              'label' => esc_html__( 'Tooltip Text Color', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::COLOR,
              'alpha' => false,
              'responsive' => true,
              'description' => esc_html__( 'The text color of the navigation tooltip.', 'mcw-fullpage-elementor' ),
            ),
          )
        )
      );

      // Section Navigation Group
      $page->add_group_control(
        McwFullPageElementorNavSectionControl::get_type(),
        new McwFullPageElementorNavSectionControl(
          array(
            // navigation, navigationPosition
            $this->GetId( 'nav' ) => array(
              'label' => esc_html__( 'Section Navigation', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::SELECT,
              'label_block' => true,
              'options' => array(
                'off' => esc_html__( 'Off', 'mcw-fullpage-elementor' ),
                'left' => esc_html__( 'Left', 'mcw-fullpage-elementor' ),
                'right' => esc_html__( 'Right', 'mcw-fullpage-elementor' ),
              ),
              'default' => 'right',
              'description' => esc_html__( 'The position of navigation bullets.', 'mcw-fullpage-elementor' ),
            ),
            // Section Navigation Style
            $this->GetId( 'nav-style' ) => array(
              'label' => esc_html__( 'Section Navigation Style', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::SELECT,
              'label_block' => true,
              'options' => array(
                'default' => esc_html__( 'Default', 'mcw-fullpage-elementor' ),
                'circles' => esc_html__( 'Circles', 'mcw-fullpage-elementor' ),
                'circles-inverted' => esc_html__( 'Circles Inverted', 'mcw-fullpage-elementor' ),
                'expanding-circles' => esc_html__( 'Expanding Circles', 'mcw-fullpage-elementor' ),
                'filled-circles' => esc_html__( 'Filled Circles', 'mcw-fullpage-elementor' ),
                'filled-circle-within' => esc_html__( 'Filled Circles Within', 'mcw-fullpage-elementor' ),
                'multiple-circles' => esc_html__( 'Multiple Circles', 'mcw-fullpage-elementor' ),
                'rotating-circles' => esc_html__( 'Rotating Circles', 'mcw-fullpage-elementor' ),
                'rotating-circles2' => esc_html__( 'Rotating Circles 2', 'mcw-fullpage-elementor' ),
                'squares' => esc_html__( 'Squares', 'mcw-fullpage-elementor' ),
                'squares-border' => esc_html__( 'Squares Border', 'mcw-fullpage-elementor' ),
                'expanding-squares' => esc_html__( 'Expanding Squares', 'mcw-fullpage-elementor' ),
                'filled-squares' => esc_html__( 'Filled Squares', 'mcw-fullpage-elementor' ),
                'multiple-squares' => esc_html__( 'Multiple Squares', 'mcw-fullpage-elementor' ),
                'squares-to-rombs' => esc_html__( 'Squares to Rombs', 'mcw-fullpage-elementor' ),
                'multiple-squares-to-rombs' => esc_html__( 'Multiple Squares to Rombs', 'mcw-fullpage-elementor' ),
                'filled-rombs' => esc_html__( 'Filled Rombs', 'mcw-fullpage-elementor' ),
                'filled-bars' => esc_html__( 'Filled Bars', 'mcw-fullpage-elementor' ),
                'story-telling' => esc_html__( 'Story Telling', 'mcw-fullpage-elementor' ),
                // Maybe add in the future 'crazy-text-effect' => esc_html__( 'Crazy Text Effect', 'mcw-fullpage-elementor' ),
              ),
              'default' => 'default',
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'description' => esc_html__( 'The section navigation style.', 'mcw-fullpage-elementor' ),
            ),
            // Responsive Enable
            $this->GetId( 'show-bullets' ) => array(
              'label' => esc_html__( 'Show', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::SWITCHER,
              'default' => 'yes',
              'tablet_default' => 'yes',
              'mobile_default' => 'yes',
              'responsive' => true,
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'description' => esc_html__( 'Shows or hide navigation bullets responsively.', 'mcw-fullpage-elementor' ),
            ),
            // Main color
            $this->GetId( 'color-main' ) => array(
              'label' => esc_html__( 'Main Color', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::COLOR,
              'responsive' => true,
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'alpha' => false,
              'description' => esc_html__( 'The color of bullets on sections.', 'mcw-fullpage-elementor' ),
            ),
            // Hover color
            $this->GetId( 'color-hover' ) => array(
              'label' => esc_html__( 'Hover Color', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::COLOR,
              'responsive' => true,
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'alpha' => false,
              'description' => esc_html__( 'The hover color of bullets on sections. This color may not be used in some of the navigation styles.', 'mcw-fullpage-elementor' ),
            ),
            // Active color
            $this->GetId( 'color-active' ) => array(
              'label' => esc_html__( 'Active Color', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::COLOR,
              'responsive' => true,
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'alpha' => false,
              'description' => esc_html__( 'The active color of bullets on sections. This color may not be used in some of the navigation styles.', 'mcw-fullpage-elementor' ),
            ),
            // Tooltip Background
            $this->GetId( 'tooltip-background-color' ) => array(
              'label' => esc_html__( 'Tooltip Background Color', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::COLOR,
              'responsive' => true,
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'description' => esc_html__( 'The background color of the navigation tooltip.', 'mcw-fullpage-elementor' ),
            ),
            // Tooltip Color
            $this->GetId( 'tooltip-text-color' ) => array(
              'label' => esc_html__( 'Tooltip Text Color', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::COLOR,
              'responsive' => true,
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'alpha' => false,
              'description' => esc_html__( 'The text color of the navigation tooltip.', 'mcw-fullpage-elementor' ),
            ),
            // Distance
            $this->GetId( 'space' ) => array(
              'label' => esc_html__( 'Space', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::NUMBER,
              'min' => 0,
              'step' => 1,
              'default' => '17',
              'responsive' => true,
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'description' => esc_html__( 'Space distance from left/right in px.', 'mcw-fullpage-elementor' ),
            ),
            // Show Active Tooltip
            $this->GetId( 'show-active-tooltip' ) => array(
              'label' => esc_html__( 'Show Active Tooltip', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::SWITCHER,
              'default' => 'no',
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'description' => esc_html__( 'Shows a persistent tooltip for the actively viewed section if enabled.', 'mcw-fullpage-elementor' ),
            ),
            // Clickable Tooltip
            $this->GetId( 'click-tooltip' ) => array(
              'label' => esc_html__( 'Clickable Tooltip', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::SWITCHER,
              'default' => 'no',
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'description' => esc_html__( 'The tooltips for the sections are clickable if enabled.', 'mcw-fullpage-elementor' ),
            ),
            // Bigger navigation styles
            $this->GetId( 'nav-big' ) => array(
              'label' => esc_html__( 'Bigger Navigation', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::SWITCHER,
              'default' => 'no',
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'description' => esc_html__( 'Sets bigger navigation bullets.', 'mcw-fullpage-elementor' ),
            ),
          )
        )
      );

      // Slide Navigation Group
      $page->add_group_control(
        McwFullPageElementorNavSlideControl::get_type(),
        new McwFullPageElementorNavSlideControl(
          array(
            // slidesNavigation, slidesNavPosition
            $this->GetId( 'nav' ) => array(
              'label' => esc_html__( 'Slides Navigation', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::SELECT,
              'label_block' => true,
              'options' => array(
                'off' => esc_html__( 'Off', 'mcw-fullpage-elementor' ),
                'top' => esc_html__( 'Top', 'mcw-fullpage-elementor' ),
                'bottom' => esc_html__( 'Bottom', 'mcw-fullpage-elementor' ),
              ),
              'default' => 'off',
              'description' => esc_html__( 'The position of navigation bar for sliders.', 'mcw-fullpage-elementor' ),
            ),
            // Slide Navigation Style
            $this->GetId( 'nav-style' ) => array(
              'label' => esc_html__( 'Slide Navigation Style', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::SELECT,
              'label_block' => true,
              'options' => array(
                'default' => esc_html__( 'Default', 'mcw-fullpage-elementor' ),
                'circles' => esc_html__( 'Circles', 'mcw-fullpage-elementor' ),
                'circles-inverted' => esc_html__( 'Circles Inverted', 'mcw-fullpage-elementor' ),
                'expanding-circles' => esc_html__( 'Expanding Circles', 'mcw-fullpage-elementor' ),
                'filled-circles' => esc_html__( 'Filled Circles', 'mcw-fullpage-elementor' ),
                'filled-circle-within' => esc_html__( 'Filled Circles Within', 'mcw-fullpage-elementor' ),
                'multiple-circles' => esc_html__( 'Multiple Circles', 'mcw-fullpage-elementor' ),
                'rotating-circles' => esc_html__( 'Rotating Circles', 'mcw-fullpage-elementor' ),
                'rotating-circles2' => esc_html__( 'Rotating Circles 2', 'mcw-fullpage-elementor' ),
                'squares' => esc_html__( 'Squares', 'mcw-fullpage-elementor' ),
                'squares-border' => esc_html__( 'Squares Border', 'mcw-fullpage-elementor' ),
                'expanding-squares' => esc_html__( 'Expanding Squares', 'mcw-fullpage-elementor' ),
                'filled-squares' => esc_html__( 'Filled Squares', 'mcw-fullpage-elementor' ),
                'multiple-squares' => esc_html__( 'Multiple Squares', 'mcw-fullpage-elementor' ),
                'squares-to-rombs' => esc_html__( 'Squares to Rombs', 'mcw-fullpage-elementor' ),
                'multiple-squares-to-rombs' => esc_html__( 'Multiple Squares to Rombs', 'mcw-fullpage-elementor' ),
                'filled-rombs' => esc_html__( 'Filled Rombs', 'mcw-fullpage-elementor' ),
                'filled-bars' => esc_html__( 'Filled Bars', 'mcw-fullpage-elementor' ),
                'story-telling' => esc_html__( 'Story Telling', 'mcw-fullpage-elementor' ),
              ),
              'default' => 'default',
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'description' => esc_html__( 'The slide navigation style.', 'mcw-fullpage-elementor' ),
            ),
            // Responsive Enable
            $this->GetId( 'show-bullets' ) => array(
              'label' => esc_html__( 'Show', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::SWITCHER,
              'default' => 'yes',
              'tablet_default' => 'yes',
              'mobile_default' => 'yes',
              'responsive' => true,
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'description' => esc_html__( 'Shows or hide slide navigation bullets responsively.', 'mcw-fullpage-elementor' ),
            ),
            // Main color
            $this->GetId( 'color-main' ) => array(
              'label' => esc_html__( 'Main Color', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::COLOR,
              'responsive' => true,
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'alpha' => false,
              'description' => esc_html__( 'The color of bullets on slides.', 'mcw-fullpage-elementor' ),
            ),
            // Hover color
            $this->GetId( 'color-hover' ) => array(
              'label' => esc_html__( 'Hover Color', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::COLOR,
              'responsive' => true,
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'alpha' => false,
              'description' => esc_html__( 'The hover color of bullets on slides. This color may not be used in some of the navigation styles.', 'mcw-fullpage-elementor' ),
            ),
            // Active color
            $this->GetId( 'color-active' ) => array(
              'label' => esc_html__( 'Active Color', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::COLOR,
              'responsive' => true,
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'alpha' => false,
              'description' => esc_html__( 'The active color of bullets on slides. This color may not be used in some of the navigation styles.', 'mcw-fullpage-elementor' ),
            ),
            // Distance
            $this->GetId( 'space' ) => array(
              'label' => esc_html__( 'Space', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::NUMBER,
              'min' => 0,
              'step' => 1,
              'default' => '17',
              'responsive' => true,
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'description' => esc_html__( 'Space distance from top/bottom in px.', 'mcw-fullpage-elementor' ),
            ),
            // Bigger navigation styles
            $this->GetId( 'nav-big' ) => array(
              'label' => esc_html__( 'Bigger Slide Navigation', 'mcw-fullpage-elementor' ),
              'type' => ControlsManager::SWITCHER,
              'default' => 'no',
              'condition' => array(
                $this->GetId( 'nav' ) . '!' => 'off',
              ),
              'description' => esc_html__( 'Sets bigger slide navigation bullets.', 'mcw-fullpage-elementor' ),
            ),
          )
        )
      );
    }

    public function OnElementorAfterSectionEnd( $page, $args ) {
      $this->AddFullPageTab( $page );

      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return;
      }

      $this->AddNavigationTab( $page );
      $this->AddScrollingTab( $page );
      $this->AddDesignTab( $page );
      $this->AddEventsTab( $page );
      $this->AddExtensionsTab( $page );
      $this->AddCustomizationsTab( $page );
      $this->AddAdvancedTab( $page );
    }

    public function OnElementorSectionAfterSectionEnd( $page, $args ) {
      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return;
      }

      $this->AddSectionTab( $page );

      // TODO: check for > .elementor-container for sections in elementor plugin
      $page->update_responsive_control(
        'custom_height',
        array(
          'selectors' => array(
            '{{WRAPPER}} > .elementor-container' => 'min-height: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} > .fp-overflow > .elementor-container' => 'min-height: {{SIZE}}{{UNIT}};',
          ),
        )
      );

      $page->update_responsive_control(
        'custom_height_inner',
        array(
          'selectors' => array(
            '{{WRAPPER}} > .elementor-container' => 'min-height: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} > .fp-overflow > .elementor-container' => 'min-height: {{SIZE}}{{UNIT}};',
          ),
        )
      );

      $page->update_responsive_control(
        'content_width',
        array(
          'selectors' => array(
            '{{WRAPPER}} > .elementor-container' => 'max-width: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} > .fp-overflow > .elementor-container' => 'max-width: {{SIZE}}{{UNIT}};',
          ),
        )
      );

      $page->update_control(
        'text_align',
        array(
          'selectors' => array(
            '{{WRAPPER}} > .elementor-container' => 'text-align: {{VALUE}};',
            '{{WRAPPER}} > .fp-overflow > .elementor-container' => 'text-align: {{VALUE}};',
          ),
        )
      );

      $contentSelector = Elementor\Plugin::$instance->experiments->is_feature_active( 'e_dom_optimization' ) ?
				'{{WRAPPER}} > .elementor-container > .elementor-column > .elementor-widget-wrap' :
				'{{WRAPPER}} > .elementor-container > .elementor-row > .elementor-column > .elementor-column-wrap > .elementor-widget-wrap';
      $contentSelectorOverflow = Elementor\Plugin::$instance->experiments->is_feature_active( 'e_dom_optimization' ) ?
				'{{WRAPPER}} > .fp-overflow > .elementor-container > .elementor-column > .elementor-widget-wrap' :
				'{{WRAPPER}} > .fp-overflow > .elementor-container > .elementor-row > .elementor-column > .elementor-column-wrap > .elementor-widget-wrap';
      $page->update_control(
        'content_position',
        array(
          'selectors' => array(
            $contentSelector => 'align-content: {{VALUE}}; align-items: {{VALUE}};',
            $contentSelectorOverflow => 'align-content: {{VALUE}}; align-items: {{VALUE}};',
          ),
        )
      );
    }

    public function OnElementorContainerAfterSectionEnd( $page, $args ) {
      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return;
      }

      $this->AddSectionTab( $page );
    }

    public function OnElementorSectionBeforeRender( $section ) {
      if ( ! $this->IsFullPageEnabled() ) {
        return;
      }

      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return;
      }

      if ( $this->GetSectionValue( $section, 'section-no-render', true ) ) {
        return;
      }

      $attr = array();
      if ( ! $this->IsSectionInner( $section ) ) {
        $attr['class'][] = 'mcw-fp-section';

        if ( $this->IsSectionFieldEnabled( $section, 'section-is-slide' ) ) {
          $attr['class'][] = 'mcw-fp-section-slide';
        }

        if ( 'auto' === $this->GetSectionValue( $section, 'section-behaviour', 'full' ) ) {
          $attr['class'][] = 'fp-auto-height';
        }

        if ( 'responsive' === $this->GetSectionValue( $section, 'section-behaviour', 'full' ) ) {
          $attr['class'][] = 'fp-auto-height-responsive';
        }

        if ( $this->IsSectionFieldEnabled( $section, 'section-scrollbars' ) ) {
          $attr['class'][] = 'fp-noscroll';
        }

        if ( ! $this->IsFieldEnabled( 'disable-anchors' ) ) {
          $attr['data-anchor'] = $this->GetAnchor( $section );
        }

        $tooltip = trim( $this->GetSectionValue( $section, 'section-nav-tooltip', '' ) );
        $attr['data-tooltip'] = ( isset( $tooltip ) && ! empty( $tooltip ) ) ? $tooltip : '';

        $offsetSections = $this->GetExtensionsInfo( 'offset-sections' );
        if ( $offsetSections['active'] && $this->IsFieldEnabled( 'enable-extensions' ) && $this->IsFieldEnabled( 'extension-offset-sections' ) ) {
          $attr['data-percentage'] = $this->GetSectionValue( $section, 'data-percentage', '100' );
          $attr['data-centered'] = $this->GetSectionValue( $section, 'data-centered', 'yes' );
        }

        $dropEffect = $this->GetExtensionsInfo( 'drop-effect' );
        if ( $dropEffect['active'] && $this->IsFieldEnabled( 'enable-extensions' ) && $this->IsFieldEnabled( 'extension-drop-effect' ) ) {
          $attr['data-drop'] = $this->GetSectionValue( $section, 'section-data-drop', 'all' );
        }

        $waterEffect = $this->GetExtensionsInfo( 'water-effect' );
        if ( $waterEffect['active'] && $this->IsFieldEnabled( 'enable-extensions' ) && $this->IsFieldEnabled( 'extension-water-effect' ) ) {
          $attr['data-water'] = $this->GetSectionValue( $section, 'section-data-water', 'all' );
        }
      } else {
        $attr['class'][] = 'mcw-fp-slide';
        if ( ! $this->IsFieldEnabled( 'disable-anchors' ) ) {
          $anchor = $this->GetSlideAnchor( $section );
          if ( $anchor ) {
            $attr['data-anchor'] = $this->GetSlideAnchor( $section );
          }
        }
      }

      if ( $this->IsSectionFieldEnabled( $section, 'section-full-height-col' ) ) {
        $attr['class'][] = 'fp-col-full-height';
      }

      $elementorVerticalAlignment = $this->GetSectionValueBase( $section, 'content_position', 'default' );
      $fullPageVerticalAlignment = $this->GetFieldValue( 'vertical-alignment', 'center' );
      if ( $fullPageVerticalAlignment !== 'center' && $elementorVerticalAlignment !== 'default' ) {
        $attr['class'][] = 'fp-table';
      }

      $section->add_render_attribute( '_wrapper', $attr );
    }

    public function OnElementorBuilderContentData( $data, $postId ) {
      if ( ! empty( $data ) && ( get_the_ID() !== $postId ) ) {
        $data = \Elementor\Plugin::instance()->db->iterate_data(
          $data,
          function( $element ) {
            if ( in_array( $element['elType'], [ 'section', 'container' ] ) ) {
              $element['settings'][ $this->GetId( 'section-no-render' ) ] = true;
            }

            return $element;
          }
        );
      }

      return $data;
    }

    public function OnAfterAddAttributes( $section ) {
      if ( ! in_array( $section->get_data( 'elType' ), [ 'section', 'container' ] ) ) {
        return;
      }

      if ( ! $this->IsFullPageEnabled() ) {
        return;
      }

      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return;
      }

      if ( $this->GetSectionValue( $section, 'section-no-render', true ) ) {
        return;
      }

      // Check if the ID and and fullpage anchor are same, if they are, remove the ID parameter, just in case
      $anchor = $section->get_render_attributes( '_wrapper', 'data-anchor' );
      $id = $section->get_render_attributes( '_wrapper', 'id' );

      if ( $anchor && $id ) {
        $anchor = is_array( $anchor ) ? $anchor[0] : $anchor;
        $id = is_array( $id ) ? $id[0] : $id;

        if ( strcasecmp( $id, $anchor ) === 0 ) {
          $section->remove_render_attribute( '_wrapper', 'id' );
        }
      }
    }

    public function OnElementorParseCSS( $post, $section ) {
      if ( $post instanceof Dynamic_CSS ) {
        return;
      }

      if ( ! in_array( $section->get_data( 'elType' ), [ 'section', 'container' ] ) ) {
        return;
      }

      if ( ! $this->IsFullPageEnabled() ) {
        return;
      }

      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return;
      }

      if ( $this->GetSectionValue( $section, 'section-no-render', true ) ) {
        return;
      }

      if ( $post->get_post_id() !== get_the_ID() ) {
        return;
      }

      if ( $this->IsSectionInner( $section ) ) {
        return;
      }

      $anchor = $this->GetAnchor( $section );
      if ( $this->IsSectionFieldEnabled( $section, 'group-section-nav-colors', 'section-element-navigation' ) ) {
        $css = $this->navigationCSS->GetCustomCSS(
          $this->GetSectionNavStyle(),
          "body[class*='fp-viewing-{$anchor}'] #fp-nav",
          $this->GetSectionValue( $section, 'color-main', '', 'section-element-navigation' ),
          $this->GetSectionValue( $section, 'color-active', '', 'section-element-navigation' ),
          $this->GetSectionValue( $section, 'color-hover', '', 'section-element-navigation' )
        );

        $post->get_stylesheet()->add_raw_css( $css );

        $css = $this->navigationCSS->GetCustomCSS(
          $this->GetSectionNavStyle(),
          "body[class*='fp-viewing-{$anchor}'] #fp-nav",
          $this->GetSectionValue( $section, 'color-main_tablet', '', 'section-element-navigation' ),
          $this->GetSectionValue( $section, 'color-active_tablet', '', 'section-element-navigation' ),
          $this->GetSectionValue( $section, 'color-hover_tablet', '', 'section-element-navigation' )
        );

        $post->get_stylesheet()->add_raw_css( $css, 'tablet' );

        $css = $this->navigationCSS->GetCustomCSS(
          $this->GetSectionNavStyle(),
          "body[class*='fp-viewing-{$anchor}'] #fp-nav",
          $this->GetSectionValue( $section, 'color-main_mobile', '', 'section-element-navigation' ),
          $this->GetSectionValue( $section, 'color-active_mobile', '', 'section-element-navigation' ),
          $this->GetSectionValue( $section, 'color-hover_mobile', '', 'section-element-navigation' )
        );

        $post->get_stylesheet()->add_raw_css( $css, 'mobile' );
      }

      if ( $this->IsSectionFieldEnabled( $section, 'group-section-nav-tooltip-colors', 'section-element-navigation-tooltip' ) && ( $this->GetSectionValue( $section, 'section-nav-tooltip', '' ) !== '' ) ) {
        $tooltipBackground = $this->GetSectionValue( $section, 'color-tooltip-background', '', 'section-element-navigation-tooltip' );
        $tooltipColor = $this->GetSectionValue( $section, 'color-tooltip-text', '', 'section-element-navigation-tooltip' );
        if ( ! empty( $tooltipBackground ) || ! empty( $tooltipColor ) ) {
          $css = sprintf(
            "body[class*='fp-viewing-%s'] #fp-nav ul li .fp-tooltip{%s%s}",
            $anchor,
            empty( $tooltipBackground ) ? '' : ( 'background-color:' . $tooltipBackground . ';' ),
            empty( $tooltipColor ) ? '' : ( 'color:' . $tooltipColor . ';' )
          );
          $post->get_stylesheet()->add_raw_css( $css );
        }

        $tooltipBackground = $this->GetSectionValue( $section, 'color-tooltip-background_tablet', '', 'section-element-navigation-tooltip' );
        $tooltipColor = $this->GetSectionValue( $section, 'color-tooltip-text_tablet', '', 'section-element-navigation-tooltip' );
        if ( ! empty( $tooltipBackground ) || ! empty( $tooltipColor ) ) {
          $css = sprintf(
            "body[class*='fp-viewing-%s'] #fp-nav ul li .fp-tooltip{%s%s}",
            $anchor,
            empty( $tooltipBackground ) ? '' : ( 'background-color:' . $tooltipBackground . ';' ),
            empty( $tooltipColor ) ? '' : ( 'color:' . $tooltipColor . ';' )
          );
          $post->get_stylesheet()->add_raw_css( $css, 'tablet' );
        }

        $tooltipBackground = $this->GetSectionValue( $section, 'color-tooltip-background_mobile', '', 'section-element-navigation-tooltip' );
        $tooltipColor = $this->GetSectionValue( $section, 'color-tooltip-text_mobile', '', 'section-element-navigation-tooltip' );
        if ( ! empty( $tooltipBackground ) || ! empty( $tooltipColor ) ) {
          $css = sprintf(
            "body[class*='fp-viewing-%s'] #fp-nav ul li .fp-tooltip{%s%s}",
            $anchor,
            empty( $tooltipBackground ) ? '' : ( 'background-color:' . $tooltipBackground . ';' ),
            empty( $tooltipColor ) ? '' : ( 'color:' . $tooltipColor . ';' )
          );
          $post->get_stylesheet()->add_raw_css( $css, 'mobile' );
        }
      }
    }

    public function OnElementorPostParseCSS( $post ) {
      if ( $post instanceof Dynamic_CSS ) {
        return;
      }

      if ( ! $this->IsFullPageEnabled() ) {
        return;
      }

      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return;
      }

      if ( $post->get_post_id() !== get_the_ID() ) {
        return;
      }

      if ( $this->IsBuilderEnabled() ) {
        return;
      }

      $css = array();
      $cssTablet = array();
      $cssMobile = array();

      $sectionNavStyle = $this->GetFieldValue( 'nav', 'right', 'section-navigation' );
      $slideNavStyle = $this->GetFieldValue( 'nav', 'off', 'slide-navigation' );

      if ( ( 'off' !== $sectionNavStyle ) ) {
        $tooltipBackground = $this->GetFieldValue( 'tooltip-background-color', '', 'section-navigation' );
        $tooltipColor = $this->GetFieldValue( 'tooltip-text-color', '', 'section-navigation' );
        if ( ! empty( $tooltipBackground ) || ! empty( $tooltipColor ) ) {
          $css[] = sprintf(
            '#fp-nav ul li .fp-tooltip{%s%s}',
            empty( $tooltipBackground ) ? '' : ( 'background-color:' . $tooltipBackground . ';' ),
            empty( $tooltipColor ) ? '' : ( 'color:' . $tooltipColor . ';' )
          );
        }

        $tooltipBackground = $this->GetFieldValue( 'tooltip-background-color_tablet', '', 'section-navigation' );
        $tooltipColor = $this->GetFieldValue( 'tooltip-text-color_tablet', '', 'section-navigation' );
        if ( ! empty( $tooltipBackground ) || ! empty( $tooltipColor ) ) {
          $cssTablet[] = sprintf(
            '#fp-nav ul li .fp-tooltip{%s%s}',
            empty( $tooltipBackground ) ? '' : ( 'background-color:' . $tooltipBackground . ';' ),
            empty( $tooltipColor ) ? '' : ( 'color:' . $tooltipColor . ';' )
          );
        }

        $tooltipBackground = $this->GetFieldValue( 'tooltip-background-color_mobile', '', 'section-navigation' );
        $tooltipColor = $this->GetFieldValue( 'tooltip-text-color_mobile', '', 'section-navigation' );
        if ( ! empty( $tooltipBackground ) || ! empty( $tooltipColor ) ) {
          $cssMobile[] = sprintf(
            '#fp-nav ul li .fp-tooltip{%s%s}',
            empty( $tooltipBackground ) ? '' : ( 'background-color:' . $tooltipBackground . ';' ),
            empty( $tooltipColor ) ? '' : ( 'color:' . $tooltipColor . ';' )
          );
        }

        $css[] = $this->navigationCSS->GetCustomCSS(
          $this->GetSectionNavStyle(),
          '#fp-nav',
          $this->GetFieldValue( 'color-main', '', 'section-navigation' ),
          $this->GetFieldValue( 'color-active', '', 'section-navigation' ),
          $this->GetFieldValue( 'color-hover', '', 'section-navigation' )
        );

        $cssTablet[] = $this->navigationCSS->GetCustomCSS(
          $this->GetSectionNavStyle(),
          '#fp-nav',
          $this->GetFieldValue( 'color-main_tablet', '', 'section-navigation' ),
          $this->GetFieldValue( 'color-active_tablet', '', 'section-navigation' ),
          $this->GetFieldValue( 'color-hover_tablet', '', 'section-navigation' )
        );

        $cssMobile[] = $this->navigationCSS->GetCustomCSS(
          $this->GetSectionNavStyle(),
          '#fp-nav',
          $this->GetFieldValue( 'color-main_mobile', '', 'section-navigation' ),
          $this->GetFieldValue( 'color-active_mobile', '', 'section-navigation' ),
          $this->GetFieldValue( 'color-hover_mobile', '', 'section-navigation' )
        );

        $showBullets = $this->IsFieldEnabledResponsive( 'show-bullets', 'section-navigation', 'yes' );
        $css[] = sprintf(
          '#fp-nav{display:%s;}',
          $showBullets['desktop'] ? 'block' : 'none'
        );

        $cssTablet[] = sprintf(
          '#fp-nav{display:%s;}',
          $showBullets['tablet'] ? 'block' : 'none'
        );

        $cssMobile[] = sprintf(
          '#fp-nav{display:%s;}',
          $showBullets['mobile'] ? 'block' : 'none'
        );

        $space = $this->GetFieldValue( 'space', 17, 'section-navigation' );
        $css[] = sprintf( '#fp-nav.fp-right{right:%1$spx}#fp-nav.fp-left{left:%1$spx}', $space );
        $space = $this->GetFieldValue( 'space_tablet', '', 'section-navigation' );
        if ( ! empty( $space ) ) {
          $cssTablet[] = sprintf( '#fp-nav.fp-right{right:%1$spx}#fp-nav.fp-left{left:%1$spx}', $space );
        }
        $space = $this->GetFieldValue( 'space_mobile', '', 'section-navigation' );
        if ( ! empty( $space ) ) {
          $cssMobile[] = sprintf( '#fp-nav.fp-right{right:%1$spx}#fp-nav.fp-left{left:%1$spx}', $space );
        }
      }

      if ( ( 'off' !== $slideNavStyle ) ) {
        $css[] = $this->navigationCSS->GetCustomCSS(
          $this->GetSlideNavStyle(),
          '.fp-slidesNav',
          $this->GetFieldValue( 'color-main', '', 'slide-navigation' ),
          $this->GetFieldValue( 'color-active', '', 'slide-navigation' ),
          $this->GetFieldValue( 'color-hover', '', 'slide-navigation' )
        );

        $cssTablet[] = $this->navigationCSS->GetCustomCSS(
          $this->GetSlideNavStyle(),
          '.fp-slidesNav',
          $this->GetFieldValue( 'color-main_tablet', '', 'slide-navigation' ),
          $this->GetFieldValue( 'color-active_tablet', '', 'slide-navigation' ),
          $this->GetFieldValue( 'color-hover_tablet', '', 'slide-navigation' )
        );

        $cssMobile[] = $this->navigationCSS->GetCustomCSS(
          $this->GetSlideNavStyle(),
          '.fp-slidesNav',
          $this->GetFieldValue( 'color-main_mobile', '', 'slide-navigation' ),
          $this->GetFieldValue( 'color-active_mobile', '', 'slide-navigation' ),
          $this->GetFieldValue( 'color-hover_mobile', '', 'slide-navigation' )
        );

        $showBullets = $this->IsFieldEnabledResponsive( 'show-bullets', 'slide-navigation', 'yes' );
        $css[] = sprintf(
          '.fp-slidesNav{display:%s;}',
          $showBullets['desktop'] ? 'block' : 'none'
        );

        $cssTablet[] = sprintf(
          '.fp-slidesNav{display:%s;}',
          $showBullets['tablet'] ? 'block' : 'none'
        );

        $cssMobile[] = sprintf(
          '.fp-slidesNav{display:%s;}',
          $showBullets['mobile'] ? 'block' : 'none'
        );

        $space = $this->GetFieldValue( 'space', 17, 'slide-navigation' );
        $css[] = sprintf( '.fp-slidesNav.fp-bottom{bottom:%1$spx}.fp-slidesNav.fp-top{top:%1$spx}', $space );
        $space = $this->GetFieldValue( 'space_tablet', '', 'slide-navigation' );
        if ( ! empty( $space ) ) {
          $cssTablet[] = sprintf( '.fp-slidesNav.fp-bottom{bottom:%1$spx}.fp-slidesNav.fp-top{top:%1$spx}', $space );
        }
        $space = $this->GetFieldValue( 'space_mobile', '', 'slide-navigation' );
        if ( ! empty( $space ) ) {
          $cssMobile[] = sprintf( '.fp-slidesNav.fp-bottom{bottom:%1$spx}.fp-slidesNav.fp-top{top:%1$spx}', $space );
        }
      }

      if ( $this->IsFieldEnabled( 'remove-theme-margins' ) ) {
        $css[] = '.fp-enabled .mcw_fp_nomargin,.fp-enabled .fp-section{margin:0 !important;width:100% !important;max-width:100% !important;border:none !important}.fp-enabled .mcw_fp_nomargin{padding:0 !important}';
      }

      if ( $this->IsFieldEnabled( 'fixed-theme-header' ) ) {
        $selector = $this->GetFieldValue( 'fixed-theme-header-selector', 'header' );
        $css[] = sprintf(
          '.fp-enabled %1$s{position:fixed!important;left:0!important;right:0!important;width:100%%!important;top:0!important;z-index:9999}.fp-enabled .admin-bar %1$s{top:32px!important}.fp-enabled .fp-section.fp-auto-height{padding-top:0!important}@media screen and (max-width:782px){.fp-enabled .admin-bar %1$s{top:46px!important}}',
          $selector
        );

        if ( $this->IsFieldEnabled( 'fixed-theme-header-toggle' ) ) {
          $css[] = sprintf(
            '.fp-enabled .fp-direction-last-down %1$s,.fp-enabled .fp-direction-last-up %1$s{transition:transform %2$sms ease}.fp-enabled %1$s{transform:translateY(0)}.fp-enabled .fp-direction-last-down %1$s{transform:translateY(-100%%)}',
            $selector,
            $this->GetFieldValue( 'scrolling-speed', '1000' )
          );
        }
      }

      if ( $this->IsFieldEnabled( 'enable-extensions' ) ) {
        $extensions = $this->GetExtensionsInfo();

        if ( $extensions['parallax']['active'] && $this->IsFieldEnabled( 'extension-parallax' ) ) {
          $easing = $this->GetFieldValue( 'easing-css', 'ease' );

          $css[] = sprintf(
            '.fp-enabled .mcw-fp-wrapper .fp-section, .fp-enabled .mcw-fp-wrapper .fp-slide{transition:background-position %sms %s !important;}',
            $this->GetFieldValue( 'scrolling-speed', '1000' ),
            $easing
          );
        }

        if ( $extensions['fading-effect']['active'] && $this->IsFieldEnabled( 'extension-fading-effect' ) ) {
          $easing = $this->GetFieldValue( 'easing-css', 'ease' );

          $css[] = sprintf(
            '.fp-enabled .mcw-fp-wrapper .fp-section, .fp-enabled .mcw-fp-wrapper .fp-slide{transition:all %sms %s !important;}',
            $this->GetFieldValue( 'scrolling-speed', '1000' ),
            $easing
          );
          $css[] = '.mcw-fp-section-slide > .elementor-container{height: 100%;}.mcw-fp-section-slide > .elementor-container .elementor-widget-wrap{height: 100%;}';
        }
      }

      if ( $this->IsFieldEnabled( 'control-arrows' ) ) {
        $color = $this->GetFieldValue( 'arrow-color-main', '#FFFFFF', 'control-arrows-options' );
        if ( $this->GetFieldValue( 'arrow-style', 'modern', 'control-arrows-options' ) === 'off' ) {
          $css[] = '.fp-controlArrow.fp-prev{border-right-color:' . $color . ';}.fp-controlArrow.fp-next{border-left-color:' . $color . ';}';
        }
      }

      if ( $this->IsFieldEnabled( 'hide-content-before-load' ) ) {
        $css[] = 'body{opacity: 0;transition: opacity 1s ease;margin-top:100vh}.fp-enabled body{opacity: 1;margin-top:0}';
      }

      if ( $this->IsFieldEnabled( 'custom-css-enable' ) ) {
        $css[] = trim( $this->GetFieldValue( 'custom-css', '' ) );
      }

      $css = implode( '', $css );
      if ( ! empty( $css ) ) {
        $post->get_stylesheet()->add_raw_css( $css );
      }

      $cssTablet = implode( '', $cssTablet );
      if ( ! empty( $cssTablet ) ) {
        $post->get_stylesheet()->add_raw_css( $cssTablet, 'tablet' );
      }

      $cssMobile = implode( '', $cssMobile );
      if ( ! empty( $cssMobile ) ) {
        $post->get_stylesheet()->add_raw_css( $cssMobile, 'mobile' );
      }
    }

    public function OnElementorContent( $content ) {
      if ( ! $this->IsFullPageEnabled() ) {
        return $content;
      }

      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return $content;
      }

      $content = sprintf(
        '<div class="%s">%s</div><script type="text/javascript">%s</script>',
        $this->wrapper,
        $content,
        $this->GetFullPageJS( $content )
      );

      return $content;
    }

    public function IsFullPageExtensionEnabled( $extension = false ) {
      if ( ! $this->IsFullPageEnabled() ) {
        return false;
      }

      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        return false;
      }

      $enabled = $this->IsFieldEnabled( 'enable-extensions' );

      if ( false === $extension ) {
        return $enabled;
      }

      if ( ! $enabled ) {
        return $enabled;
      }

      return $this->IsFieldEnabled( 'extension-' . $extension );
    }

    private function GetId( $suffix, $parent = null ) {
      $prefix = '';
      if ( $parent ) {
        $prefix = $this->tag . '-' . $parent . '_';
      }
      return $prefix . $this->tag . '-' . $suffix;
    }

    private function GetPageSettingsModel( $postId ) {
      $manager = \Elementor\Core\Settings\Manager::get_settings_managers( 'page' );
      if ( isset( $manager ) ) {
        return $manager->get_model( $postId );
      }

      return null;
    }

    // Return the field value of the specified id
    private function GetFieldValueBase( $id, $default = null, $parent = null ) {
      // Get the post ID
      $postId = get_the_ID();

      // Check the model is taken
      if ( ! ( isset( $this->pageSettingsModel[ $postId ] ) && $this->pageSettingsModel[ $postId ] ) ) {

        // Just a precaution
        if ( ! $postId ) {
          return $default;
        }

        $this->pageSettingsModel[ $postId ] = $this->GetPageSettingsModel( $postId );

        if ( ! ( isset( $this->pageSettingsModel[ $postId ] ) && $this->pageSettingsModel[ $postId ] ) ) {
          return $default;
        }
      }

      $val = $this->pageSettingsModel[ $postId ]->get_settings_for_display( $this->GetId( $id, $parent ) );

      if ( $parent ) {
        $types = array(
          'control-arrows-options' => 'group-section-control-arrows-type',
          'section-navigation' => 'group-section-nav-type',
          'slide-navigation' => 'group-slide-nav-type',
        );
        if ( array_key_exists( $parent, $types ) ) {
          $parentVal = $this->pageSettingsModel[ $postId ]->get_settings_for_display( $this->GetId( $types[ $parent ], $parent ) );
          if ( ! ( isset( $parentVal ) && ( 'yes' === $parentVal ) ) ) {
            $val = $default;
          }
        }
      }

      // Add filter
      if ( has_filter( $this->tag . '-field-' . $id ) ) {
        $val = apply_filters( $this->tag . '-field-' . $id, $val );
      }

      return $val;
    }

    private function GetFieldValue( $id, $default = null, $parent = null ) {
      $val = $this->GetFieldValueBase( $id, $default, $parent );
      // Return field value or default
      return ( empty( $val ) ? $default : $val );
    }

    private function GetSectionValueBase( $section, $id, $default = null ) {
      if ( isset( $section ) && $section ) {
        $val = $section->get_settings_for_display( $id );

        return ( empty( $val ) ? $default : $val );
      }

      return $default;
    }

    private function GetSectionValue( $section, $id, $default = null, $parent = null ) {
      if ( 'section-no-render' === $id || 'section-is-inner' === $id ) {
        $val = $section->get_settings_for_display( $this->GetId( $id ) );
        return $val === NULL ? true : $val;
      }

      $val = $this->GetSectionValueBase( $section, $this->GetId( $id, $parent ), $default );
      if ( $parent ) {
        $types = array(
          'section-element-navigation' => 'group-section-nav-colors',
          'section-element-navigation-tooltip' => 'group-section-nav-tooltip-colors',
        );
        if ( array_key_exists( $parent, $types ) ) {
          $parentVal = $this->GetSectionValueBase( $section, $this->GetId( $types[ $parent ], $parent ), $default );
          if ( ! ( isset( $parentVal ) && ( 'yes' === $parentVal ) ) ) {
            $val = $default;
          }
        }
      }

      return $val;
    }

    // Checks if specified setting is on (used for metabox checkboxes) and returns true or false
    private function IsFieldEnabled( $id, $parent = null ) {
      // Get field value
      $val = $this->GetFieldValue( $id, 'no', $parent );

      // Return true if field is on
      return isset( $val ) && ( 'yes' === $val );
    }

    private function IsFieldEnabledResponsive( $id, $parent = null, $default = 'no' ) {
      // Get field value
      $val = $this->GetFieldValue( $id, $default, $parent );

      $valueDesktop = isset( $val ) && ( 'yes' === $val );
      $valueMobile = $this->GetFieldValueBase( $id . '_mobile', null, $parent );
      $valueTablet = $this->GetFieldValueBase( $id . '_tablet', null, $parent );

      return array(
        'desktop' => $valueDesktop,
        'mobile' => ( ( ! isset( $valueMobile ) ) || ( null === $valueMobile ) ) ? $valueDesktop : ( 'yes' === $valueMobile ),
        'tablet' => ( ( ! isset( $valueTablet ) ) || ( null === $valueTablet ) ) ? $valueDesktop : ( 'yes' === $valueTablet ),
      );
    }

    private function IsSectionFieldEnabled( $section, $id, $parent = null, $default = 'no' ) {
      // Get field value
      $val = $this->GetSectionValue( $section, $id, $default, $parent );

      // Return true if field is on
      return isset( $val ) && ( 'yes' === $val );
    }

    private function IsSectionInner( $section ) {
      // Get inner sections
      $isInner = $section->get_data( 'isInner' ) || $this->GetSectionValue( $section, 'section-is-inner', false );
      return isset( $isInner ) && $isInner;
    }

    // Returns true if the specified field is on
    private function IsFieldOn( $id, $parent = null ) {
      return $this->IsFieldEnabled( $id, $parent ) ? 'true' : 'false';
    }

    private function MinimizeJavascriptSimple( $js ) {
      return preg_replace( array( '/\s+\n/', '/\n\s+/', '/ +/', '/\{\s+/', '/\s+=\s+/', '/\s*;\s*/' ), array( '', '', ' ', '{', '=', ';' ), $js );
    }

    private function MinimizeJavascriptAdvanced( $js, $level = 3 ) {
      if ( $level <= 0 ) {
        return $js;
      }

      if ( $level >= 1 ) {
        // Remove single-line code comments
        $js = preg_replace( '/^[\t ]*?\/\/.*\s?/m', '', $js );

        // Remove end-of-line code comments
        $js = preg_replace( '/([\s;})]+)\/\/.*/m', '\\1', $js );

        // Remove multi-line code comments
        $js = preg_replace( '/\/\*[\s\S]*?\*\//', '', $js );
      }

      if ( $level >= 2 ) {
        // Remove leading whitespace
        $js = preg_replace( '/^\s*/m', '', $js );

        // Replace multiple tabs with a single space
        $js = preg_replace( '/\t+/m', ' ', $js );
      }

      if ( $level >= 3 ) {
        // Remove newlines
        $js = preg_replace( '/[\r\n]+/', '', $js );
      }

      // Return final minified JavaScript
      return trim( $js );
    }

    private function IsFullPageEnabled( $check = true ) {
      if ( $check ) {
        $doc = \Elementor\Plugin::$instance->documents->get_current();
        if ( isset( $doc ) && ! empty( $doc ) && $doc->get_main_id() !== get_the_ID() ) {
          return false;
        }
      }

      return $this->IsFieldEnabled( 'enabled' );
    }

    private function GetSectionNavStyle() {
      if ( $this->GetFieldValue( 'nav', 'right', 'section-navigation' ) !== 'off' ) {
        return $this->GetFieldValue( 'nav-style', 'default', 'section-navigation' );
      }

      return false;
    }

    private function GetSlideNavStyle() {
      if ( $this->GetFieldValue( 'nav', 'off', 'slide-navigation' ) !== 'off' ) {
        return $this->GetFieldValue( 'nav-style', 'default', 'slide-navigation' );
      }

      return false;
    }

    private function AddSlideCSS( $post, $anchor, $section ) {
      if ( ! $section ) {
        return '';
      }

      $count = 0;
      foreach ( $section->get_children() as $child ) {
        if ( in_array( $child->get_data( 'elType' ), [ 'section', 'container' ] ) ) {
          if ( ! $this->GetSectionValue( $child, 'section-no-render', true ) ) {
            $isInner = $child->get_data( 'isInner' );
            $isInner = isset( $isInner ) && $isInner;

            if ( $isInner ) {
              if ( $this->IsSectionFieldEnabled( $child, 'group-section-nav-colors', 'section-element-navigation' ) ) {
                $slideAnchor = $this->GetSlideAnchor( $child );

                if ( ! $slideAnchor ) {
                  $slideAnchor = $count;
                }

                $css = $this->navigationCSS->GetCustomCSS(
                  $this->GetSlideNavStyle(),
                  "body[class*='fp-viewing-{$anchor}-{$slideAnchor}'] .fp-slidesNav",
                  $this->GetSectionValue( $child, 'color-main', '', 'section-element-navigation' ),
                  $this->GetSectionValue( $child, 'color-active', '', 'section-element-navigation' ),
                  $this->GetSectionValue( $child, 'color-hover', '', 'section-element-navigation' )
                );

                $cssTablet = $this->navigationCSS->GetCustomCSS(
                  $this->GetSlideNavStyle(),
                  "body[class*='fp-viewing-{$anchor}-{$slideAnchor}'] .fp-slidesNav",
                  $this->GetSectionValue( $child, 'color-main_tablet', '', 'section-element-navigation' ),
                  $this->GetSectionValue( $child, 'color-active_tablet', '', 'section-element-navigation' ),
                  $this->GetSectionValue( $child, 'color-hover_tablet', '', 'section-element-navigation' )
                );

                $cssMobile = $this->navigationCSS->GetCustomCSS(
                  $this->GetSlideNavStyle(),
                  "body[class*='fp-viewing-{$anchor}-{$slideAnchor}'] .fp-slidesNav",
                  $this->GetSectionValue( $child, 'color-main_mobile', '', 'section-element-navigation' ),
                  $this->GetSectionValue( $child, 'color-active_mobile', '', 'section-element-navigation' ),
                  $this->GetSectionValue( $child, 'color-hover_mobile', '', 'section-element-navigation' )
                );

                $post->get_stylesheet()->add_raw_css( $css );
                $post->get_stylesheet()->add_raw_css( $cssTablet, 'tablet' );
                $post->get_stylesheet()->add_raw_css( $cssMobile, 'mobile' );
              }

              $count++;

              continue;
            }
          }
        }

        $this->AddSlideCSS( $post, $anchor, $child );
      }
    }

    private function GetAnchor( $section ) {
      $anchor = trim( $this->GetSectionValue( $section, 'section-anchor', '' ) );
      if ( isset( $anchor ) && ! empty( $anchor ) ) {
        return preg_replace( '/\s+/', '_', $anchor );
      }

      return 'section-' . $section->get_id();
    }

    private function GetSlideAnchor( $section ) {
      $anchor = trim( $this->GetSectionValue( $section, 'slide-anchor', '' ) );
      if ( isset( $anchor ) && ! empty( $anchor ) ) {
        return preg_replace( '/\s+/', '_', $anchor );
      }

      return false;
    }

    private function GetTemplatePath( $redirect ) {
      // Get post
      global $post;
      if ( ! ( isset( $post ) && is_object( $post ) ) ) {
        return false;
      }

      // Check if fullpage is enabled
      if ( ! $this->IsFullPageEnabled() ) {
        return false;
      }

      // Check if template is enabled
      if ( ! $this->IsFieldEnabled( 'enable-template' ) ) {
        return false;
      }

      if ( $redirect ) {
        // Check if template redirect is enabled
        if ( ! $this->IsFieldEnabled( 'enable-template-redirect' ) ) {
          return false;
        }
      } else {
        if ( $this->IsFieldEnabled( 'enable-template-redirect' ) ) {
          return false;
        }
      }

      $path = trim( $this->GetFieldValue( 'template-path' ) );
      if ( '' === $path ) {
        $path = plugin_dir_path( dirname( __FILE__ ) ) . 'template/template.php';
      }

      // Add filter
      if ( has_filter( $this->tag . '-template' ) ) {
        $path = apply_filters( $this->tag . '-template', $path );
      }

      if ( empty( $path ) ) {
        return false;
      }

      return $path;
    }

    private function GetNotInstalledExtensionHtml( $label, $text ) {
      return sprintf(
        '<div class="elementor-control-field mcw-fullpage-elementor-disabled">
          <div class="elementor-control-title">%s</div>
          <div class="elementor-control-input-wrapper">%s</div>
        </div>',
        $label,
        $text
      );
    }

    private function GetExtensionsInfo( $extension = '' ) {
      if ( ! $this->extensions ) {
        $this->extensions = McwFullPageElementorGlobals::GetExtensions();
      }

      if ( empty( $extension ) ) {
        return $this->extensions;
      }

      return $this->extensions[ $extension ];
    }

    private function GetExtensionKey( $extension ) {
      return $extension['key'];
    }

    private function IsBuilderEnabled() {
      return \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode();
    }

    private function AddSectionTab( $page ) {
      $page->start_controls_section(
        $this->GetId( 'tab-fullpage-section' ),
        array(
          'label' => esc_html__( 'FullPage', 'mcw-fullpage-elementor' ),
          'tab' => ControlsManager::TAB_LAYOUT,
          'condition' => array(
            $this->GetId( 'section-no-render' ) => false,
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'section-no-render' ),
        array(
          'type' => ControlsManager::HIDDEN,
          'default' => true,
        )
      );

      $page->add_control(
        $this->GetId( 'section-is-inner' ),
        array(
          'type' => ControlsManager::HIDDEN,
          'default' => false,
        )
      );

      $page->add_control(
        $this->GetId( 'enable-data-percentage' ),
        array(
          'type' => ControlsManager::HIDDEN,
          'default' => false,
        )
      );

      $page->add_control(
        $this->GetId( 'enable-data-drop' ),
        array(
          'type' => ControlsManager::HIDDEN,
          'default' => false,
        )
      );

      $page->add_control(
        $this->GetId( 'enable-data-water' ),
        array(
          'type' => ControlsManager::HIDDEN,
          'default' => false,
        )
      );

      $page->add_control(
        $this->GetId( 'section-is-slide' ),
        array(
          'label' => esc_html__( 'Has Horizontal Slides?', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Converts section to slide section when enabled.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'section-is-inner' ) => false,
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'section-behaviour' ),
        array(
          'label' => esc_html__( 'Section Behaviour', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SELECT,
          'options' => array(
            'full' => esc_html__( 'Full Height', 'mcw-fullpage-elementor' ),
            'auto' => esc_html__( 'Auto Height', 'mcw-fullpage-elementor' ),
            'responsive' => esc_html__( 'Responsive Auto Height', 'mcw-fullpage-elementor' ),
          ),
          'default' => 'full',
          'description' => esc_html__( 'Defines the section behaviour.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'section-is-inner' ) => false,
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'section-anchor' ),
        array(
          'label' => esc_html__( 'FullPage Anchor', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::TEXT,
          'default' => '',
          'description' => esc_html__( 'Defines the fullpage anchor. This option should be unique in the page and different from CSS ID option.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'section-is-inner' ) => false,
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'slide-anchor' ),
        array(
          'label' => esc_html__( 'Slide Anchor', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::TEXT,
          'default' => '',
          'description' => esc_html__( 'Defines the fullpage slide anchor. This option should be unique in the page and different from CSS ID option.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'section-is-inner' ) => true,
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'section-nav-tooltip' ),
        array(
          'label' => esc_html__( 'Navigation Tooltip', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::TEXT,
          'default' => '',
          'description' => esc_html__( 'Defines the navigation bullet tooltip text.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'section-is-inner' ) => false,
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'section-scrollbars' ),
        array(
          'label' => esc_html__( 'Disable Scroll Overflow', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'When enabled, scroll overflow is disabled for this section.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'section-is-inner' ) => false,
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'section-full-height-col' ),
        array(
          'label' => esc_html__( 'Full Height Columns', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Sets the column height as full height.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'data-percentage' ),
        array(
          'label' => esc_html__( 'Data Percentage', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::NUMBER,
          'min' => 0,
          'max' => 100,
          'step' => 1,
          'default' => '100',
          'description' => esc_html__( 'The data percentage for Offset Sections extension.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'section-is-inner' ) => false,
            $this->GetId( 'enable-data-percentage' ) => true,
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'data-centered' ),
        array(
          'label' => esc_html__( 'Data Centered', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SELECT,
          'options' => array(
            'yes'  => esc_html__( 'Yes', 'mcw-fullpage-elementor' ),
            'no'  => esc_html__( 'No', 'mcw-fullpage-elementor' ),
            'top' => esc_html__( 'Top', 'mcw-fullpage-elementor' ),
            'bottom'  => esc_html__( 'Bottom', 'mcw-fullpage-elementor' ),
          ),
          'default' => 'yes',
          'description' => esc_html__( 'The data centered for Offset Sections extension.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'section-is-inner' ) => false,
            $this->GetId( 'enable-data-percentage' ) => true,
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'section-data-drop' ),
        array(
          'label' => esc_html__( 'Drop Effect Target', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SELECT,
          'options' => array(
            'all' => esc_html__( 'All', 'mcw-fullpage-elementor' ),
            'up' => esc_html__( 'Up', 'mcw-fullpage-elementor' ),
            'down' => esc_html__( 'Down', 'mcw-fullpage-elementor' ),
          ),
          'default' => 'all',
          'description' => esc_html__( 'Apply Drop Effect only on certain sections or directions.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'section-is-inner' ) => false,
            $this->GetId( 'enable-data-drop' ) => true,
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'section-data-water' ),
        array(
          'label' => esc_html__( 'Water Effect Target', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SELECT,
          'options' => array(
            'all' => esc_html__( 'All', 'mcw-fullpage-elementor' ),
            'up' => esc_html__( 'Up', 'mcw-fullpage-elementor' ),
            'down' => esc_html__( 'Down', 'mcw-fullpage-elementor' ),
          ),
          'default' => 'all',
          'description' => esc_html__( 'Apply Water Effect only on certain sections or directions.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'section-is-inner' ) => false,
            $this->GetId( 'enable-data-water' ) => true,
          ),
        )
      );

      $page->add_group_control(
        McwFullPageElementorNavColorsControl::get_type(),
        array(
          'name' => $this->GetId( 'section-element-navigation' ),
          'label' => esc_html__( 'Navigation Colors', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_group_control(
        McwFullPageElementorNavTooltipColorsControl::get_type(),
        array(
          'name' => $this->GetId( 'section-element-navigation-tooltip' ),
          'label' => esc_html__( 'Tooltip Colors', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'section-is-inner' ) => false,
            $this->GetId( 'section-nav-tooltip' ) . '!' => '',
          ),
        )
      );

      $page->end_controls_section();
    }

    private function AddFullPageTab( $page ) {
      $page->start_controls_section(
        $this->GetId( 'tab-activate' ),
        array(
          'label' => esc_html__( 'FullPage', 'mcw-fullpage-elementor' ),
          'tab' => $this->tab,
        )
      );

      if ( ! ( $this->pluginSettings->GetLicenseKey() && $this->pluginSettings->GetLicenseState() ) ) {
        $page->add_control(
          $this->GetId( 'enabled-disabled' ),
          array(
            'label' => $this->GetNotInstalledExtensionHtml( esc_html__( 'Enable FullPage', 'mcw-fullpage-elementor' ), esc_html__( 'Not Activated', 'mcw-fullpage-elementor' ) ),
            'type' => ControlsManager::RAW_HTML,
          )
        );
      } else {
        $page->add_control(
          $this->GetId( 'enabled' ),
          array(
            'label' => esc_html__( 'Enable FullPage', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'no',
            'label_on' => esc_html__( 'Yes', 'mcw-fullpage-elementor' ),
            'label_off' => esc_html__( 'No', 'mcw-fullpage-elementor' ),
            'return_value' => 'yes',
          )
        );

        $page->add_control(
          'update-page',
          array(
            'label' => '<div class="elementor-update-preview mcw-fullpage-elementor-update-button"><div class="elementor-update-preview-button-wrapper"><button class="elementor-update-preview-button elementor-button elementor-button-success">' . esc_html__( 'Update Preview', 'mcw-fullpage-elementor' ) . '</button></div></div>',
            'type' => ControlsManager::RAW_HTML,
          )
        );

        $page->add_control(
          $this->GetId( 'help' ),
          array(
            'label' => false,
            'type' => ControlsManager::RAW_HTML,
            'raw' => '<div class="mcw-fullpage-elementor-help"><a class="elementor-panel__editor__help__link" href="https://www.meceware.com/docs/fullpage-for-elementor/" target="_blank">
              ' . esc_html__( 'Need Help', 'mcw-fullpage-elementor' ) . '<i class="eicon-help-o"></i></a>',
          )
        );
      }

      $page->end_controls_section();
    }

    private function AddNavigationTab( $page ) {
      $page->start_controls_section(
        $this->GetId( 'tab-navigation' ),
        array(
          'label' => esc_html__( 'Navigation', 'mcw-fullpage-elementor' ),
          'tab' => $this->tab,
          'condition' => array( $this->GetId( 'enabled' ) => 'yes' ),
        )
      );

      $page->add_group_control(
        McwFullPageElementorNavSectionControl::get_type(),
        array(
          'name' => $this->GetId( 'section-navigation' ),
          'label' => esc_html__( 'Section Navigation', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_group_control(
        McwFullPageElementorNavSlideControl::get_type(),
        array(
          'name' => $this->GetId( 'slide-navigation' ),
          'label' => esc_html__( 'Slide Navigation', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'control-arrows' ),
        array(
          'label' => esc_html__( 'Control Arrows', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'yes',
          'description' => esc_html__( 'Determines whether to use control arrows for the slides to move right or left.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_group_control(
        McwFullPageElementorControlArrowsControl::get_type(),
        array(
          'name' => $this->GetId( 'control-arrows-options' ),
          'label' => esc_html__( 'Control Arrows Options', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'control-arrows' ) => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'lock-anchors' ),
        array(
          'label' => esc_html__( 'Lock Anchors', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Determines whether anchors in the URL will have any effect.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'disable-anchors' ),
        array(
          'label' => esc_html__( 'Disable Anchors', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Determines whether to enable or disable all section anchors.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'animate-anchor' ),
        array(
          'label' => esc_html__( 'Animate Anchor', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'yes',
          'description' => esc_html__( 'Defines whether the load of the site when given anchor (#) will scroll with animation to its destination.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'keyboard-scrolling' ),
        array(
          'label' => esc_html__( 'Keyboard Scrolling', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'yes',
          'description' => esc_html__( 'Defines if the content can be navigated using the keyboard.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'record-history' ),
        array(
          'label' => esc_html__( 'Record History', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'yes',
          'description' => esc_html__( 'Defines whether to push the state of the site to the browsers history, so back button will work on section navigation.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->end_controls_section();
    }

    private function AddScrollingTab( $page ) {
      $page->start_controls_section(
        $this->GetId( 'tab-scrolling' ),
        array(
          'label' => esc_html__( 'Scrolling', 'mcw-fullpage-elementor' ),
          'tab' => $this->tab,
          'condition' => array( $this->GetId( 'enabled' ) => 'yes' ),
        )
      );

      $page->add_control(
        $this->GetId( 'auto-scrolling' ),
        array(
          'label' => esc_html__( 'Auto Scrolling', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'yes',
          'description' => esc_html__( 'Defines whether to use the automatic scrolling or the normal one.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'fit-to-section' ),
        array(
          'label' => esc_html__( 'Fit To Section', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'yes',
          'description' => esc_html__( 'Determines whether or not to fit sections to the viewport or not.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'fit-to-section-delay' ),
        array(
          'label' => esc_html__( 'Fit To Section Delay', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::NUMBER,
          'min' => 0,
          'step' => 100,
          'default' => '1000',
          'description' => esc_html__( 'The delay in miliseconds for section fitting.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'fit-to-section' ) => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'scroll-bar' ),
        array(
          'label' => esc_html__( 'Scroll Bar', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Determines whether to use the scrollbar for the site or not.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'scroll-overflow' ),
        array(
          'label' => esc_html__( 'Scroll Overflow', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'yes',
          'condition' => array( $this->GetId( 'scroll-bar' ) . '!' => 'yes' ),
          'description' => esc_html__( 'Defines whether or not to create a scroll for the section in case the content is bigger than the height of it. (Disabled when Scrollbars are enabled.)', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'scroll-overflow-mac-style' ),
        array(
          'label' => esc_html__( 'Mac Style Scroll Overflow', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'yes',
          'description' => esc_html__( 'Use a "mac style" for the scrollbar instead of the default one', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'scroll-bar' ) . '!' => 'yes',
            $this->GetId( 'scroll-overflow' ) => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'continuous-vertical' ),
        array(
          'label' => esc_html__( 'Continuous Vertical', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Determines vertical scrolling is continuous.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'scroll-bar' ) . '!' => 'yes',
            $this->GetId( 'auto-scrolling' ) => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'loop-bottom' ),
        array(
          'label' => esc_html__( 'Loop Bottom', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Defines whether scrolling down in the last section should scroll to the first one or not.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'scroll-bar' ) . '!' => 'yes',
            $this->GetId( 'auto-scrolling' ) => 'yes',
            $this->GetId( 'continuous-vertical' ) . '!' => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'loop-top' ),
        array(
          'label' => esc_html__( 'Loop Top', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Defines whether scrolling up in the first section should scroll to the last one or not.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'scroll-bar' ) . '!' => 'yes',
            $this->GetId( 'auto-scrolling' ) => 'yes',
            $this->GetId( 'continuous-vertical' ) . '!' => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'loop-slides' ),
        array(
          'label' => esc_html__( 'Loop Slides', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'yes',
          'description' => esc_html__( 'Defines whether horizontal sliders will loop after reaching the last or previous slide or not.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'big-sections-destination' ),
        array(
          'label' => esc_html__( 'Big Sections Destination', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SELECT,
          'options' => array(
            'default' => esc_html__( 'Default', 'mcw-fullpage-elementor' ),
            'top' => esc_html__( 'Top', 'mcw-fullpage-elementor' ),
            'bottom' => esc_html__( 'Bottom', 'mcw-fullpage-elementor' ),
          ),
          'default' => 'default',
          'description' => esc_html__( 'Defines how to scroll to a section which size is bigger than the viewport.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'css-easing-enable' ),
        array(
          'label' => esc_html__( 'CSS Easing', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'yes',
          'description' => esc_html__( 'Defines whether to use CSS easing or not. If disabled JavaScript easing is used.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'easing-css' ),
        array(
          'label' => esc_html__( 'Easing', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SELECT,
          'options' => array(
            'ease' => esc_html__( 'Ease', 'mcw-fullpage-elementor' ),
            'linear' => esc_html__( 'Linear', 'mcw-fullpage-elementor' ),
            'ease-in' => esc_html__( 'Ease In', 'mcw-fullpage-elementor' ),
            'ease-out' => esc_html__( 'Ease Out', 'mcw-fullpage-elementor' ),
            'ease-in-out' => esc_html__( 'Ease In Out', 'mcw-fullpage-elementor' ),
          ),
          'default' => 'ease',
          'description' => esc_html__( 'Defines the scrolling transition animation.)', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'css-easing-enable' ) => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'easing-js' ),
        array(
          'label' => esc_html__( 'Easing', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SELECT,
          'options' => array(
            'linear' => esc_html__( 'Linear', 'mcw-fullpage-elementor' ),
            'swing' => esc_html__( 'Swing', 'mcw-fullpage-elementor' ),
            'easeInQuad' => esc_html__( 'Ease In Quad', 'mcw-fullpage-elementor' ),
            'easeOutQuad' => esc_html__( 'Ease Out Quad', 'mcw-fullpage-elementor' ),
            'easeInOutQuad' => esc_html__( 'Ease In Out Quad', 'mcw-fullpage-elementor' ),
          ),
          'default' => 'linear',
          'description' => esc_html__( 'Defines the JS scrolling transition animation.)', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'css-easing-enable' ) . '!' => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'scrolling-speed' ),
        array(
          'label' => esc_html__( 'Scrolling Speed', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::NUMBER,
          'min' => 0,
          'step' => 10,
          'default' => '1000',
          'description' => esc_html__( 'Speed in miliseconds for the scrolling transitions.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->end_controls_section();
    }

    private function AddDesignTab( $page ) {
      $page->start_controls_section(
        $this->GetId( 'tab-design' ),
        array(
          'label' => esc_html__( 'Design', 'mcw-fullpage-elementor' ),
          'tab' => $this->tab,
          'condition' => array( $this->GetId( 'enabled' ) => 'yes' ),
        )
      );

      $page->add_control(
        $this->GetId( 'vertical-alignment' ),
        array(
          'label' => esc_html__( 'Vertical Alignment', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SELECT,
          'options' => array(
            'default' => esc_html__( 'Default', 'mcw-fullpage-elementor' ),
            'center' => esc_html__( 'Center', 'mcw-fullpage-elementor' ),
          ),
          'default' => 'center',
          'description' => esc_html__( 'Determines the default position of the content vertically in the section.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'responsive-width' ),
        array(
          'label' => esc_html__( 'Responsive Width', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::NUMBER,
          'min' => 0,
          'step' => 1,
          'default' => '0',
          'description' => esc_html__( 'Normal scroll will be used under the defined width in pixels. (autoScrolling: false)', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'responsive-height' ),
        array(
          'label' => esc_html__( 'Responsive Height', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::NUMBER,
          'min' => 0,
          'step' => 1,
          'default' => '0',
          'description' => esc_html__( 'Normal scroll will be used under the defined height in pixels. (autoScrolling: false)', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'fixed-elements' ),
        array(
          'label' => esc_html__( 'Fixed Elements', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::TEXT,
          'default' => '',
          'description' => esc_html__( 'Defines which elements will be taken off the scrolling structure of the plugin which is necessary when using the keep elements fixed with css. Enter comma seperated element selectors. (example: #element1, .element2)', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'normal-scroll-elements' ),
        array(
          'label' => esc_html__( 'Normal Scroll Elements', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::TEXT,
          'default' => '',
          'description' => esc_html__( 'If you want to avoid the auto scroll when scrolling over some elements, this is the option you need to use. (useful for maps, scrolling divs etc.) Enter comma seperated element selectors. (example: #element1, .element2)', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'custom-css-enable' ),
        array(
          'label' => esc_html__( 'Custom CSS', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'yes',
        )
      );

      $page->add_control(
        $this->GetId( 'custom-css' ),
        array(
          'type' => ControlsManager::CODE,
          'language' => 'css',
          'seperator' => 'none',
          'show_label' => false,
          'default' => '',
          'condition' => array( $this->GetId( 'custom-css-enable' ) => 'yes' ),
        )
      );

      $page->end_controls_section();
    }

    private function AddEventsTab( $page ) {
      $page->start_controls_section(
        $this->GetId( 'tab-events' ),
        array(
          'label' => esc_html__( 'Events', 'mcw-fullpage-elementor' ),
          'tab' => $this->tab,
          'condition' => array( $this->GetId( 'enabled' ) => 'yes' ),
        )
      );

      $events = array(
        'afterRender' => array(
          'enable' => 'after-render-enable',
          'field' => 'after-render',
          'description' => esc_html__( 'Fired just after the structure of the page is generated.', 'mcw-fullpage-elementor' ),
        ),
        'afterResize' => array(
          'enable' => 'after-resize-enable',
          'field' => 'after-resize',
          'description' => esc_html__( 'Fired after resizing the browsers window.', 'mcw-fullpage-elementor' ),
        ),
        'afterLoad' => array(
          'enable' => 'after-load-enable',
          'field' => 'after-load',
          'description' => esc_html__( 'Fired once the sections have been loaded, after the scrolling has ended.', 'mcw-fullpage-elementor' ),
        ),
        'beforeLeave' => array(
          'enable' => 'before-leave-enable',
          'field' => 'before-leave',
          'description' => esc_html__( 'Fired before the user leaves a section.', 'mcw-fullpage-elementor' ),
        ),
        'onLeave' => array(
          'enable' => 'on-leave-enable',
          'field' => 'on-leave',
          'description' => esc_html__( 'Fired once the user leaves a section.', 'mcw-fullpage-elementor' ),
        ),
        'afterSlideLoad' => array(
          'enable' => 'after-slide-load-enable',
          'field' => 'after-slide-load',
          'description' => esc_html__( 'Fired once the slide of a section has been loaded, after the scrolling has ended.', 'mcw-fullpage-elementor' ),
        ),
        'onSlideLeave' => array(
          'enable' => 'on-slide-leave-enable',
          'field' => 'on-slide-leave',
          'description' => esc_html__( 'Fired once the user leaves a slide to go another.', 'mcw-fullpage-elementor' ),
        ),
        'afterResponsive' => array(
          'enable' => 'after-responsive-enable',
          'field' => 'after-responsive',
          'description' => esc_html__( 'Fired after normal mode is changed to responsive mode or responsive mode is changed to normal mode.', 'mcw-fullpage-elementor' ),
        ),
        'afterReBuild' => array(
          'enable' => 'after-rebuild-enable',
          'field' => 'after-rebuild',
          'description' => esc_html__( 'Fired after manually re-building fullpage.js.', 'mcw-fullpage-elementor' ),
        ),
        'onScrollOverflow' => array(
          'enable' => 'on-scroll-overflow-enable',
          'field' => 'on-scroll-overflow',
          'description' => esc_html__( 'Fired when scrolling inside a scrollable section.', 'mcw-fullpage-elementor' ),
        ),
        'Before FullPage' => array(
          'enable' => 'before-fullpage-enable',
          'field' => 'before-fullpage',
          'description' => esc_html__( 'The javascript code that runs right after document is ready and before fullpage is called.', 'mcw-fullpage-elementor' ),
        ),
        'After FullPage' => array(
          'enable' => 'after-fullpage-enable',
          'field' => 'after-fullpage',
          'description' => esc_html__( 'The javascript code that runs right after document is ready and after fullpage is called.', 'mcw-fullpage-elementor' ),
        ),
      );

      foreach ( $events as $name => $event ) {
        $page->add_control(
          $this->GetId( $event['enable'] ),
          array(
            'label' => $name,
            'type' => ControlsManager::SWITCHER,
            'default' => 'no',
          )
        );

        $page->add_control(
          $this->GetId( $event['field'] ),
          array(
            'type' => ControlsManager::CODE,
            'language' => 'javascript',
            'seperator' => 'none',
            'show_label' => false,
            'condition' => array( $this->GetId( $event['enable'] ) => 'yes' ),
            'description' => $event['description'],
          )
        );
      }

      $page->end_controls_section();
    }

    private function AddExtensionsTab( $page ) {
      $page->start_controls_section(
        $this->GetId( 'tab-extensions' ),
        array(
          'label' => esc_html__( 'Extensions', 'mcw-fullpage-elementor' ),
          'tab' => $this->tab,
          'condition' => array( $this->GetId( 'enabled' ) => 'yes' ),
        )
      );

      $page->add_control(
        $this->GetId( 'enable-extensions' ),
        array(
          'label' => esc_html__( 'Enable FullPage Extensions', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
        )
      );

      $extensions = $this->GetExtensionsInfo();

      // Cards extension
      if ( false && $extensions['cards']['active'] ) {
        $page->add_control(
          $this->GetId( 'extension-cards' ),
          array(
            'label' => esc_html__( 'Cards', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'no',
            'separator' => 'before',
            'description' => esc_html__( 'Enables fullpage Cards extension.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );

        $page->add_control(
          $this->GetId( 'extension-cards-perspective' ),
          array(
            'label' => esc_html__( 'Perspective', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::NUMBER,
            'min' => 0,
            'step' => 1,
            'default' => 100,
            'description' => esc_html__( 'Sets fullpage Cards extension perspective option.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
              $this->GetId( 'extension-cards' ) => 'yes',
            ),
          )
        );

        $page->add_control(
          $this->GetId( 'extension-cards-fading-content' ),
          array(
            'label' => esc_html__( 'Fading Content', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'yes',
            'description' => esc_html__( 'Enables fullpage Cards extension fadingContent option.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
              $this->GetId( 'extension-cards' ) => 'yes',
            ),
          )
        );

        $page->add_control(
          $this->GetId( 'extension-cards-fading-background' ),
          array(
            'label' => esc_html__( 'Fading Background', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'yes',
            'description' => esc_html__( 'Enables fullpage Cards extension fadingBackground option.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
              $this->GetId( 'extension-cards' ) => 'yes',
            ),
          )
        );
      } elseif ( false ) {
        $page->add_control(
          $this->GetId( 'extension-cards-disabled' ),
          array(
            'label' => $this->GetNotInstalledExtensionHtml( esc_html__( 'Cards', 'mcw-fullpage-elementor' ), esc_html__( 'Not Installed', 'mcw-fullpage-elementor' ) ),
            'type' => ControlsManager::RAW_HTML,
            'separator' => 'before',
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      }

      // Continuous Horizontal extension
      if ( $extensions['continuous-horizontal']['active'] ) {
        $page->add_control(
          $this->GetId( 'extension-continuous-horizontal' ),
          array(
            'label' => esc_html__( 'Continuous Horizontal', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'no',
            'separator' => 'before',
            'description' => esc_html__( 'Enables fullpage Continuous Horizontal extension.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      } else {
        $page->add_control(
          $this->GetId( 'extension-continuous-horizontal-disabled' ),
          array(
            'label' => $this->GetNotInstalledExtensionHtml( esc_html__( 'Continuous Horizontal', 'mcw-fullpage-elementor' ), esc_html__( 'Not Installed', 'mcw-fullpage-elementor' ) ),
            'type' => ControlsManager::RAW_HTML,
            'separator' => 'before',
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      }

      // Drag And Move extension
      if ( $extensions['drag-and-move']['active'] ) {
        $page->add_control(
          $this->GetId( 'extension-drag-and-move' ),
          array(
            'label' => esc_html__( 'Drag And Move', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'no',
            'separator' => 'before',
            'description' => esc_html__( 'Enables fullpage Drag And Move extension.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );

        $page->add_control(
          $this->GetId( 'extension-drag-and-move-target' ),
          array(
            'label' => esc_html__( 'Drag And Move Target', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SELECT,
            'label_block' => true,
            'options' => array(
              'off' => esc_html__( 'Default', 'mcw-fullpage-elementor' ),
              'vertical' => esc_html__( 'Vertical', 'mcw-fullpage-elementor' ),
              'horizontal' => esc_html__( 'Horizontal', 'mcw-fullpage-elementor' ),
              'fingersonly' => esc_html__( 'Fingers Only', 'mcw-fullpage-elementor' ),
              'mouseonly' => esc_html__( 'Mouse Only', 'mcw-fullpage-elementor' ),
            ),
            'default' => 'off',
            'description' => esc_html__( 'Defines Drag And Move extension target.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
              $this->GetId( 'extension-drag-and-move' ) => 'yes',
            ),
          )
        );
      } else {
        $page->add_control(
          $this->GetId( 'extension-drag-and-move-disabled' ),
          array(
            'label' => $this->GetNotInstalledExtensionHtml( esc_html__( 'Drag And Move', 'mcw-fullpage-elementor' ), esc_html__( 'Not Installed', 'mcw-fullpage-elementor' ) ),
            'type' => ControlsManager::RAW_HTML,
            'separator' => 'before',
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      }

      // Drop Effect extension
      if ( $extensions['drop-effect']['active'] ) {
        $page->add_control(
          $this->GetId( 'extension-drop-effect' ),
          array(
            'label' => esc_html__( 'Drop Effect', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'no',
            'separator' => 'before',
            'description' => esc_html__( 'Enables fullpage Drop Effect extension.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );

        $page->add_control(
          $this->GetId( 'extension-drop-effect-target' ),
          array(
            'label' => esc_html__( 'Target', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SELECT,
            'label_block' => true,
            'options' => array(
              'off' => esc_html__( 'Default', 'mcw-fullpage-elementor' ),
              'sections' => esc_html__( 'Sections', 'mcw-fullpage-elementor' ),
              'slides' => esc_html__( 'Slides', 'mcw-fullpage-elementor' ),
            ),
            'default' => 'off',
            'description' => esc_html__( 'Defines Drop Effect extension target.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
              $this->GetId( 'extension-drop-effect' ) => 'yes',
            ),
          )
        );

        $page->add_control(
          $this->GetId( 'extension-drop-effect-speed' ),
          array(
            'label' => esc_html__( 'Speed', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::NUMBER,
            'min' => 10,
            'max' => 10000,
            'step' => 10,
            'default' => 2300,
            'description' => esc_html__( 'Defines the speed in milliseconds for the drop animation effect since beginning to end.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
              $this->GetId( 'extension-drop-effect' ) => 'yes',
            ),
          )
        );

        $page->add_control(
          $this->GetId( 'extension-drop-effect-color' ),
          array(
            'label' => esc_html__( 'Color', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::COLOR,
            'alpha' => false,
            'default' => '#F82F4D',
            'description' => esc_html__( 'Defines color of the drop effect.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
              $this->GetId( 'extension-drop-effect' ) => 'yes',
            ),
          )
        );

        $page->add_control(
          $this->GetId( 'extension-drop-effect-zindex' ),
          array(
            'label' => esc_html__( 'z-Index', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::NUMBER,
            'min' => 99,
            'max' => 1000000,
            'step' => 1,
            'default' => 9999,
            'description' => esc_html__( 'Defines value assigned to the z-index property for the drop effect.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
              $this->GetId( 'extension-drop-effect' ) => 'yes',
            ),
          )
        );
      } else {
        $page->add_control(
          $this->GetId( 'extension-drop-effect-disabled' ),
          array(
            'label' => $this->GetNotInstalledExtensionHtml( esc_html__( 'Drop Effect', 'mcw-fullpage-elementor' ), esc_html__( 'Not Installed', 'mcw-fullpage-elementor' ) ),
            'type' => ControlsManager::RAW_HTML,
            'separator' => 'before',
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      }

      // Fading Effect extension
      if ( $extensions['fading-effect']['active'] ) {
        $page->add_control(
          $this->GetId( 'extension-fading-effect' ),
          array(
            'label' => esc_html__( 'Fading Effect', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'no',
            'separator' => 'before',
            'description' => esc_html__( 'Enables fullpage Fading Effect extension.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );

        $page->add_control(
          $this->GetId( 'extension-fading-effect-target' ),
          array(
            'label' => esc_html__( 'Fading Effect Target', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SELECT,
            'label_block' => true,
            'options' => array(
              'off' => esc_html__( 'Default', 'mcw-fullpage-elementor' ),
              'sections' => esc_html__( 'Sections', 'mcw-fullpage-elementor' ),
              'slides' => esc_html__( 'Slides', 'mcw-fullpage-elementor' ),
            ),
            'default' => 'off',
            'description' => esc_html__( 'Defines Fading Effect extension target.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
              $this->GetId( 'extension-fading-effect' ) => 'yes',
            ),
          )
        );
      } else {
        $page->add_control(
          $this->GetId( 'extension-fading-effect-disabled' ),
          array(
            'label' => $this->GetNotInstalledExtensionHtml( esc_html__( 'Fading Effect', 'mcw-fullpage-elementor' ), esc_html__( 'Not Installed', 'mcw-fullpage-elementor' ) ),
            'type' => ControlsManager::RAW_HTML,
            'separator' => 'before',
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      }

      // Interlocked Slides extension
      if ( $extensions['interlocked-slides']['active'] ) {
        $page->add_control(
          $this->GetId( 'extension-interlocked-slides' ),
          array(
            'label' => esc_html__( 'Interlocked Slides', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'no',
            'separator' => 'before',
            'description' => esc_html__( 'Enables fullpage Interlocked Slides extension.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      } else {
        $page->add_control(
          $this->GetId( 'extension-interlocked-slides-disabled' ),
          array(
            'label' => $this->GetNotInstalledExtensionHtml( esc_html__( 'Interlocked Slides', 'mcw-fullpage-elementor' ), esc_html__( 'Not Installed', 'mcw-fullpage-elementor' ) ),
            'type' => ControlsManager::RAW_HTML,
            'separator' => 'before',
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      }

      // Offset Sections extension
      if ( $extensions['offset-sections']['active'] ) {
        $page->add_control(
          $this->GetId( 'extension-offset-sections' ),
          array(
            'label' => esc_html__( 'Offset Sections', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'no',
            'separator' => 'before',
            'description' => esc_html__( 'Enables fullpage Offset Sections extension.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      } else {
        $page->add_control(
          $this->GetId( 'extension-offset-sections-disabled' ),
          array(
            'label' => $this->GetNotInstalledExtensionHtml( esc_html__( 'Offset Sections', 'mcw-fullpage-elementor' ), esc_html__( 'Not Installed', 'mcw-fullpage-elementor' ) ),
            'type' => ControlsManager::RAW_HTML,
            'separator' => 'before',
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      }

      // Parallax extension
      if ( $extensions['parallax']['active'] ) {
        $page->add_control(
          $this->GetId( 'extension-parallax' ),
          array(
            'label' => esc_html__( 'Parallax', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'no',
            'separator' => 'before',
            'description' => esc_html__( 'Enables fullpage Parallax extension.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );

        $page->add_control(
          $this->GetId( 'extension-parallax-target' ),
          array(
            'label' => esc_html__( 'Parallax Target', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SELECT,
            'label_block' => true,
            'options' => array(
              'off' => esc_html__( 'Default', 'mcw-fullpage-elementor' ),
              'sections' => esc_html__( 'Sections', 'mcw-fullpage-elementor' ),
              'slides' => esc_html__( 'Slides', 'mcw-fullpage-elementor' ),
            ),
            'default' => 'off',
            'description' => esc_html__( 'Defines Parallax extension target.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
              $this->GetId( 'extension-parallax' ) => 'yes',
            ),
          )
        );

        $page->add_control(
          $this->GetId( 'extension-parallax-type' ),
          array(
            'label' => esc_html__( 'Type', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SELECT,
            'options' => array(
              'reveal' => esc_html__( 'Reveal', 'mcw-fullpage-elementor' ),
              'cover' => esc_html__( 'Cover', 'mcw-fullpage-elementor' ),
            ),
            'default' => 'reveal',
            'description' => esc_html__( 'Provides a way to choose if the current section will be above or below the destination one.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
              $this->GetId( 'extension-parallax' ) => 'yes',
            ),
          )
        );

        $page->add_control(
          $this->GetId( 'extension-parallax-percentage' ),
          array(
            'label' => esc_html__( 'Percentage', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::NUMBER,
            'min' => 0,
            'max' => 100,
            'step' => 1,
            'default' => 62,
            'description' => esc_html__( 'Provides a way to define the percentage of the parallax effect. Maximum value (100) will show completely static backgrounds.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
              $this->GetId( 'extension-parallax' ) => 'yes',
            ),
          )
        );
      } else {
        $page->add_control(
          $this->GetId( 'extension-parallax-disabled' ),
          array(
            'label' => $this->GetNotInstalledExtensionHtml( esc_html__( 'Parallax', 'mcw-fullpage-elementor' ), esc_html__( 'Not Installed', 'mcw-fullpage-elementor' ) ),
            'type' => ControlsManager::RAW_HTML,
            'separator' => 'before',
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      }

      // Reset Sliders extension
      if ( $extensions['reset-sliders']['active'] ) {
        $page->add_control(
          $this->GetId( 'extension-reset-sliders' ),
          array(
            'label' => esc_html__( 'Reset Sliders', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'no',
            'separator' => 'before',
            'description' => esc_html__( 'Enables fullpage Reset Sliders extension.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      } else {
        $page->add_control(
          $this->GetId( 'extension-reset-sliders-disabled' ),
          array(
            'label' => $this->GetNotInstalledExtensionHtml( esc_html__( 'Reset Sliders', 'mcw-fullpage-elementor' ), esc_html__( 'Not Installed', 'mcw-fullpage-elementor' ) ),
            'type' => ControlsManager::RAW_HTML,
            'separator' => 'before',
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      }

      // Responsive Slides extension
      if ( $extensions['responsive-slides']['active'] ) {
        $page->add_control(
          $this->GetId( 'extension-responsive-slides' ),
          array(
            'label' => esc_html__( 'Responsive Slides', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'no',
            'separator' => 'before',
            'description' => esc_html__( 'Enables fullpage Responsive Slides extension.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      } else {
        $page->add_control(
          $this->GetId( 'extension-responsive-slides-disabled' ),
          array(
            'label' => $this->GetNotInstalledExtensionHtml( esc_html__( 'Responsive Slides', 'mcw-fullpage-elementor' ), esc_html__( 'Not Installed', 'mcw-fullpage-elementor' ) ),
            'type' => ControlsManager::RAW_HTML,
            'separator' => 'before',
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      }

      // Scroll Horizontally extension
      if ( $extensions['scroll-horizontally']['active'] ) {
        $page->add_control(
          $this->GetId( 'extension-scroll-horizontally' ),
          array(
            'label' => esc_html__( 'Scroll Horizontally', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'no',
            'separator' => 'before',
            'description' => esc_html__( 'Enables fullpage Scroll Horizontally extension.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      } else {
        $page->add_control(
          $this->GetId( 'extension-scroll-horizontally-disabled' ),
          array(
            'label' => $this->GetNotInstalledExtensionHtml( esc_html__( 'Scroll Horizontally', 'mcw-fullpage-elementor' ), esc_html__( 'Not Installed', 'mcw-fullpage-elementor' ) ),
            'type' => ControlsManager::RAW_HTML,
            'separator' => 'before',
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      }

      // Scroll Overflow Reset extension
      if ( $extensions['scroll-overflow-reset']['active'] ) {
        $page->add_control(
          $this->GetId( 'extension-scroll-overflow-reset' ),
          array(
            'label' => esc_html__( 'Scroll Overflow Reset', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'no',
            'separator' => 'before',
            'description' => esc_html__( 'Enables fullpage Scroll Overflow Reset extension.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );

        $page->add_control(
          $this->GetId( 'extension-scroll-overflow-reset-target' ),
          array(
            'label' => esc_html__( 'Scroll Overflow Reset Target', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SELECT,
            'label_block' => true,
            'options' => array(
              'off' => esc_html__( 'Default', 'mcw-fullpage-elementor' ),
              'sections' => esc_html__( 'Sections', 'mcw-fullpage-elementor' ),
              'slides' => esc_html__( 'Slides', 'mcw-fullpage-elementor' ),
            ),
            'default' => 'off',
            'description' => esc_html__( 'Defines Scroll Overflow Reset extension target.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
              $this->GetId( 'extension-scroll-overflow-reset' ) => 'yes',
            ),
          )
        );
      } else {
        $page->add_control(
          $this->GetId( 'extension-scroll-overflow-reset-disabled' ),
          array(
            'label' => $this->GetNotInstalledExtensionHtml( esc_html__( 'Scroll Overflow Reset', 'mcw-fullpage-elementor' ), esc_html__( 'Not Installed', 'mcw-fullpage-elementor' ) ),
            'type' => ControlsManager::RAW_HTML,
            'separator' => 'before',
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      }

      // Water Effect extension
      if ( $extensions['water-effect']['active'] ) {
        $page->add_control(
          $this->GetId( 'extension-water-effect' ),
          array(
            'label' => esc_html__( 'Water Effect', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'no',
            'separator' => 'before',
            'description' => esc_html__( 'Enables fullpage Water Effect extension.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );

        $page->add_control(
          $this->GetId( 'extension-water-effect-target' ),
          array(
            'label' => esc_html__( 'Target', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SELECT,
            'label_block' => true,
            'options' => array(
              'off' => esc_html__( 'Default', 'mcw-fullpage-elementor' ),
              'sections' => esc_html__( 'Sections', 'mcw-fullpage-elementor' ),
              'slides' => esc_html__( 'Slides', 'mcw-fullpage-elementor' ),
            ),
            'default' => 'off',
            'description' => esc_html__( 'Defines Water Effect extension target.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
              $this->GetId( 'extension-water-effect' ) => 'yes',
            ),
          )
        );

        $page->add_control(
          $this->GetId( 'extension-water-effect-animate-content' ),
          array(
            'label' => esc_html__( 'Animate Content', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'yes',
            'description' => esc_html__( 'Defines whether or not to animate and fade the sections/slides content when background animation takes place.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
              $this->GetId( 'extension-water-effect' ) => 'yes',
            ),
          )
        );

        $page->add_control(
          $this->GetId( 'extension-water-effect-animate-on-mouse-move' ),
          array(
            'label' => esc_html__( 'Animate On Mouse Move', 'mcw-fullpage-elementor' ),
            'type' => ControlsManager::SWITCHER,
            'default' => 'yes',
            'description' => esc_html__( 'Defines whether to animate on mouse move.', 'mcw-fullpage-elementor' ),
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
              $this->GetId( 'extension-water-effect' ) => 'yes',
            ),
          )
        );
      } else {
        $page->add_control(
          $this->GetId( 'extension-water-effect-disabled' ),
          array(
            'label' => $this->GetNotInstalledExtensionHtml( esc_html__( 'Water Effect', 'mcw-fullpage-elementor' ), esc_html__( 'Not Installed', 'mcw-fullpage-elementor' ) ),
            'type' => ControlsManager::RAW_HTML,
            'separator' => 'before',
            'condition' => array(
              $this->GetId( 'enable-extensions' ) => 'yes',
            ),
          )
        );
      }

      do_action( $this->tag . '-extension-controls' );

      $page->end_controls_section();
    }

    private function AddCustomizationsTab( $page ) {
      $page->start_controls_section(
        $this->GetId( 'tab-customizations' ),
        array(
          'label' => esc_html__( 'Customizations', 'mcw-fullpage-elementor' ),
          'tab' => $this->tab,
          'condition' => array(
            $this->GetId( 'enabled' ) => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'extra-parameters' ),
        array(
          'label' => esc_html__( 'Extra Parameters', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::TEXT,
          'default' => '',
          'description' => esc_html__( 'If there are parameters you want to include, add these parameters (comma seperated). Example: parameter1:true, parameter2:15', 'mcw-fullpage-elementor' ),
          'seperator' => 'after',
        )
      );

      $page->add_control(
        $this->GetId( 'video-autoplay' ),
        array(
          'label' => esc_html__( 'Video Autoplay', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'yes',
          'description' => esc_html__( 'Enables playing the videos (HTML5 and Youtube) only when the section is in view and stops it otherwise.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'video-keepplaying' ),
        array(
          'label' => esc_html__( 'Video Keep Playing', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'The videos keep playing even after section is changed.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'video-autoplay' ) => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'remove-theme-margins' ),
        array(
          'label' => esc_html__( 'Remove Theme Margins', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Force to remove theme wrapper margins and paddings.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'fixed-theme-header' ),
        array(
          'label' => esc_html__( 'Force Fixed Theme Header', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Force to make theme header fixed on top.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'fixed-theme-header-selector' ),
        array(
          'label' => esc_html__( 'Theme Header Selector', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::TEXT,
          'default' => 'header',
          'description' => esc_html__( 'Theme header CSS selector. (Example: .elementor-location-header)', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'fixed-theme-header' ) => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'fixed-theme-header-section' ),
        array(
          'label' => esc_html__( 'Is Header a Section?', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Enable this option if you design the header using a section or the header is inside a section.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'fixed-theme-header' ) => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'fixed-theme-header-toggle' ),
        array(
          'label' => esc_html__( 'Toggle Header', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Enable this option if you want the header hidden when scrolling down, and show the header when scrolling up.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'fixed-theme-header' ) => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'fixed-theme-header-padding' ),
        array(
          'label' => esc_html__( 'Theme Header Padding', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Add theme header height to sections as padding.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'fixed-theme-header' ) => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'move-footer' ),
        array(
          'label' => esc_html__( 'Show Theme Footer', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Tries to move the theme footer inside a new auto-height section placed as last.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'move-footer-selector' ),
        array(
          'label' => esc_html__( 'Theme Footer Selector', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::TEXT,
          'default' => 'footer, .elementor-location-footer',
          'description' => esc_html__( 'Theme footer CSS selector. (Example: .elementor-location-footer)', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'move-footer' ) => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'hide-content-before-load' ),
        array(
          'label' => esc_html__( 'Hide Content Before FullPage', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Hide content before FullPage load and show content when the page is loaded.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->end_controls_section();
    }

    private function AddAdvancedTab( $page ) {
      $page->start_controls_section(
        $this->GetId( 'tab-advanced' ),
        array(
          'label' => esc_html__( 'Advanced', 'mcw-fullpage-elementor' ),
          'tab' => $this->tab,
          'condition' => array( $this->GetId( 'enabled' ) => 'yes' ),
        )
      );

      $page->add_control(
        $this->GetId( 'section-selector' ),
        array(
          'label' => esc_html__( 'Section Selector', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::TEXT,
          'placeholder' => '.mcw-fp-section',
          'default' => '',
        )
      );

      $page->add_control(
        $this->GetId( 'slide-selector' ),
        array(
          'label' => esc_html__( 'Slide Selector', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::TEXT,
          'placeholder' => '.mcw-fp-section-slide .mcw-fp-slide',
          'default' => '',
        )
      );

      $page->add_control(
        $this->GetId( 'observer' ),
        array(
          'label' => esc_html__( 'Observer', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'yes',
          'description' => esc_html__( 'Defines whether or not to observe changes in the HTML structure of the page.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'jquery' ),
        array(
          'label' => esc_html__( 'Enable JQuery Dependency', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Enables JQuery dependency.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'enable-template' ),
        array(
          'label' => esc_html__( 'Enable Empty Page Template', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'yes',
          'description' => esc_html__( 'Defines if page will be redirected to the defined template. The template is independent from the theme and is an empty page template if not defined.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'enable-template-redirect' ),
        array(
          'label' => esc_html__( 'Use Template Redirect', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Defines if template will be redirected or included. If set, template will be redirected, otherwise template will be included. Play with this setting to see the best scenario that fits.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'enable-template' ) => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'template-path' ),
        array(
          'label' => esc_html__( 'Template Path', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::TEXT,
          'default' => '',
          'description' => esc_html__( 'If you want to use your own template, put the template path and template name here. If left empty, an empty predefined page template will be used.', 'mcw-fullpage-elementor' ),
          'condition' => array(
            $this->GetId( 'enable-template' ) => 'yes',
          ),
        )
      );

      $page->add_control(
        $this->GetId( 'remove-theme-js' ),
        array(
          'label' => esc_html__( 'Remove Theme JS', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::SWITCHER,
          'default' => 'no',
          'description' => esc_html__( 'Remove theme javascript from output. Be aware, this might crash the page output if the theme has JS output on the head section.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->add_control(
        $this->GetId( 'remove-js' ),
        array(
          'label' => esc_html__( 'Remove JS', 'mcw-fullpage-elementor' ),
          'type' => ControlsManager::TEXT,
          'default' => '',
          'description' => esc_html__( 'Remove specified javascript from output. Be aware, this might crash the page output. Write javascript names with comma in between.', 'mcw-fullpage-elementor' ),
        )
      );

      $page->end_controls_section();
    }

    private function ImplodeParams( $parameters, $extras = '' ) {
      $paramStr = '';
      foreach ( $parameters as $key => $value ) {
        if ( isset( $value ) && ! empty( $value ) ) {
          if ( is_array( $value ) && isset( $value['raw'] ) ) {
            $paramStr .= $key . ':' . $value['raw'] . ',';
          } elseif ( 'false' === $value || 'true' === $value || 'null' === $value || is_numeric( $value ) ) {
            $paramStr .= $key . ':' . $value . ',';
          } else {
            $paramStr .= $key . ':"' . $value . '",';
          }
        }
      }
      $paramStr .= $extras;
      return '{' . rtrim( $paramStr, ',' ) . '}';
    }

    private function GetFullPageCustomizationScripts() {
      return array(

        // removeThemeMargins
        'removeThemeMargins' => 'function getParentsUntil(elem, parent, selector){
          if (!Element.prototype.matches) {
            Element.prototype.matches = Element.prototype.matchesSelector || Element.prototype.mozMatchesSelector || Element.prototype.msMatchesSelector || Element.prototype.oMatchesSelector || Element.prototype.webkitMatchesSelector || function(s) {
              var matches = (this.document || this.ownerDocument).querySelectorAll(s);
              var i = matches.length;
              while(--i >= 0 && matches.item(i) !== this) {}
              return i > -1;
            };
          }

          var parents = [];
          for (;elem && elem !== document; elem = elem.parentNode) {
            if (parent) {
              if (elem.matches(parent)) break;
            }

            if (selector) {
              if (elem.matches(selector)) {
                parents.push(elem);
              }
              break;
            }
            parents.push(elem);
          }
          return parents;
        }
        Array.prototype.forEach.call(getParentsUntil( document.querySelector(".' . $this->wrapper . '"), "body" ), function(el, i){
          if (el.classList) el.classList.add("mcw_fp_nomargin");
          else el.className += " mcw_fp_nomargin";
        });',

        // fixedThemeHeader
        'fixedThemeHeader' => 'function outerHeight(el) {
          if (!el) return 0;
          var height = el.offsetHeight;
          var style = getComputedStyle(el);
          return height + parseInt(style.marginTop) + parseInt(style.marginBottom);
        }
        function headerPadding(header, selector){
          var el = document.querySelectorAll(header);
          if (el.length == 0) return;
          var height = outerHeight(el[0]);

          [].slice.call( document.querySelectorAll(selector) ).forEach(function(eli){
            eli.style.paddingTop = height + "px";
          });

          [].slice.call( document.querySelectorAll(".fp-controlArrow") ).forEach(function(eli){
            eli.style.marginTop = ((height - outerHeight(eli)) / 2) + "px";
          });

          [].slice.call( document.querySelectorAll("#fp-nav") ).forEach(function(eli){
            eli.style.paddingTop = (height / 2) + "px";
          });

          return height;
        }',

        // moveThemeHeader
        'moveThemeHeader' => 'function moveArr() {
          function wrapAll(wrapper, elms) {
            var el = elms.length ? elms[0] : elms;
            var parent  = el.parentNode;
            var sibling = el.nextSibling;

            wrapper.appendChild(el);

            while (elms.length) {
              wrapper.appendChild(elms[0]);
            }

            if (sibling) {
              parent.insertBefore(wrapper, sibling);
            } else {
              parent.appendChild(wrapper);
            }
          }
          var h = document.querySelectorAll(".mcw-fp-wrapper %s");
          var p = document.querySelector(".mcw-fp-wrapper .elementor");
          var w = document.querySelector(".mcw-fp-wrapper");
          var r = w.querySelector(".elementor .elementor-section-wrap");
          if (!r) {
            var wrapper = document.createElement("div");
            wrapper.classList.add("elementor-section-wrap");
            var parent = document.querySelector(".mcw-fp-section").parentElement;
            wrapAll(wrapper, parent.children);
            parent.appendChild(wrapper);
            var r = w.querySelector(".elementor .elementor-section-wrap");
          }
          for (var i = h.length - 1; i >= 0; i--) {
              p.insertBefore(h[i], p.firstChild);
              h[i].classList.remove("mcw-fp-section");
              h[i].classList.remove("fp-table");
          }
          r.classList.add("mcw-fp-wrapper");
          w.classList.remove("mcw-fp-wrapper");
        }
        moveArr();',

        'toggleThemeHeader' => 'if (direction) {
          document.body.classList.remove("fp-direction-last-" + (direction === "up" ? "down" : "up"));
          document.body.classList.add("fp-direction-last-" + direction);
        }',

        // moveFooter
        'moveFooter' => 'var els = document.querySelectorAll("%s");
        if (els.length) {
          var d = document.createElement("div");
          d.classList.add("mcw-fp-section", "fp-footer-section", "fp-auto-height");'
          . ( $this->IsFieldEnabled( 'disable-anchors' ) ? '' : 'd.setAttribute("data-anchor", document.querySelector("#footer") ? "footers" : "footer");' ) .
          ' els.forEach(function(el){ d.appendChild(el); });
          document.querySelector(".mcw-fp-section").parentElement.appendChild(d);
        }',

        // videoAutoplay
        'videoAutoplay' => 'function videoAutoplay(element,keep){
            element = element ? element : document;
            var elements = element.querySelectorAll(\'video,audio,iframe[src*="youtube.com/embed/"]\');
            Array.prototype.forEach.call(elements, function(el, i){
              if (!el.hasAttribute("data-autoplay")) el.setAttribute("data-autoplay", "true");
              if (keep&&!el.hasAttribute("data-keepplaying")) el.setAttribute("data-keepplaying", "true");
            });
          };',

        'clickTooltip' => 'function clickTooltip(){
          Array.prototype.forEach.call(document.querySelectorAll("#fp-nav ul li .fp-tooltip"), function(t, i){
            t.addEventListener("click", function(e) {
              if (event.target && event.target.parentElement && event.target.parentElement.tagName == "LI") {
                event.target.parentElement.querySelector("a").dispatchEvent(new MouseEvent("click", {
                  bubbles: true,
                  cancelable: true,
                  view: window
                }));
              }
            });
          });
        }',

      );
    }

    private function GetFullPageJS( $content ) {
      $customizationScripts = $this->GetFullPageCustomizationScripts();

      // FullPage parameters
      $parameters = array();
      // License key
      $parameters['licenseKey'] = $this->pluginSettings->GetLicenseKeyGen();
      $parameters['credits'] = 'false';

      // Section selector
      $selector = $this->GetFieldValue( 'section-selector', '' );
      $parameters['sectionSelector'] = empty( $selector ) ? '.mcw-fp-section' : $selector;

      // Slide selector
      $selector = $this->GetFieldValue( 'slide-selector', '' );
      $parameters['slideSelector'] = empty( $selector ) ? '.mcw-fp-section-slide .mcw-fp-slide' : $selector;

      // Observer
      $parameters['observer'] = $this->IsFieldOn( 'observer' );

      // Scroll Beyond FullPage
      $parameters['scrollBeyondFullpage'] = 'false';

      // Customizations
      {
        $extras = $this->GetFieldValue( 'extra-parameters', '' );

        $customizations = array(
          'before' => '',
          'after' => 'window.fullpage_api.wordpress={name:"elementor",version:"' . McwFullPageElementorGlobals::Version() . '"};',
          'afterRender' => '',
          'afterResize' => '',
          'afterLoad' => '',
          'beforeLeave' => '',
          'onLeave' => '',
          'afterSlideLoad' => '',
          'onSlideLeave' => '',
          'onScrollOverflow' => '',
          'afterResponsive' => '',
          'afterReBuild' => '',
        );

        if ( $this->IsFieldEnabled( 'fixed-theme-header' ) ) {
          $selector = $this->GetFieldValue( 'fixed-theme-header-selector', 'header' );
          if ( $this->IsFieldEnabled( 'fixed-theme-header-section' ) ) {
            $customizations['before'] .= sprintf( $customizationScripts['moveThemeHeader'], $selector );
          }

          if ( $this->IsFieldEnabled( 'fixed-theme-header-toggle' ) ) {
            $customizations['onLeave'] .= $customizationScripts['toggleThemeHeader'];
          }

          if ( $this->IsFieldEnabled( 'fixed-theme-header-padding' ) ) {
            $headerPaddingScriptCall = sprintf( 'headerPadding("%s","%s");', $selector, '.mcw-fp-section:not(.mcw-fp-section-slide)' );
            $headerPaddingScriptCall .= sprintf( 'headerPadding("%s","%s");', $selector, '.mcw-fp-section.mcw-fp-section-slide .mcw-fp-slide' );
            $customizations['afterRender'] .= $headerPaddingScriptCall;
            $customizations['afterResize'] .= $headerPaddingScriptCall;

            $customizations['before'] .= $customizationScripts['fixedThemeHeader'];
          }
        }

        if ( $this->IsFieldEnabled( 'move-footer' ) ) {
          $selector = $this->GetFieldValue( 'move-footer-selector', 'footer, .elementor-location-footer' );
          $customizations['before'] .= sprintf( $customizationScripts['moveFooter'], $selector );
        }

        if ( $this->IsFieldEnabled( 'video-autoplay' ) || preg_match( '/data-settings="[^"].*[\b|:|&quot;]([^&]*youtube\.com.*?)[\b|:|&quot;][^"]"/', $content ) ) {
          $keep = $this->IsFieldEnabled( 'video-autoplay' ) && $this->IsFieldEnabled( 'video-keepplaying' );
          $customizations['before'] .= $customizationScripts['videoAutoplay'];
          $customizations['afterRender'] .= 'videoAutoplay(undefined,' . ( $keep ? 'true' : 'false' ) . ');';
          $customizations['afterLoad'] .= 'videoAutoplay(undefined,' . ( $keep ? 'true' : 'false' ) . ');';
        }

        if ( $this->IsFieldEnabled( 'remove-theme-margins' ) ) {
          $customizations['before'] .= $customizationScripts['removeThemeMargins'];
        }

        if ( $this->IsFieldEnabled( 'scroll-overflow' ) ) {
          $customizations['afterRender'] .= 'document.querySelectorAll(".fp-overflow > .elementor-background-overlay, .fp-overflow > .elementor-shape, .fp-overflow > .background-video-container").forEach(function(el){el.parentNode.parentNode.insertBefore(el, el.parentNode.parentNode.firstChild)});';
        }
      }

      // Navigation parameters
      {
        $sectionNavStyle = $this->GetFieldValue( 'nav', 'right', 'section-navigation' );
        $parameters['navigation'] = ( 'off' === $sectionNavStyle ) ? 'false' : 'true';
        if ( 'off' !== $sectionNavStyle ) {
          $parameters['navigationPosition'] = ( 'right' === $sectionNavStyle ) ? 'right' : 'left';
          $parameters['showActiveTooltip'] = $this->IsFieldOn( 'show-active-tooltip', 'section-navigation' );

          if ( $this->IsFieldEnabled( 'click-tooltip', 'section-navigation' ) ) {
            $customizations['before'] .= $customizationScripts['clickTooltip'];
            $customizations['afterRender'] .= 'clickTooltip();';
          }
        }

        $slideNavStyle = $this->GetFieldValue( 'nav', 'off', 'slide-navigation' );
        $parameters['slidesNavigation'] = ( 'off' === $slideNavStyle ) ? 'false' : 'true';
        if ( 'off' !== $slideNavStyle ) {
          // navigationPosition
          $parameters['slidesNavPosition'] = ( 'top' === $slideNavStyle ) ? 'top' : 'bottom';
        }

        // controlArrows
        $parameters['controlArrows'] = $this->IsFieldOn( 'control-arrows' );
        if ( $this->IsFieldEnabled( 'control-arrows' ) && $this->GetFieldValue( 'arrow-style', 'modern', 'control-arrows-options' ) === 'modern' ) {
          $color = $this->GetFieldValue( 'arrow-color-main', '#FFFFFF', 'control-arrows-options' );

          $parameters['controlArrowsHTML'] = array(
            'raw' => sprintf(
              "['<div class=\"fp-arrow\">%1s<\/div>','<div class=\"fp-arrow\">%2s<\/div>']",
              '<svg width="60px" height="80px" viewBox="0 0 50 80" xml:space="preserve"><polyline fill="none" stroke="' . $color . '" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" points="45.63,75.8 0.375,38.087 45.63,0.375"></polyline></svg>',
              '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="60px" height="80px" viewBox="0 0 50 80" xml:space="preserve"><polyline fill="none" stroke="' . $color . '" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" points="0.375,0.375 45.63,38.087 0.375,75.8 "></polyline></svg>'
            ),
          );
        }

        // lockAnchors
        $parameters['lockAnchors'] = $this->IsFieldOn( 'lock-anchors' );
        // animateAnchor
        $parameters['animateAnchor'] = $this->IsFieldOn( 'animate-anchor' );
        // keyboardScrolling
        $parameters['keyboardScrolling'] = $this->IsFieldOn( 'keyboard-scrolling' );
        // recordHistory
        $parameters['recordHistory'] = $this->IsFieldOn( 'record-history' );
      }

      // Scrolling parameters
      {
        // autoScrolling
        $parameters['autoScrolling'] = $this->IsFieldOn( 'auto-scrolling' );
        // fitToSection
        $parameters['fitToSection'] = $this->IsFieldOn( 'fit-to-section' );
        // fitToSectionDelay
        $parameters['fitToSectionDelay'] = $this->GetFieldValue( 'fit-to-section-delay', '1000' );
        // scrollBar
        $parameters['scrollBar'] = $this->IsFieldOn( 'scroll-bar' );

        if ( $this->IsFieldEnabled( 'scroll-bar' ) ) {
          $parameters['scrollOverflow'] = 'false';
          $parameters['scrollOverflowMacStyle'] = 'false';
        } else {
          $customizations['afterLoad'] .= 'Waypoint.refreshAll();';
          // scrollOverflow
          $parameters['scrollOverflow'] = $this->IsFieldOn( 'scroll-overflow' );
          $parameters['scrollOverflowMacStyle'] = $this->IsFieldEnabled( 'scroll-overflow' ) ? $this->IsFieldOn( 'scroll-overflow-mac-style' ) : 'false';
        }

        // bigSectionsDestination
        $bigSectionsDestination = $this->GetFieldValue( 'big-sections-destination', 'default' );
        $parameters['bigSectionsDestination'] = ( 'default' !== $bigSectionsDestination ) ? $bigSectionsDestination : '';

        // continuousVertical, loopBottom, loopTop
        if ( ! $this->IsFieldEnabled( 'scroll-bar' ) && $this->IsFieldEnabled( 'auto-scrolling' ) && $this->IsFieldEnabled( 'continuous-vertical' ) ) {
          $parameters['continuousVertical'] = 'true';
          $parameters['loopBottom'] = 'false';
          $parameters['loopTop'] = 'false';
        } else {
          $parameters['continuousVertical'] = 'false';
          $parameters['loopBottom'] = $this->IsFieldOn( 'loop-bottom' );
          $parameters['loopTop'] = $this->IsFieldOn( 'loop-top' );
        }

        // loopHorizontal
        $parameters['loopHorizontal'] = $this->IsFieldOn( 'loop-slides' );
        // scrollingSpeed
        $parameters['scrollingSpeed'] = $this->GetFieldValue( 'scrolling-speed', '1000' );

        // css3, easingcss3, easing
        if ( $this->IsFieldEnabled( 'css-easing-enable' ) ) {
          $parameters['css3'] = 'true';
          $parameters['easingcss3'] = $this->GetFieldValue( 'easing-css', 'ease' );
        } else {
          $parameters['css3'] = 'false';
          $parameters['easing'] = $this->GetFieldValue( 'easing-js', 'linear' );
        }
      }

      // Design parameters
      {
        // verticalCentered
        $parameters['verticalCentered'] = $this->GetFieldValue( 'vertical-alignment', 'center' ) === 'center' ? 'true' : 'false';
        // responsiveWidth
        $parameters['responsiveWidth'] = $this->GetFieldValue( 'responsive-width', '0' );
        // responsiveHeight
        $parameters['responsiveHeight'] = $this->GetFieldValue( 'responsive-height', '0' );
        // paddingTop
        // $parameters['paddingTop'] = array( 'raw' => '(typeof mcwPaddingTop!=="undefined"&&mcwPaddingTop)?mcwPaddingTop+"px":"0px"' );
        // paddingBottom
        // $parameters['paddingBottom'] = '0px';
        // fixedElements
        $parameters['fixedElements'] = $this->GetFieldValue( 'fixed-elements', '' );
        // normalScrollElements
        $parameters['normalScrollElements'] = $this->GetFieldValue( 'normal-scroll-elements', '.elementor-location-popup' );
      }

      // Extension parameters
      if ( $this->IsFieldEnabled( 'enable-extensions' ) ) {
        $extensions = $this->GetExtensionsInfo();
        $extensionKeys = apply_filters( 'mcw-fullpage-extension-key', array() );

        foreach ( $extensions as $key => $value ) {
          $extensions[ $key ]['key'] = array_key_exists( $key, $extensionKeys ) ? $extensionKeys[ $key ] : '';
        }

        // Cards extension
        if ( false && $extensions['cards']['active'] && $this->IsFieldEnabled( 'extension-cards' ) ) {
          $parameters['cards'] = $this->IsFieldOn( 'extension-cards' );
          $parameters['cardsKey'] = $this->GetExtensionKey( $extensions['cards'] );
          $parameters['cardsOptions'] = array(
            'raw' => $this->ImplodeParams(
              array(
                'perspective' => $this->GetFieldValue( 'extension-cards-perspective' ),
                'fadingContent' => $this->IsFieldOn( 'extension-cards-fading-content' ),
                'fadingBackground' => $this->IsFieldOn( 'extension-cards-fading-background' ),
              )
            ),
          );
        }

        // Continuous Horizontal extension
        if ( $extensions['continuous-horizontal']['active'] && $this->IsFieldEnabled( 'extension-continuous-horizontal' ) ) {
          $parameters['continuousHorizontal'] = $this->IsFieldOn( 'extension-continuous-horizontal' );
          $parameters['continuousHorizontalKey'] = $this->GetExtensionKey( $extensions['continuous-horizontal'] );
        }

        // Drag And Move extension
        if ( $extensions['drag-and-move']['active'] && $this->IsFieldEnabled( 'extension-drag-and-move' ) ) {
          $dragAndMove = $this->GetFieldValue( 'extension-drag-and-move-target', 'off' );
          $parameters['dragAndMove'] = 'off' === $dragAndMove ? 'true' : $dragAndMove;
          $parameters['dragAndMoveKey'] = $this->GetExtensionKey( $extensions['drag-and-move'] );
        }

        // Drop Effect extension
        if ( $extensions['drop-effect']['active'] && $this->IsFieldEnabled( 'extension-drop-effect' ) ) {
          $dropEffect = $this->GetFieldValue( 'extension-drop-effect-target', 'off' );
          $parameters['dropEffect'] = 'off' === $dropEffect ? 'true' : $dropEffect;
          $parameters['dropEffectKey'] = $this->GetExtensionKey( $extensions['drop-effect'] );
          $parameters['dropEffectOptions'] = array(
            'raw' => $this->ImplodeParams(
              array(
                'speed' => $this->GetFieldValue( 'extension-drop-effect-speed' ),
                'color' => $this->GetFieldValue( 'extension-drop-effect-color' ),
                'zIndex' => $this->GetFieldValue( 'extension-drop-effect-zindex' ),
              )
            ),
          );
        }

        // Fading Effect extension
        if ( $extensions['fading-effect']['active'] && $this->IsFieldEnabled( 'extension-fading-effect' ) ) {
          $fadingEffect = $this->GetFieldValue( 'extension-fading-effect-target', 'off' );
          $parameters['fadingEffect'] = 'off' === $fadingEffect ? 'true' : $fadingEffect;
          $parameters['fadingEffectKey'] = $this->GetExtensionKey( $extensions['fading-effect'] );
        }

        // Interlocked Slides extension
        if ( $extensions['interlocked-slides']['active'] && $this->IsFieldEnabled( 'extension-interlocked-slides' ) ) {
          $parameters['interlockedSlides'] = $this->IsFieldOn( 'extension-interlocked-slides' );
          $parameters['interlockedSlidesKey'] = $this->GetExtensionKey( $extensions['interlocked-slides'] );
        }

        // Offset Sections extension
        if ( $extensions['offset-sections']['active'] && $this->IsFieldEnabled( 'extension-offset-sections' ) ) {
          $parameters['offsetSections'] = $this->IsFieldOn( 'extension-offset-sections' );
          $parameters['offsetSectionsKey'] = $this->GetExtensionKey( $extensions['offset-sections'] );
        }

        // Parallax extension
        if ( $extensions['parallax']['active'] && $this->IsFieldEnabled( 'extension-parallax' ) ) {
          $parallax = $this->GetFieldValue( 'extension-parallax-target', 'off' );
          $parameters['parallax'] = 'off' === $parallax ? 'true' : $parallax;
          $parameters['parallaxKey'] = $this->GetExtensionKey( $extensions['parallax'] );
          $parameters['parallaxOptions'] = array(
            'raw' => $this->ImplodeParams(
              array(
                'type' => $this->GetFieldValue( 'extension-parallax-type' ),
                'percentage' => $this->GetFieldValue( 'extension-parallax-percentage' ),
                'property' => 'translate',
              )
            ),
          );
        }

        // Reset Sliders extension
        if ( $extensions['reset-sliders']['active'] && $this->IsFieldEnabled( 'extension-reset-sliders' ) ) {
          $parameters['resetSliders'] = $this->IsFieldOn( 'extension-reset-sliders' );
          $parameters['resetSlidersKey'] = $this->GetExtensionKey( $extensions['reset-sliders'] );
        }

        // Responsive Slides extension
        if ( $extensions['responsive-slides']['active'] && $this->IsFieldEnabled( 'extension-responsive-slides' ) ) {
          $parameters['responsiveSlides'] = $this->IsFieldOn( 'extension-responsive-slides' );
          $parameters['responsiveSlidesKey'] = $this->GetExtensionKey( $extensions['responsive-slides'] );
        }

        // Scroll Horizontally extension
        if ( $extensions['scroll-horizontally']['active'] && $this->IsFieldEnabled( 'extension-scroll-horizontally' ) ) {
          $parameters['scrollHorizontally'] = $this->IsFieldOn( 'extension-scroll-horizontally' );
          $parameters['scrollHorizontallyKey'] = $this->GetExtensionKey( $extensions['scroll-horizontally'] );
        }

        // Scroll Overflow Reset extension
        if ( $extensions['scroll-overflow-reset']['active'] && $this->IsFieldEnabled( 'extension-scroll-overflow-reset' ) ) {
          $scrollOverflowReset = $this->GetFieldValue( 'extension-scroll-overflow-reset-target', 'off' );
          $parameters['scrollOverflowReset'] = 'off' === $scrollOverflowReset ? 'true' : $scrollOverflowReset;
          $parameters['scrollOverflowResetKey'] = $this->GetExtensionKey( $extensions['scroll-overflow-reset'] );
        }

        // Water Effect extension
        if ( $extensions['water-effect']['active'] && $this->IsFieldEnabled( 'extension-water-effect' ) ) {
          $waterEffect = $this->GetFieldValue( 'extension-water-effect-target', 'off' );
          $parameters['waterEffect'] = 'off' === $waterEffect ? 'true' : $waterEffect;
          $parameters['waterEffectKey'] = $this->GetExtensionKey( $extensions['water-effect'] );
          $parameters['waterEffectOptions'] = array(
            'raw' => $this->ImplodeParams(
              array(
                'animateContent' => $this->IsFieldOn( 'extension-water-effect-animate-content' ),
                'animateOnMouseMove' => $this->IsFieldOn( 'extension-water-effect-animate-on-mouse-move' ),
              )
            ),
          );
        }
      }

      // Events
      {
        $events = array(
          'afterRender' => array(
            'enable' => 'after-render-enable',
            'field' => 'after-render',
            'fn' => 'function(){%s}',
          ),
          'afterResize' => array(
            'enable' => 'after-resize-enable',
            'field' => 'after-resize',
            'fn' => 'function(width,height){%s}',
          ),
          'afterLoad' => array(
            'enable' => 'after-load-enable',
            'field' => 'after-load',
            'fn' => 'function(origin,destination,direction,trigger){%s}',
          ),
          'beforeLeave' => array(
            'enable' => 'before-leave-enable',
            'field' => 'before-leave',
            'fn' => 'function(origin,destination,direction,trigger){%s}',
          ),
          'onLeave' => array(
            'enable' => 'on-leave-enable',
            'field' => 'on-leave',
            'fn' => 'function(origin,destination,direction,trigger){%s}',
          ),
          'afterSlideLoad' => array(
            'enable' => 'after-slide-load-enable',
            'field' => 'after-slide-load',
            'fn' => 'function(section,origin,destination,direction,trigger){%s}',
          ),
          'onSlideLeave' => array(
            'enable' => 'on-slide-leave-enable',
            'field' => 'on-slide-leave',
            'fn' => 'function(section,origin,destination,direction,trigger){%s}',
          ),
          'afterResponsive' => array(
            'enable' => 'after-responsive-enable',
            'field' => 'after-responsive',
            'fn' => 'function(isResponsive){%s}',
          ),
          'afterReBuild' => array(
            'enable' => 'after-rebuild-enable',
            'field' => 'after-rebuild',
            'fn' => 'function(){%s}',
          ),
          'onScrollOverflow' => array(
            'enable' => 'on-scroll-overflow-enable',
            'field' => 'on-scroll-overflow',
            'fn' => 'function(section,slide,position,direction){%s}',
          ),
        );

        foreach ( $events as $name => $event ) {
          $script = '';

          if ( isset( $customizations[ $name ] ) && ! empty( $customizations[ $name ] ) ) {
            $script .= $customizations[ $name ];
          }

          if ( $this->IsFieldEnabled( $event['enable'] ) ) {
            $script .= $this->MinimizeJavascriptAdvanced( $this->GetFieldValue( $event['field'], '' ) );
          }

          if ( ! empty( $script ) ) {
            $parameters[ $name ] = array( 'raw' => sprintf( $event['fn'], $script ) );
          }
        }

        // beforeFullPage event
        $beforeFullPage = $this->IsFieldEnabled( 'before-fullpage-enable' ) ? $this->GetFieldValue( 'before-fullpage', '' ) : '';
        // afterFullPage event
        $afterFullPage = $this->IsFieldEnabled( 'after-fullpage-enable' ) ? $this->GetFieldValue( 'after-fullpage', '' ) : '';
      }

      $script = $this->IsFieldEnabled( 'jquery' ) ?
        '(function runFullPage($){(function ready(fn){if (document.attachEvent?document.readyState==="complete":document.readyState!=="loading"){fn();}else{document.addEventListener("DOMContentLoaded",fn);}})(function(){%s%s%s});})(jQuery);' :
        '(function runFullPage(){(function ready(fn){if (document.attachEvent?document.readyState==="complete":document.readyState!=="loading"){fn();}else{document.addEventListener("DOMContentLoaded",fn);}})(function(){%s%s%s});})();';

      return $this->MinimizeJavascriptSimple(
        sprintf(
          $script,
          $customizations['before'] . $beforeFullPage,
          'new fullpage(".' . $this->wrapper . '",' . $this->ImplodeParams( $parameters, $extras ) . ');',
          $customizations['after'] . $afterFullPage
        )
      );
    }
  }

}
