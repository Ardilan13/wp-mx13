<?php
namespace PPV\Model;

class Metabox{
    protected static $_instance = null;

    /**
     * construct function
     */
    public function __construct(){
        if(!defined('PPV_PRO_PLUGIN')){
            add_action( 'add_meta_boxes', [$this, 'ppv_myplugin_add_meta_box'] );
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

    function ppv_myplugin_add_meta_box() {
        add_meta_box(
            'donation',
            __( 'Upgrade to Pro', 'myplugin_textdomain' ),
            [$this, 'ppv_callback_donation'],
            'ppt_viewer'
        );	
        add_meta_box(
            'myplugin_sectionid',
            __( 'Try LightBox Addons', 'myplugin_textdomain' ),
            [$this, 'ppv_addons_callback'],
            'ppt_viewer',
            'side'
        );	
    }

    function ppv_callback_donation( ) {
        echo '
    <script src="https://gumroad.com/js/gumroad-embed.js"></script>
    <div class="gumroad-product-embed" data-gumroad-product-id="depro" data-outbound-embed="true"><a target="_blank" href="https://gumroad.com/l/depro">Loading...</a></div>
    ';}
    function ppv_addons_callback(){
        echo'<a target="_blank" href="https://app.gumroad.com/l/nAiet"><img style="width:100%" src="'.PPV_PLUGIN_DIR.'/img/upwork.png" ></a>
    <p>LightBox Addons enable you to open any doc in a Nice LightBox</p>
    <table>
        <tr>
            <td><a class="button button-primary button-large" href="https://bplugins.com/demo/lightbox-addons-demo/" target="_blank">See Demo </a></td>
            <td><a class="button button-primary button-large" href="https://gumroad.com/l/lightB" target="_blank">Buy Now</a></td>
        </tr>
    </table>
    ';}
    
}
Metabox::instance();