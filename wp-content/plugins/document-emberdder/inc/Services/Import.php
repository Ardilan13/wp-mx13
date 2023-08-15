<?php
namespace PPV\Services;

class Import {

    public static function import($isPro = false){
        $docs = new \WP_Query(array(
            'post_type' => 'ppt_viewer',
            'post_status' => 'any',
            'posts_per_page' => -1
        ));

        $output = [];

        while($docs->have_posts()): 
            $docs->the_post();
            $id = get_the_ID();

            $file = get_post_meta($id, '_groupped_ppv_file_url', true);
            if(isset($file['url'])){
                $file = $file['url'];
            }else {
                $file = get_post_meta($id, '_groupped_ppv_ext_url', true);
            }

            $width = get_post_meta($id, 'ppt_ppv_width', true);
            $widthUnit = 'px';
            if(!$width){
                $width = 100;
                $widthUnit = '%';
            }

            $height = get_post_meta($id, 'ppt_ppv_height', true);
            if(!$height){
                $height = 600;
            }

            $fileName = get_post_meta($id, 'ppt_ppv_file_name', true);

            

            // if (\metadata_exists('post', $id, 'ppv') == false) {
            //     update_post_meta($id, 'ppv', [
            //         'doc' => $file,
            //         'width' => ['width' => $width, 'unit' => $widthUnit],
            //         'height' => ['height' => $height, 'unit' => 'px'],
            //         'showName' => $fileName
            //     ]);
            // }
            
            $output[$id] =  [
                'doc' => $file,
                'width' => ['width' => $width, 'unit' => $widthUnit],
                'height' => ['height' => $height, 'unit' => 'px'],
                'showName' => $fileName
            ];


        endwhile;

        $data = apply_filters( 'ppv_data_import', $output, $docs );

        foreach($data as $key => $value){
            if ($isPro || \metadata_exists('post', $key, 'ppv') == false) {
                update_post_meta($key, 'ppv', $value);
            }
        }

    }
}

