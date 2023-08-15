<?php
namespace PPV\Helper;
use PPV\Helper\Functions;

class DefaultArgs{
    public static function parseArgs($data){
        $default = self::doc();
        $data = wp_parse_args( $data, $default );
        return $data;
    }

    public static function doc(){
        return [
            'width' => '100%',
            'height' => '600px',
            'doc' => '',
            'showName' => true,
            'download' => true,
            'downloadButtonText' => 'Download File',
            'googleDrive' => false,
            'disablePopout' => false,
        ];
        
    }

}