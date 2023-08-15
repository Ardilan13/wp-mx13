<?php
namespace PPV\Model;

class EnqueueAssets{
    protected static $_instance = null;

    /**
     * construct function
     */
    public function __construct(){
        add_action('wp_enqueue_scripts',[$this, 'publicAssets']);
        add_action('admin_enqueue_scripts',[$this, 'adminAssets']);
    }

    /**
     * Create instance function
     */
    public static function instance(){
        if(self::$_instance === null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Enqueue Public Assets
     */
    public function publicAssets(){
        wp_enqueue_script('ppv-public', PPV_PLUGIN_DIR . 'dist/public.js', array(), PPV_VER);
        wp_enqueue_style('ppv-public', PPV_PLUGIN_DIR . 'dist/public.css', array(), PPV_VER);
    }

    /**
     * Enqueue Admin Assets
     */
    public function adminAssets($page){
        global $post;
        $screen = get_current_screen();
		if($page === 'plugins.php' || $screen->post_type === 'ppt_viewer'){
			wp_enqueue_script('ppv-admin',  PPV_PLUGIN_DIR . 'admin/js/script.js', array(), PPV_VER);

			wp_localize_script('ppv-admin', 'ppvAdmin', array(
				'ajaxUrl' => admin_url('admin-ajax.php')
			));
			wp_enqueue_style('ppv-admin',  PPV_PLUGIN_DIR . 'admin/css/style.css', array(), PPV_VER);
		}
    }
}
EnqueueAssets::instance();