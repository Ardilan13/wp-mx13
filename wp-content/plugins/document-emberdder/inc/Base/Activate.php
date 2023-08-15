<?php 
namespace PPV\Base;

class Activate{

    protected $FILE = PPV__FILE__;
    protected $prefix = '';
    protected $plugin_name = 'Document Embedder';
    protected $url = 'https://bplugins.com/wp-json/data/v1/accept-data';
    protected $status = false;
    protected $post_type = 'ppt_viewer';
    protected $version = PPV_VER;
    protected $nonce = null;
    protected $marketing_allowed = false;
    protected static $_instance = null;
    protected $basename = null;

    public function __construct(){
        $this->register();
    }

    public static function instance(){
        if(!self::$_instance){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function register(){
        $this->prefix = dirname(plugin_basename($this->FILE));
        $this->status = get_option("$this->prefix-opt_in", false);
        $this->last_check = get_option("$this->prefix-info-check", time()-1);
        $this->marketing_allowed = get_option("$this->prefix-marketing-allowed", false);
        $this->basename = plugin_basename($this->FILE);
        register_activation_hook( $this->FILE, [$this, 'activate'] );
        add_filter("plugin_action_links_$this->basename", [$this, 'opt_in_button'] );
        add_action('admin_init', [$this, 'admin_init']);

        if(!$this->status){
            add_action('admin_menu', [$this, 'add_opt_in_menu']);
            add_action('bts_opt_in', [$this, 'bts_opt_in']);
        }
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        
        if($this->status == 'agreed'){
            register_deactivation_hook( $this->FILE, [$this, 'deactivate'] );
        }

        add_action('admin_footer', [$this, 'opt_in_modal']);

    }


    public function opt_in_button($links){
        if($this->marketing_allowed){
            $settings_link = '<a href="'.admin_url("plugins.php?$this->prefix-allow=false").'" data-modal="'.$this->prefix.'">Opt out</a>';
        }else {
            $settings_link = '<a href="'.admin_url("plugins.php?$this->prefix-allow=true").'" class="bpc_opt_btn" data-modal="'.$this->prefix.'">Opt In</a>';
        }
        array_unshift($links, $settings_link);
        return $links;
    }

    public function add_opt_in_menu(){
        $this->nonce = \wp_create_nonce('bp_nonce');
        add_submenu_page(
            '',
            $this->plugin_name,
            $this->plugin_name,
            'manage_options',
            $this->prefix,
            [$this, 'opt_in_form']
        );
    }

    public function enqueue_assets($hook){

        wp_register_script("$this->prefix-opt-in", plugin_dir_url($this->FILE).'dist/optin.js', [], $this->version);
        wp_register_style("$this->prefix-opt-in", plugin_dir_url($this->FILE).'dist/optin.css', [], $this->version);

        wp_localize_script("$this->prefix-opt-in", 'ppvOptData', [
            'post_type' => $this->post_type,
            'page' => admin_url("admin.php?page=$this->prefix"),
            'basename' => $this->basename,
        ]);

        if($hook === 'plugins.php' || $hook === "admin_page_$this->prefix"){
            wp_enqueue_script("$this->prefix-opt-in");
            wp_enqueue_style("$this->prefix-opt-in");
        }

    }

    public function opt_in_form(){
        update_option("$this->prefix-redirect", true);
        
        global $wp_version;
        wp_enqueue_script('bts-opt-in');
        wp_enqueue_style('bts-opt-in');
        $user = wp_get_current_user();
        $skip_url = admin_url("edit.php?post_type=$this->post_type&bp_action={$this->prefix}_skipped_activate&nonce=$this->nonce");
        $agree_url = admin_url("/admin.php?page=$this->prefix&opt_action=opt_accept&nonce=".wp_create_nonce('opt_action'));

        ?>
        <div class="bts-opt-in">
            <div class="content">
                <div class="bp-connect">
                    <span class="wp-icon"><i class="dashicons dashicons-wordpress"></i></span>
                   <i class="dashicons dashicons-plus fs-second"></i>
                    <img src="<?php echo esc_url("https://ps.w.org/$this->prefix/assets/icon-128x128.png")  ?>" alt="">
                    <i class="dashicons dashicons-plus fs-second"></i>
                    <img src="https://bplugins.com/wp-content/uploads/2021/07/bplugins-white.png" alt="">
                </div>
                <p>
                Hey <b><?php echo esc_html($user->user_login) ?></b>,<br/> <?php echo esc_html__("Never miss an important update - opt in to our security and feature updates notifications, and non-sensitive diagnostic tracking with", "bts"); ?> bplugins.com.
                </p>
            </div>
            <div class="actions">
                <form method="POST" action="<?php echo $this->url ?>">
                    <input type="hidden" name="website" value="<?php echo esc_url(site_url()); ?>"  />
                    <input type="hidden" name="user_email" value="<?php echo esc_attr($user->user_email); ?>"  />
                    <input type="hidden" name="user_display_name" value="<?php echo esc_attr($user->display_name); ?>"  />
                    <input type="hidden" name="user_nickname" value="<?php echo esc_attr($user->user_login); ?>"  />
                    <input type="hidden" name="plugin_version" value="<?php echo esc_attr($this->version); ?>"  />
                    <input type="hidden" name="php_version" value="<?php echo esc_attr(phpversion()); ?>"  />
                    <input type="hidden" name="platform_version" value="<?php echo esc_attr($wp_version); ?>" />
                    <input type="hidden" name="status" value="activate" />
                    <input type="hidden" name="plugin_slug" value="<?php echo esc_attr($this->prefix) ?>" />
                    <input type="hidden" name="marketing_allowed" value="1" />
                    <input type="hidden" name="return_url" value="<?php echo esc_url(admin_url("edit.php?post_type=$this->post_type&bp_action={$this->prefix}_agreed_activate&nonce=$this->nonce")); ?>" />
                    <button class="button button-primary"><?php echo esc_html__("Agree & continue", "bts") ?></button>
                </form>
                <a href="<?php echo esc_url($skip_url) ?>" class="button button-secondary"><?php echo esc_html__("Skip", "bts"); ?></a>
            </div>
            <div class="what-is-granted">
                <p class="details-btn"><?php echo esc_html__("What permissions are being granted?", "bts"); ?></p>
                <div class="permissions">
                    <div class="permission">
                        <i class="dashicons dashicons-admin-users"></i>
                        <div>
                            <h3><?php echo esc_html__("YOUR PROFILE OVERVIEW", "bts"); ?></h3>
                            <p><?php echo esc_html__("Name and email address", "bts"); ?></p>
                        </div>
                    </div>
                    <div class="permission">
                        <i class="dashicons dashicons-admin-settings"></i>
                        <div>
                            <h3><?php echo esc_html__("YOUR SITE OVERVIEW", "bts"); ?></h3>
                            <p><?php echo esc_html__("Site URL, WP version, PHP info", "bts"); ?></p>
                        </div>
                    </div>
                    <div class="permission">
                        <i class="dashicons dashicons-testimonial"></i>
                        <div>
                            <h3><?php echo esc_html__("ADMIN NOTICES", "bts"); ?></h3>
                            <p><?php echo esc_html__("Updates, announcements, marketing, no spam", "bts"); ?></p>
                        </div>
                    </div>
                    <div class="permission">
                        <i class="dashicons dashicons-admin-plugins"></i>
                        <div>
                            <h3><?php echo esc_html__("CURRENT PLUGIN STATUS", "bts"); ?></h3>
                            <p><?php echo esc_html__("Active, deactivated, or uninstalled", "bts"); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="privacy-policy">
                <a href="https://bplugins.com/privacy-policy/" target="_blank"><?php echo esc_html__("Privacy Policy", "bts"); ?></a>
                <span>-</span>
                <a href="https://bplugins.com/terms-of-service/" target="_blank"><?php echo esc_html__("Terms and Condition", "bts"); ?></a>
            </div>
        </div>
        <style>
                <?php echo esc_html("#menu-posts-$this->post_type") ?> ul{
                    display: none;
                }
                <?php echo esc_html("#menu-posts-$this->post_type") ?> > a{
                    background: #2271b1;
                }
                <?php echo esc_html("#menu-posts-$this->post_type") ?> div.wp-menu-image::before{
                    color: #fff;
                }
            </style>
        <?php
        // echo "<div id='bts-opt-in'></div>";
    }

    public function data_update($data = []){
        global $wp_version;
        $user = wp_get_current_user();
        $response = wp_remote_post($this->url, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'headers'     => array(),
            'body'        => wp_parse_args($data, [
                'website' => site_url(),
                'user_email' => $user->user_email,
                'user_nickname' => $user->user_nickname ? $user->user_nickname : $user->user_login,
                'php_version' => phpversion(),
                'platform_version' => $wp_version,
                'plugin_version' => $this->version
            ] )
            )
        );
    }

    public function admin_init(){
        if(!$this->status){
            if(isset($_GET['bp_action']) && isset($_GET['nonce'])){
                $action = $_GET['bp_action'];
                $nonce = $_GET['nonce'];
                if(wp_verify_nonce($nonce, 'bp_nonce')){
                    if($action == "{$this->prefix}_skipped_activate"){
                        update_option("$this->prefix-opt_in", 'skipped');
                    }else if($action == "{$this->prefix}_agreed_activate") {
                        update_option("$this->prefix-opt_in", 'agreed');
                        update_option("$this->prefix-marketing-allowed", true);
                    }
                }
            }

            $redirect = get_option("$this->prefix-redirect", false);
            if(!$redirect){
                wp_redirect("admin.php?page=$this->prefix");
            }
        }

        if(isset($_GET["$this->prefix-allow"])){
            if($_GET["$this->prefix-allow"] == "true"){
                update_option("$this->prefix-marketing-allowed", true);
                $this->data_update(['marketing_allowed' => true]);
            }else {
                update_option("$this->prefix-marketing-allowed", false);
                $this->data_update(['marketing_allowed' => false]);
            }
            wp_redirect(admin_url('plugins.php'));
        }

    }


    public function activate(){
        if($this->status == 'agreed'){
            $this->data_update(['status' => 'activate']);
        }
    }

    public function deactivate(){
        if($this->status == 'agreed'){
            $this->data_update(['status' => 'deactivate']);
        }
    }

    public function opt_in_modal($hook){
        global $wp_version;
        $user = \wp_get_current_user();
        $screen = \get_current_screen();
		if($screen->base === 'plugins'){
        ?>
            <div id="opt_in_modal" class="opt_in_modal <?php echo esc_attr($this->prefix) ?>">
                <div class="opt_in_content">
                    <div class="content">
                        <div>
                            <img src="<?php echo esc_url(plugin_dir_url( $this->FILE ).'/img/images/shield_success.png') ?>" alt="">
                        </div>
                        <div>
                            <h3><?php echo esc_html__("Stay on the safe side", "bts") ?></h3>
                            <p><?php echo esc_html__("Receive our plugin’s alerts in case of critical security & feature updates and allow non-sensitive diagnostic tracking.", "bts") ?></p>
                        </div>
                    </div>
                    <div class="active-btn">
                    <form method="POST" action="<?php echo esc_url($this->url) ?>">
                        <input type="hidden" name="website" value="<?php echo esc_url(site_url()); ?>"  />
                        <input type="hidden" name="user_email" value="<?php echo esc_attr($user->user_email); ?>"  />
                        <input type="hidden" name="user_display_name" value="<?php echo esc_attr($user->display_name); ?>"  />
                        <input type="hidden" name="user_nickname" value="<?php echo esc_attr($user->user_login); ?>"  />
                        <input type="hidden" name="plugin_version" value="<?php echo esc_attr($this->version); ?>"  />
                        <input type="hidden" name="php_version" value="<?php echo esc_attr(phpversion()); ?>"  />
                        <input type="hidden" name="platform_version" value="<?php echo esc_attr($wp_version); ?>" />
                        <input type="hidden" name="status" value="activate" />
                        <input type="hidden" name="plugin_slug" value="<?php echo esc_attr($this->prefix) ?>" />
                        <input type="hidden" name="marketing_allowed" value="1" />
                        <input type="hidden" name="return_url" value="<?php echo esc_url(admin_url("plugins.php?$this->prefix-allow=true&bp_action={$this->prefix}_agreed_activate&nonce=$this->nonce")); ?>" />
                        <button class="button button-primary"><?php echo esc_html__("Agree & continue", "bts") ?></button>
                    </form>
                    </div>
                    <div class="granted-info">
                        <h3><?php echo esc_html__("You're granting these permissions", "bts") ?>:</h3>
                        <ul>
                            <li><?php echo esc_html__("Your profile information (name and email)", "bts") ?></li>
                            <li><?php echo esc_html__("Your site information (URL, WP version, PHP info)", "bts") ?></li>
                            <li><?php echo esc_html__("Plugin notices (updates, announcements, marketing, no spam)", "bts") ?></li>
                            <li><?php echo esc_html__("Plugin events (activation, deactivation and uninstall)", "bts") ?></li>
                        </ul>
                    </div>
                    <div class="learn-skip">
                        <a href="#" class="hide-btn"><?php echo esc_html__("hide", "bts") ?></a>
                        <a href="#" class="learn-more-btn"><?php echo esc_html__("Learn More", "bts") ?></a>|
                        <a href="#" class="skip-btn"><?php echo esc_html__("Skip", "bts") ?></a>
                    </div>
                </div>
            </div>
        <?php
        }
    }

}

Activate::instance();
