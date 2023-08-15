<?php
namespace PPV\Model;

class GlobalChanges{
    protected static $_instance = null;

    /**
     * construct function
     */
    public function __construct(){
        
        if(is_admin()){
            add_filter('post_row_actions', [$this, 'removeRowAction'], 10, 2);
            add_action('admin_head-post.php', [$this, 'ppv_hide_publishing_actions']);
            add_action('admin_head-post-new.php', [$this, 'ppv_hide_publishing_actions']);
            add_filter( 'gettext', [$this, 'ppv_change_publish_button'], 10, 2 );
            add_action( 'admin_head', [$this, 'ppv_my_custom_script'] );

            add_action('admin_menu', [$this, 'h5vp_add_custom_link_into_cpt_menu']);
        }
    }


      /**
     * add submenu -> PRO Version Demo
     */
    function h5vp_add_custom_link_into_cpt_menu() {
        global $submenu;
        $link = 'https://bplugins.com/products/document-embedder/#pricing';
        $submenu['edit.php?post_type=ppt_viewer'][] = array( 'Upgrade to PRO', 'manage_options', $link, 'meta'=>'target="_blank"' );
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

    /*-------------------------------------------------------------------------------*/
    /*   Hide & Disabled View, Quick Edit and Preview Button
    /*-------------------------------------------------------------------------------*/
    public function removeRowAction($row){
        global $post;
        if ($post->post_type == 'ppt_viewer') {
            unset($row['view']);
            unset($row['inline hide-if-no-js']);
        }
        return $row;
    }

    /*-------------------------------------------------------------------------------*/
    /* HIDE everything in PUBLISH metabox except Move to Trash & PUBLISH button
    /*-------------------------------------------------------------------------------*/
    function ppv_hide_publishing_actions(){
        $my_post_type = 'ppt_viewer';
        global $post;
        if($post->post_type == $my_post_type){
            echo '
                <style type="text/css">
                    #misc-publishing-actions,
                    #minor-publishing-actions{
                        display:none;
                    }
                </style>
            ';
        }
    }

    
    /*-------------------------------------------------------------------------------*/
    /* Change publish button to save.
    /*-------------------------------------------------------------------------------*/
    function ppv_change_publish_button( $translation, $text ) {
        if ( 'ppt_viewer' == get_post_type())
        if ( $text == 'Publish' )
            return 'Save';

        return $translation;
    }

    /**
     * Inject script in admin head
     */
    function ppv_my_custom_script() {
        $screen = get_current_screen();
		if($screen->post_type === 'ppt_viewer'){
			?>
            <script type="text/javascript">
                jQuery(document).ready( function($) {
                    $( "ul#adminmenu a[href$='https://bplugins.com/products/document-embedder/#pricing']" ).attr( 'target', '_blank' );
                    $( "ul#adminmenu a[href$='https://bplugins.com/products/document-embedder/#demos']" ).attr( 'target', '_blank' );
                });
            </script>
            <?php
		}
    }


   
}
GlobalChanges::instance();