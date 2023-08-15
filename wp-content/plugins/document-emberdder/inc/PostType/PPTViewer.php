<?php
namespace PPV\PostType;

class PPTViewer{
    protected static $_instance = null;
    protected $post_type = 'ppt_viewer';

    /**
     * construct function
     */
    public function __construct(){
        add_action('init',[$this, 'init']);
        if(is_admin()){
            add_filter("manage_{$this->post_type}_posts_columns", [$this, 'postTypeColumns'], 1);
            add_action("manage_{$this->post_type}_posts_custom_column", [$this, 'postTypeContent'], 10, 2);
            add_action('edit_form_after_title', [$this, 'ppv_shortcode_area']);
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

    public function init(){
        register_post_type( 'ppt_viewer',
            array(
            'labels' => array(
                    'name' => __( 'Document Embedder'),
                    'singular_name' => __( 'Document Embedder' ),
                    'add_new' => __( 'Add New Doc' ),
                    'add_new_item' => __( 'Add New Doc' ),
                    'edit_item' => __( 'Edit' ),
                    'new_item' => __( 'New item' ),
                    'view_item' => __( 'View item' ),
                    'search_items'       => __( 'Search'),
                    'not_found' => __( 'Sorry, we couldn\'t find the power point file you are looking for.' )
            ),
            'public' => false,
            'show_ui' => true, 									
            'publicly_queryable' => true,
            'exclude_from_search' => true,
            'menu_position' => 14,
            'show_in_rest' => true,
            'menu_icon' => PPV_PLUGIN_DIR .'/img/doc.png',
            'has_archive' => false,
            'hierarchical' => false,
            'capability_type' => 'page',
            'rewrite' => array( 'slug' => 'ppt_viewer' ),
            'supports' => array( 'title' )
            )
		);
    }

    /**
     * Columns on all posts page
     *
     * @param array $defaults
     * @return array
     */
    public function postTypeColumns($columns)
    {

        unset($columns['date']);
        $columns['shortcode'] = 'Shortcode';
        $columns['date'] = 'Date';
        return $columns;
    }

    public function postTypeContent($column_name, $post_id)
    {
        switch ( $column_name ) {

            case 'shortcode':
                echo '<div class="ppv_front_shortcode"><input style="text-align: center; border: none; outline: none; background-color: #1e8cbe; color: #fff; padding: 4px 10px; border-radius: 3px;" value="[doc id=' . esc_attr($post_id) . ']" ><span class="htooltip">Copy To Clipboard</span></div>';
                break;
            default: 
                false;
    
        }
    }

    function ppv_shortcode_area(){
        global $post;	
        if($post->post_type=='ppt_viewer'){
        ?>	
        <div class="ppv_playlist_shortcode">
                <div class="shortcode-heading">
                    <div class="icon"><img src="<?php echo esc_url(PPV_PLUGIN_DIR .'/img/doc.png') ?>" alt=""> <?php _e("Document Embedder", "ppv") ?></div>
                    <div class="text"> <a href="https://bplugins.com/support/" target="_blank"><?php _e("Supports", "ppv") ?></a></div>
                </div>
                <div class="shortcode-left">
                    <h3><?php _e("Shortcode", "ppv") ?></h3>

                    <p><?php _e("Copy and paste this shortcode into your posts, pages and widget:", "ppv") ?></p>
                    <div class="shortcode" selectable>[doc id=<?php echo esc_attr($post->ID); ?>]</div>
                </div>
                <div class="shortcode-right">
                    <h3><?php _e("Template Include", "ppv") ?></h3>
                    <p><?php _e("Copy and paste the PHP code into your template file:", "ppv"); ?></p>
                    <div class="shortcode">&lt;?php echo do_shortcode('[doc id=<?php echo esc_html($post->ID); ?>]');
                    ?&gt;</div>
                </div>
            </div>
            <div style="background:black; color: white;padding:5px; font-size:16px;">
                <?php echo esc_html__('! Important : Document Embedder Plugin does not preview any documents in localhost. No worries, when you will live your site you will see all the document are previewing perfectly.', 'ppv') ?> 
            </div>
        <?php   
        }
    }
}
PPTViewer::instance();