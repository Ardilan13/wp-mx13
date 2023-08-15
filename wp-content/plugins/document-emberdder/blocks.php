<?php
if(!defined('ABSPATH')) {
    return;
}

if(!class_exists('PPV_Block')){
    class PPV_Block{
        function __construct(){
            // add_action('init', [$this, 'enqueue_block_css_js']);
            add_action('init', [$this, 'enqueue_script']);
        }

        function enqueue_script(){
            wp_register_script(	'ppv-blocks', plugin_dir_url( __FILE__ ).'dist/editor.js', array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'jquery' ), PPV_VER, true );

            // wp_register_style( 'ppv-blocks', plugin_dir_url( __FILE__ ). 'dist/editor.css' , array(), PPV_VER );

            wp_localize_script('ppv-blocks', 'ppvBlocks', [
                'siteUrl' => site_url(),
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'ppv_nonce' => wp_create_nonce( 'ppv_secret_nonce' )
            ]);

            register_block_type('meta-box/document-embedder', array(
                'editor_script' => 'ppv-blocks',
                // 'style' => 'ppv-blocks',
                'render_callback' => function($attr, $content){
                    ob_start();
                    
                    if(isset($attr['selected'])){
                        echo do_shortcode("[doc id=".esc_attr($attr['selected'])."]");
                    }else if(isset($attr['data']['tringle_text'])){
                        echo do_shortcode("[doc id=".esc_attr($attr['data']['tringle_text'])."]");
                    }
                    return ob_get_clean();
                }
            ));

            register_block_type('kahf-kit/kahf-banner-k27f', array(
                'editor_script' => 'ppv-blocks',
                // 'style' => 'ppv-blocks',
                'render_callback' => function($attr, $content){
                    return wpautop( $content );
                }
            ));


        }

    }

    new PPV_Block();
}

