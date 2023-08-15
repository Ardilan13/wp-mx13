<?php
namespace PPV\Model;
use PPV\Helper\Functions;
use PPV\Helper\DefaultArgs;
use PPV\Services\DocTemplate;

class AnalogSystem{

    public static function html($id){
        $data = self::doc($id);
        $data = DefaultArgs::parseArgs($data);
        return DocTemplate::html($data);
    }

    public static function doc($id){
        $width = Functions::meta($id, 'width', ['width' => '100', 'unit' => '%']);
        $height = Functions::meta($id, 'height', ['height' => 600, 'unit' => 'px']);

        $result = [
            'doc' => Functions::meta($id, 'doc', ''),
            'width' => $width['width'].$width['unit'],
            'height' => $height['height'].$height['unit'],
            'showName' => Functions::meta($id, 'showName'),
            'download' => Functions::meta($id, 'download', '1'),
            'id' => $id
        ];

        return apply_filters('ppv_doc_data', $result, $id);
    }
}