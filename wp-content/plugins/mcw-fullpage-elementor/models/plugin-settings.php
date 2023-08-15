<?php

/* Copyright 2019-2023 Mehmet Celik */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

require_once McwFullPageElementorGlobals::Dir() . 'models/server.php';

if ( ! class_exists( 'McwFullPageElementorPluginSettings' ) ) {

  class McwFullPageElementorPluginSettings {
    private $tag;
    // Plugin settings
    private $settings = null;
    private $settingsId = '-settings';
    private $licenseKeyId = '-license-key';
    private $nonceField = '_settings_nonce';
    private $extensions = array();
    // Server
    private $server = null;

    public function __construct( $tag ) {
      $this->tag = $tag;
      $this->settingsId = $this->tag . $this->settingsId;
      $this->licenseKeyId = $this->tag . $this->licenseKeyId;
      $this->nonceField = '_' . $this->tag . $this->nonceField;

      $this->extensions = McwFullPageElementorGlobals::GetExtensions();

      add_action( 'admin_menu', array( $this, 'OnAdminMenu' ), 200 );
      add_action( 'admin_init', array( $this, 'OnAdminInit' ), 200 );
      add_action( 'current_screen', array( $this, 'OnCurrentScreen' ) );
    }

    // Adds FullPage for Elementor Settings Page
    public function OnAdminMenu() {
      $optionsPage = add_submenu_page(
        \Elementor\Settings::PAGE_ID,
        McwFullPageElementorGlobals::Name() . ' ' . esc_html__( 'Settings', 'mcw-fullpage-elementor' ),
        McwFullPageElementorGlobals::Name(),
        'manage_options',
        $this->tag,
        array( $this, 'OnAddSubMenuPage' )
      );

      // Load settings CSS
      add_action( 'load-' . $optionsPage, array( $this, 'onAdminEnqueueSettingsStyle' ) );
    }

    public function onAdminEnqueueSettingsStyle() {
      wp_enqueue_style(
        $this->tag . '-settings',
        McwFullPageElementorGlobals::Url() . 'assets/settings/settings.min.css',
        array(),
        McwFullPageElementorGlobals::Version(),
        'all'
      );

      wp_enqueue_script(
        $this->tag . '-settings-js',
        McwFullPageElementorGlobals::Url() . 'assets/settings/settings.min.js',
        array( 'jquery' ),
        '1.0',
        true
      );

      wp_localize_script(
        $this->tag . '-settings-js',
        'McwFullPageSettings',
        array(
          'ajaxurl' => admin_url( 'admin-ajax.php' ),
          'nonce' => wp_create_nonce( $this->tag . '-fullpage-extension-install-nonce' ),
        )
      );
    }

    public function OnAdminInit() {
      $this->server = new McwFullPageCommonServer(
        McwFullPageElementorGlobals::Name(),
        $this->tag,
        $this->GetLicenseKey(),
        McwFullPageElementorGlobals::Version(),
        McwFullPageElementorGlobals::File()
      );

      register_setting(
        $this->tag . '-group',
        $this->settingsId
      );

      // Register plugin license key setting
      add_settings_section(
        $this->tag . '-section',
        null,
        array( $this, 'OnSettingsLicenseKeyRender' ),
        $this->tag . '-sections'
      );

      // Ajax request
      add_action( 'wp_ajax_fullpage_extension_install', array( $this, 'OnAjaxFullPageExtensionInstall' ) );

      do_action( $this->tag . '-admin-settings' );
    }

    public function OnCurrentScreen( $screen ) {
      // Deactivate button
      if ( isset( $_POST ) && isset( $_POST[ $this->nonceField ] ) && wp_verify_nonce( $_POST[ $this->nonceField ], $this->nonceField ) ) {
        if ( isset( $_POST['option_page'] ) && ( $this->tag . '-group' === $_POST['option_page'] ) ) {
          if ( isset( $_POST['action'] ) && ( 'update' === $_POST['action'] ) ) {
            if ( isset( $_POST['deactivate'] ) && ( esc_html__( 'Deactivate', 'mcw-fullpage-elementor' ) === $_POST['deactivate'] ) ) {
              $this->server->Deactivate();
              $_POST[ $this->settingsId ][ $this->licenseKeyId ] = '';
            }
          }
        }
      }

      // Update settings
      if ( $this->IsSettingsPage( $screen ) ) {
        if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
          $this->server->GetRemote( true, array( 'activate' => true ) );
        }
      }
    }

    public function OnSettingsLicenseKeyRender() {
      $licenseKey = $this->GetLicenseKey();
      $licenseState = $this->GetLicenseState();
      $isActive = $licenseKey && $licenseState;

      $url = $isActive && current_user_can( 'update_plugins' ) && $this->server->CanUpdate() ? rawurlencode( plugin_basename( __FILE__ ) ) : false;
      if ( $url ) {
        $url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . $url ), 'upgrade-plugin_' . $url );
      }

      $extensionKeys = apply_filters( 'mcw-fullpage-extension-key', array() );

      foreach ( $this->extensions as $key => $value ) {
        $this->extensions[ $key ]['key'] = array_key_exists( $key, $extensionKeys ) ? $extensionKeys[ $key ] : '';
      }
      ?>

      <section class = "mcw-fp-settings-flex-sect">
        <div class = "mcw-fp-settings-row-parent">
          <div class = "mcw-fp-settings-cell">
            <div class = "mcw-fp-settings-content-wrapper mcw-fp-settings-header">
              <h1><?php echo McwFullPageElementorGlobals::Name(); ?></h1>
              <?php wp_nonce_field( $this->nonceField, $this->nonceField ); ?>
            </div>
          </div>
        </div>
      </section>
      <section class = "mcw-fp-settings-flex-sect">
        <div class = "mcw-fp-settings-row-parent">
          <div class = "mcw-fp-settings-cell">
            <div class = "mcw-fp-settings-content-wrapper">
              <div class="mcw-fp-settings-row mcw-fp-settings-row-inner mcw-fp-settings-border-bottom">
                <div class="mcw-fp-settings-cell mcw-fp-settings-title"><?php echo esc_html__( 'Plugin Activation', 'mcw-fullpage-elementor' ); ?></div>
                <?php if ( $isActive ) : ?>
                <div class="mcw-fp-settings-cell mcw-fp-settings-info activated">
                  <div><img src="<?php echo McwFullPageElementorGlobals::Url() . 'assets/settings/correct-symbol.svg'; ?>"><span class="text"><?php echo esc_html__( 'Plugin Activated!', 'mcw-fullpage-elementor' ); ?></span></div>
                </div>
                <?php else : ?>
                <div class="mcw-fp-settings-cell mcw-fp-settings-info">
                  <div><img src="<?php echo McwFullPageElementorGlobals::Url() . 'assets/settings/remove-symbol.svg'; ?>"><span class="text"><?php echo esc_html__( 'Plugin Not Activated!', 'mcw-fullpage-elementor' ); ?></span></div>
                </div>
                <?php endif; ?>
              </div>
              <div class="mcw-fp-settings-row mcw-fp-settings-row-inner">
                <div class="mcw-fp-settings-cell">
                  <div class="mcw-fp-settings-license-key-inner">
                    <img src="<?php echo McwFullPageElementorGlobals::Url() . 'assets/settings/credit-card.svg'; ?>" />
                    <div>
                    <?php if ( $isActive ) : ?>
                      <span class="key"><?php echo esc_html__( 'License', 'mcw-fullpage-elementor' ); ?></span>
                      <br/>
                      <?php echo esc_html__( 'In order to register fullpage.js plugin for another domain, you\'ll have to deactivate your license for the current domain.', 'mcw-fullpage-elementor' ); ?>
                    <?php else : ?>
                      <span class="key"><?php echo esc_html__( 'License Key', 'mcw-fullpage-elementor' ); ?></span>
                      <br/>
                      <?php echo esc_html__( 'The license key was sent to you on the purchase confirmation email', 'mcw-fullpage-elementor' ); ?>
                    <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
              <?php if ( ! $isActive ) : ?>
              <div class="mcw-fp-settings-row mcw-fp-settings-row-inner">
                <div class="mcw-fp-settings-cell">
                  <input type="text" autocomplete="off" name="<?php echo $this->settingsId; ?>[<?php echo $this->licenseKeyId; ?>]" value="<?php echo $licenseKey; ?>" style="padding:7px;width:100%;"><br/><div class="mcw-fp-settings-note"><?php
                    if ( $licenseKey && ! $licenseState ) {
                      echo '<p class="error">' . esc_html__( 'An error occured while activating the license. Please check your license key!', 'mcw-fullpage-elementor' ) . '<br/>Error: ' . $this->server->GetRemoteErrorMessage() . '</p>';
                      echo '<a href="https://www.meceware.com/server/deactivate-fullpage/" target="_blank" class="button deactivate-link" rel="noopener noreferrer">Deactive License on Another Domain</a>';
                    }
                  ?></div>
                </div>
              </div>
              <?php endif; ?>
              <div class="mcw-fp-settings-row mcw-fp-settings-row-inner">
                <div class="mcw-fp-settings-cell mcw-fp-settings-<?php echo $isActive ? 'deactivate' : 'activate'; ?>">
                  <?php
                  if ( $isActive ) {
                    submit_button( esc_html__( 'Deactivate', 'mcw-fullpage-elementor' ), 'delete', 'deactivate', false, null );
                  } else {
                    submit_button( esc_html__( 'Activate', 'mcw-fullpage-elementor' ), 'button', 'activate', false, null );
                  }
                  ?>
                </div>
              </div>
            </div>
          </div>
          <div class="mcw-fp-settings-cell">
            <div class = "mcw-fp-settings-content-wrapper">
              <div class="mcw-fp-settings-row mcw-fp-settings-row-inner mcw-fp-settings-border-bottom">
                <div class="mcw-fp-settings-cell mcw-fp-settings-title"><?php echo esc_html__( 'Plugin Updates', 'mcw-fullpage-elementor' ); ?></div>
              </div>
              <div class="mcw-fp-settings-row mcw-fp-settings-row-inner">
                <div class="mcw-fp-settings-cell">
                  <div class="mcw-fp-settings-license-key-inner"><div><span class="key"><?php echo esc_html__( 'Installed Version', 'mcw-fullpage-elementor' ); ?></span><br/><?php echo McwFullPageElementorGlobals::Version(); ?></div></div>
                </div>
              </div>
              <div class="mcw-fp-settings-row mcw-fp-settings-row-inner">
                <div class="mcw-fp-settings-cell">
                  <div class="mcw-fp-settings-license-key-inner"><div><span class="key"><?php echo esc_html__( 'Latest Available Version', 'mcw-fullpage-elementor' ); ?></span><br/><?php echo $isActive ? $this->server->GetRemoveVersion() : '----'; ?></div></div>
                </div>
              </div>
              <div class="mcw-fp-settings-row mcw-fp-settings-row-inner">
                <div class="mcw-fp-settings-cell">
                  <?php if ( $url ) : ?>
                    <a href="<?php echo $url; ?>" class="button"><?php echo esc_html__( 'Update', 'mcw-fullpage-elementor' ); ?></a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class = "mcw-fp-settings-flex-sect">
        <div class = "mcw-fp-settings-row-parent">
          <div class = "mcw-fp-settings-cell">
            <div class = "mcw-fp-settings-content-wrapper mcw-fp-settings-header">
              <div class="mcw-fp-settings-row mcw-fp-settings-row-inner mcw-fp-settings-border-bottom">
                <div class="mcw-fp-settings-cell mcw-fp-settings-title"><a href="https://www.meceware.com/docs/fullpage-for-elementor/" target="blank"><?php echo esc_html__( 'Documentation', 'mcw-fullpage-elementor' ); ?></a></div>
              </div>
              <div class="mcw-fp-settings-row-block mcw-fp-settings-row-inner">
                <p><?php echo esc_html__( 'Check out the tutorial video!', 'mcw-fullpage-elementor' ); ?></p>
                <p><a href="https://www.meceware.com/docs/fullpage-for-elementor/#tutorial-video" target="blank"><img src="<?php echo McwFullPageElementorGlobals::Url() . 'assets/settings/tutorial-video.jpg'; ?>" /></a></p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <?php if ( $isActive ) : ?>
      <section class = "mcw-fp-settings-flex-sect">
        <div class = "mcw-fp-settings-row-parent">
          <div class = "mcw-fp-settings-cell">
            <div class = "mcw-fp-settings-content-wrapper mcw-fp-settings-header">
              <div class="mcw-fp-settings-row mcw-fp-settings-row-inner mcw-fp-settings-border-bottom">
                <div class="mcw-fp-settings-cell mcw-fp-settings-title"><?php echo esc_html__( 'Extensions', 'mcw-fullpage-elementor' ); ?></div>
              </div>
              <?php foreach ( $this->extensions as $key => $value ) : ?>
              <div class="mcw-fp-settings-row-extension mcw-fp-settings-row-inner">
                <?php if ( $value['active'] && get_option( $value['id'] ) ) : ?>
                  <?php if ( $value['key'] ) : ?>
                    <div class="mcw-fp-settings-cell mcw-fp-settings-info activated">
                      <div>
                        <img src="<?php echo McwFullPageElementorGlobals::Url() . 'assets/settings/correct-symbol.svg'; ?>"><span class="text"><?php /* translators: 1: Extension Plugin Name */ echo sprintf( esc_html__( '%s Extension Plugin Activated!', 'mcw-fullpage-elementor' ), $value['name'] ); ?></span>
                      </div>
                    </div>
                  <?php else : ?>
                    <?php
                      $linkUrl = wp_nonce_url(
                        add_query_arg(
                          array(
                            'mcw_updates' => 1,
                            'slug' => $value['id'],
                          ),
                          admin_url( 'admin.php?page=' . $_GET['page'] )
                        ),
                        'mcw_updates'
                      );
                      $homeUrl = get_home_url();
                      if ( strpos( $homeUrl, 'localhost' ) !== false ) {
                        $homeUrl = '';
                      }
                    ?>
                    <div class="mcw-fp-settings-cell mcw-fp-settings-info installed">
                      <div>
                        <img src="<?php echo McwFullPageElementorGlobals::Url() . 'assets/settings/correct-symbol.svg'; ?>"><span class="text"><?php /* translators: 1: Extension Plugin Name */ echo sprintf( esc_html__( '%s Extension Plugin Installed!', 'mcw-fullpage-elementor' ), $value['name'] ); ?></span><a href="https://www.meceware.com/docs/fullpage-for-elementor/#extensions" target="_blank">(<?php echo esc_html__( 'How To Activate!', 'mcw-fullpage-elementor' ); ?>)</a>
                      </div>
                      <a id="activate-<?php echo $value['id']; ?>" class="button button-primary" href="https://alvarotrigo.com/fullPage/extensions/activationKey.html?extension=<?php echo $value['idc']; ?>&l=<?php echo get_option( $value['id'] ); ?>&url=<?php echo $homeUrl; ?>" target="_blank"><?php echo esc_html__( 'Activate Extension', 'mcw-fullpage-elementor' ); ?></a>
                      <a id="check-activation-<?php echo $value['id']; ?>" class="button button-primary" href="<?php echo $linkUrl; ?>"><?php echo esc_html__( 'Check Activation', 'mcw-fullpage-elementor' ); ?></a>
                    </div>
                    <div class="mcw-fp-settings-note">
                    <?php
                      echo esc_html__( 'When you purchase an extension, you will need to activate the extension for a domain. You can use the extension without activating it on a domain (for example, a staging/development domain). When a non-activated extension is used on a domain, a red box will appear but it will be removed automatically upon activation.', 'mcw-fullpage-elementor' );
                      echo '<br/><br/>';
                      echo '<span style="font-size:15px;">' . esc_html__( 'Please note that, there is no deactivation for extensions.', 'mcw-fullpage-elementor' ) . '</span>';
                      echo '<br/><br/>';
                      echo '<span style="font-size:15px;">' . esc_html__( 'The extension domain cannot be changed (no deactivation) and once activated, refund for the extension would not be possible.', 'mcw-fullpage-elementor' ) . '</span>';
                    ?>
                    </div>
                  <?php endif; ?>
                <?php else : ?>
                  <span class="key"><?php echo $value['name']; ?></span>
                  <input type="text" name="<?php echo $this->settingsId; ?>[<?php echo $value['id']; ?>]" value="<?php echo get_option( $value['id'] ); ?>"/>
                  <button type="button" id="install-<?php echo $value['id']; ?>" class="button button-primary fp-extension-install" data-extension="<?php echo $key; ?>">Install Extension</button>
                  <div class="mcw-fp-settings-note" style="display: none;"></div>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <script type="text/javascript">if (document.querySelector('.mcw-fp-settings-deactivate input.button')) document.querySelector('.mcw-fp-settings-deactivate input.button').addEventListener('click', function (e){return confirm('<?php echo esc_html__( 'Are you sure you want to deactivate?', 'mcw-fullpage-elementor' ); ?>') || e.preventDefault();});</script>
      <?php
    }

    // FullPage for Elementor Settings Page content
    public function OnAddSubMenuPage() {
      ?>
      <form action='options.php' method='post'>
        <?php
        settings_fields( $this->tag . '-group' );
        do_settings_sections( $this->tag . '-sections' );
        ?>
      </form>
      <?php
    }

    public function OnAjaxFullPageExtensionInstall() {
      if ( ! wp_verify_nonce( $_POST['nonce'], $this->tag . '-fullpage-extension-install-nonce' ) ) {
        echo wp_json_encode(
          array(
            'success' => false,
            'message' => esc_html__( 'The request did not pass the security check.', 'mcw-fullpage-elementor' )
            . '<br/>'
            . esc_html__( 'Please, try again. Try to refresh the page in order to renew security nonce.', 'mcw-fullpage-elementor' ),
          )
        );
        wp_die();
      }

      $key = isset( $_POST['key'] ) ? urldecode( $_POST['key'] ) : '';
      if ( empty( $key ) || strlen( $key ) < 5 ) {
        echo wp_json_encode(
          array(
            'success' => false,
            'message' => esc_html__( 'Invalid license key!', 'mcw-fullpage-elementor' ),
          )
        );
        wp_die();
      }

      $extension = isset( $_POST['extension'] ) ? urldecode( $_POST['extension'] ) : '';
      if ( empty( $extension ) || ! array_key_exists( $extension, $this->extensions ) ) {
        echo wp_json_encode(
          array(
            'success' => false,
            'message' => esc_html__( 'Invalid extension!', 'mcw-fullpage-elementor' ),
          )
        );
        wp_die();
      }

      // Update option
      update_option( $this->extensions[ $extension ]['id'], $key );

      $remote = $this->server->GetRemoteBase(
        array(
          'action' => 'get_metadata',
          'slug' => $this->extensions[ $extension ]['id'],
          'extension' => $extension,
          'key' => $key,
          'installed_version' => '0.0.0',
          'api' => 'v2',
        )
      );

      if ( ! $remote ) {
        echo wp_json_encode(
          array(
            'success' => false,
            'message' => esc_html__( 'Invalid response!', 'mcw-fullpage-elementor' ),
          )
        );
        wp_die();
      }

      $remote = json_decode( $remote['body'] );

      if ( ! isset( $remote->download_url ) || empty( $remote->download_url ) ) {
        if ( isset( $remote->license_status ) && ! $remote->license_status && isset( $remote->license_error ) && $remote->license_error ) {
          echo wp_json_encode(
            array(
              'success' => false,
              'message' => esc_html__( 'Invalid response!', 'mcw-fullpage-elementor' ) . '<br/>' . $remote->license_error,
            )
          );
        } else {
          echo wp_json_encode(
            array(
              'success' => false,
              'message' => esc_html__( 'Invalid response!', 'mcw-fullpage-elementor' ) . '<br/>' . esc_html__( 'Please check your license key!', 'mcw-fullpage-elementor' ),
            )
          );
        }
        wp_die();
      }

      $url = esc_url_raw( $remote->download_url );
      $pluginFile = download_url( $url );

      if ( is_wp_error( $pluginFile ) || ! $pluginFile ) {
        echo wp_json_encode(
          array(
            'success' => false,
            'message' => esc_html__( 'Failed to download the plugin! Please check your WordPress settings or contact support!', 'mcw-fullpage-elementor' ),
          )
        );
        wp_die();
      }

      ob_start();

      require_once 'upgrader-skin.php';

      $skin = new McwFullPageUpgraderSkinHelper( array() );

      add_filter(
        'upgrader_package_options',
        function ( $options ) {
          $options['clear_destination'] = true;
          return $options;
        }
      );

      $upgrader = new Plugin_Upgrader( $skin );
      $upgraderResult = $upgrader->install( $pluginFile );

      $content = ob_get_clean();

      $errors = array();
      if ( true !== $upgraderResult ) {
        if ( is_object( $upgrader->skin->result ) && 'WP_Error' === get_class( $upgrader->skin->result ) ) {
          if ( isset( $upgrader->skin->result->errors ) && is_array( $upgrader->skin->result->errors ) ) {
            foreach ( $upgrader->skin->result->errors as $err_id => $err ) {
              if ( is_array( $err ) ) {
                foreach ( $err as $string ) {
                  if ( is_string( $string ) ) {
                    $errors[] = $string . ( 'folder_exists' === $err_id ? ' ' . esc_html__( 'You need to uninstall the old plugin first!', 'mcw-fullpage-elementor' ) : '' );
                  }
                }
              } elseif ( is_string( $err ) ) {
                  $errors[] = $err;
              }
            }
          }
        } else {
          $errors[] = 'Unknown error while installing plugin!';
        }
      } else {
        $pluginInfo = $upgrader->skin->upgrader->plugin_info();
        if ( ! is_string( $pluginInfo ) && empty( $pluginInfo ) ) {
          $errors[] = 'Plugin installed but something went wrong!';
        }
      }

      if ( ! empty( $errors ) ) {
        $errors[] = 'Plugin installation failed.';
      } else {
        $pluginDir = trailingslashit( ABSPATH . str_replace( site_url() . '/', '', plugins_url() ) );
        if ( file_exists( $pluginDir . $pluginInfo ) ) {
          ob_start();
          activate_plugin( $pluginDir . $pluginInfo );
          $content = ob_get_clean();
        } else {
          $errors[] = 'Plugin installed but cannot be activated! Please try to activate the plugin manually from the plugins page!';
        }
      }

      @unlink( $pluginFile );

      if ( ! empty( $errors ) ) {
        echo wp_json_encode(
          array(
            'success' => false,
            'message' => implode( '<br/>', $errors ),
          )
        );
        wp_die();
      }

      echo wp_json_encode(
        array(
          'success' => true,
          'message' => esc_html__( 'Plugin installed successfully!', 'mcw-fullpage-elementor' ),
        )
      );
      wp_die();
    }

    public function GetLicenseKey() {
      return trim( $this->GetSettings( $this->licenseKeyId ) );
    }

    public function GetLicenseState() {
      return ( isset( $this->server ) && $this->server ) ? $this->server->GetRemoteLicenseStatus() : McwFullPageCommonLocal::GetState( $this->tag );
    }

    public function GetLicenseKeyGen() {
      return ( isset( $this->server ) && $this->server ) ? $this->server->GetRemoteLicenseKey() : McwFullPageCommonLocal::GetState( $this->tag . '-license-key' );
    }

    public function IsSettingsPage( $screen = null ) {
      if ( isset( $_GET ) && isset( $_GET['page'] ) && $_GET['page'] === $this->tag ) {
        return true;
      }

      if ( ! $screen ) {
        $screen = get_current_screen();
      }

      if ( $screen && ( ( 'elementor_page_' . $this->tag ) === $screen->base ) ) {
        return true;
      }

      return false;
    }

    // Returns if the element is enabled (1/0)
    private function GetSettings( $element ) {
      if ( ! isset( $this->settings ) ) {
        $this->settings = get_option( $this->settingsId, array() );
      }

      if ( isset( $this->settings[ $element ] ) ) {
        return $this->settings[ $element ];
      }

      return null;
    }
  }

}
