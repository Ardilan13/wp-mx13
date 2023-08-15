<?php
namespace PPV\Rest;
use PPV\Helper\Functions;

if(!class_exists('GET_PPV_META')){
class GET_PPV_META{
    function __construct(){
        if(is_admin()){
            add_action('wp_ajax_pdfp_get_doc_meta', [$this, 'single_doc_callback']);
        }
    }

    function single_doc_callback(){
        $response = [];
        $id = isset($_GET['id']) ? sanitize_text_field( $_GET['id'] ): false;
        $nonce = isset($_GET['ppv_nonce']) ? sanitize_text_field( $_GET['ppv_nonce'] ): false;
        $user = wp_get_current_user();
        if(!$id || get_post_status($id) != 'publish' || !wp_verify_nonce( $nonce, 'ppv_secret_nonce' ) || !in_array('administrator', $user->roles)){
            return false;
        }

        $data = $this->get_data($id);
       
        echo wp_json_encode($data);
        die();
    }


    public function get_data( $id = 2038){
        $width = Functions::meta($id, 'width', ['width' => '100', 'unit' => '%']);
        $height = Functions::meta($id, 'height', ['height' => 600, 'unit' => 'px']);

        $result = [
            'url' => Functions::meta($id, 'doc', ''),
            'width' => $width['width'].$width['unit'],
            'height' => $height['height'].$height['unit'],
            'showName' => Functions::meta($id, 'showName'),
            'title' => get_the_title($id)
        ];

        return $result;
    }

    public static function meta($id, $key, $default = null){
        if (metadata_exists('post', $id, $key)) {
            $value = get_post_meta($id, $key, true);
            if ($value != '') {
                return $value;
            } else {
                return $default;
            }
        } else {
            return $default;
        }
    }

 
}
new GET_PPV_META();
}

