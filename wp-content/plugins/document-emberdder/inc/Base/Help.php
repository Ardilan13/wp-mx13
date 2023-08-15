<?php
namespace PPV\Base;

class Help{
    protected static $_instance = null;
    protected static $review_url = 'https://wordpress.org/support/plugin/document-emberdder/reviews/#new-post';
    protected static $demo_url = 'https://bplugins.com/products/document-embedder/#demos';
    protected static $doc_url = 'https://documentembedder.com/docs/';
    protected static $video_url = 'https://www.youtube.com/embed/mUlMpuPMP5Q?version=3&rel=1&showsearch=0&showinfo=1&iv_load_policy=1&fs=1&hl=en-US&autohide=2&wmode=transparent';
    protected static $slug = 'document-emberdder';

    /**
     * construct function
     */
    public function __construct(){
        add_action('admin_menu', [$this, 'ppv_custom_submenu_page']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
    }

    /**
     * enqueue admin assets
     */
    function admin_enqueue_scripts($hook){
        if($hook === 'ppt_viewer_page_ppv_help'){
            wp_enqueue_style('ppv-page', PPV_PLUGIN_DIR.'admin/css/help.css', [], PPV_VER);
        }
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
     * Register Submenu
     */
    function ppv_custom_submenu_page() {
        global $submenu;

        add_submenu_page( 'edit.php?post_type=ppt_viewer', __("Help", "ppv"), __("Help", "ppv"), 'manage_options', 'ppv_help', [$this, 'help_callback'] );


        $link = 'https://bplugins.com/products/document-embedder/#demos';
        $submenu['edit.php?post_type=ppt_viewer'][] = array( 'PRO Version Demo', 'manage_options', $link, 'meta'=>'target="_blank"' );
    }


	function help_callback(){
		?>
        <div class="bplugins-container">
            <div class="row">
				<div class="col col-12">
					<div class="bplugins-feature center">
						<h1><?php _e("Help & Usages", "ppv"); ?></h1>
					</div>
				</div>
            </div>
        </div>
        <div class="bplugins-container">
            <div class="row">
                <div class="bplugins-features">
                    <div class="col col-4">
                        <div class="bplugins-feature center">
                            <i class="fa fa-life-ring"></i>
                            <h3><?php _e('Need any Assistance?', "ppv"); ?></h3>
                            <p><?php _e('Our Expert Support Team is always ready to help you out promptly.', "ppv"); ?></p>
                            <a href="https://bplugins.com/support/" target="_blank" class="button
                            button-primary"><?php _e('Contact Support', "ppv") ?></a>
                        </div>
                    </div>
                    <div class="col col-4">
                        <div class="bplugins-feature center">
                            <i class="fa fa-file-text"></i>
                            <h3><?php _e('Looking for Documentation?', "ppv") ?></h3>
                            <p><?php echo _e("We have detailed documentation on every aspects of HTML5 Audio Player.", "ppv") ?></p>
                            <a href="<?php echo esc_attr(self::$doc_url) ?>" target="_blank" class="button button-primary"><?php _e("Documentation", "ppv") ?></a>
                        </div>
                    </div>
                    <div class="col col-4">
                        <div class="bplugins-feature center">
                            <i class="fa fa-thumbs-up"></i>
                            <h3><?php _e("Like This Plugin?", "ppv"); ?></h3>
                            <p><?php _e("If you like HTML5 Audio Player, please leave us a 5 &#11088; rating.", "ppv") ?></p>
                            <a href="<?php echo esc_url(self::$review_url) ?>" target="_blank" class="button button-primary"><?php _e("Rate the Plugin", "ppv"); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bplugins-container">
			<div class="row">
				<div class="col col-12">
					<div class="bplugins-feature center" style="padding:5px;">
						<h2 style="font-size:22px;"><?php _e("Looking For Demo?", "ppv"); ?> <a href="<?php echo esc_url(self::$demo_url) ?>" target="_blank"><?php _e("Click Here", "ppv"); ?></a></h2>
					</div>
				</div>
			</div>
        </div>
        <div class="bplugins-container">
            <div class="row">
				<div class="col col-12">
					<div class="bplugins-feature center">
						<h1><?php _e("Video Tutorials", "ppv"); ?></h1><br/>
						<div class="embed-container"><iframe width="100%" height="700px" src="<?php echo esc_url(self::$video_url) ?>" frameborder="0"
						allowfullscreen></iframe></div>
					</div>
				</div>
            </div>
        </div>
    
    <?php
	}
}
Help::instance();