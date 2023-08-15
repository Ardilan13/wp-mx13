<?php 
/*
 * Plugin Name: Document Embedder
 * Plugin URI:  http://documentembedder.com/
 * Description: Embed Any document easily in wordpress such as word, excel, powerpoint, pdf and more
 * Version:     1.8.4
 * Author:      bPlugins LLC
 * Author URI:  http://bplugins.com
 * License:     GPLv3
 * Text Domain:  ppv
 * Domain Path:  /i18n
 */

function ppv_load_textdomain() {
    load_plugin_textdomain( 'ppv', false, dirname( plugin_basename( __FILE__ ) ) . "/i18n" );
}
add_action( "init", 'ppv_load_textdomain', 20 );

/*Some Set-up*/
define('PPV_VER', '1.8.4'); 
define('PPV_PLUGIN_DIR', plugin_dir_url(__FILE__) ); 
define('PPV__FILE__', __FILE__ ); 
define('PPV_IMPORT', '1.0.0'); 


require_once(__DIR__.'/upgrade.php');

use PPV\Services\Import;

//Remove post update massage and link
function ppv_updated_messages( $messages ) {
    $messages['ppt_viewer'][1] = __('Updated', 'ppv');
    return $messages;
}
add_filter('post_updated_messages','ppv_updated_messages');
			
// After activation redirect
register_activation_hook(__FILE__, 'ppv_plugin_activate');
add_action('admin_init', 'ppv_plugin_redirect');

function ppv_plugin_activate() {
    add_option('ppv_plugin_do_activation_redirect', true);
}

function ppv_plugin_redirect() {
    if (get_option('ppv_plugin_do_activation_redirect', false)) {
        delete_option('ppv_plugin_do_activation_redirect');
        wp_redirect('edit.php?post_type=ppt_viewer&page=ppv_help');
    }

    if(get_option('ppv_import', '0') < PPV_IMPORT){
        Import::import();
        update_option('ppv_import', PPV_IMPORT);
    }
}
