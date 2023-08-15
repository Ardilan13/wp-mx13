<?php
namespace PPV\Services;

class DocTemplate{

    public static function html($data){

        $frame_style= 'width:'.$data['width'].'; '. 'height:'. $data['height']. ';';
        $base_url = '//docs.google.com/gview?embedded=true&url=';
        ob_start();

        // echo '<pre>';
        // print_r( $data );
        // echo '</pre>';
        
        if($data['doc'] == ''){ 
            echo '<h2>Ooops... You forgot to Select a document. Please select a file or paste a external document link to show here. </h2>';
        } else{
            // show file name
            if($data['showName']){ echo '<p style="padding-left:10px;">File Name: '. esc_html(basename($data['doc'])) .'</p>';} 

            // Download button
            if($data['download'] === '1'){ 
            $down_btn_color = '';
            echo '<p style="padding-left:10px;"><a class="s_pdf_download_link" href="'.esc_url($data['doc']).'" download><button style="margin-bottom:10px;'.'background-color:'.esc_attr($down_btn_color).';" class="ppv_download_bttn">'.esc_html($data['downloadButtonText']).'</button></a></p>';}
                
            $frame_url = $base_url . $data['doc'];
            if($data['googleDrive']){
                $frame_url = str_replace("view","preview",$data['doc']);
            }

            ?>
            <div class="ppv_wrapper ppv_container" style="position: relative;<?php echo esc_html($frame_style); ?>">
                <iframe id="s_pdf_frame" src="<?php echo esc_url($frame_url) ?>" style="float:left; padding:10px;<?php echo esc_html($frame_style); ?>" frameborder="0"></iframe>
                <?php 
                    if($data['disablePopout']){?> <div style="width: 80px; height: 80px; position: absolute; opacity: 0; right: 0px; top: 0px;"></div><?php }
                ?>
            </div>
            
            <?php
    }
	$output = ob_get_clean();
    return apply_filters( 'ppv_shortcode_html', $output, $data );
    
    }

}