<?php
namespace PPV\Model;
use PPV\Model\AnalogSystem;

class Shortcode{
    protected static $_instance = null;

    /**
     * construct function
     */
    public function __construct(){
        add_shortcode('doc', [$this, 'doc']);
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

    public function doc($atts){
        $post_type = get_post_type($atts['id']);
        
        if($post_type != 'ppt_viewer'){
            return false;
        }
        
        return AnalogSystem::html($atts['id']);

        return $output;
    }
}
Shortcode::instance();
