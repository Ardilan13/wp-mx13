<?php
namespace PPV\Helper;

class Functions{
    
    public static function meta($id, $key, $default = false){
        $meta = get_post_meta($id, 'ppv', true);
        if(isset($meta[$key])){
            return $meta[$key];
        }else {
            return $default;
        }
    }
}