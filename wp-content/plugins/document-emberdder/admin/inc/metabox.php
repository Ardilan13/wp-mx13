<?php
// Control core classes for avoid errors
add_action('plugins_loaded', function(){
  if( class_exists( 'CSF' ) ) {

    //
    // Set a unique slug-like ID
    $prefix = 'ppv';
  
    //
    // Create a metabox
    CSF::createMetabox( $prefix, array(
      'title'     => esc_html__('Configuration', 'ppv'),
      'post_type' => 'ppt_viewer',
    ) );
  
    //
    // Create a section
    CSF::createSection( $prefix, array(
      'title'  => '',
      'fields' => apply_filters( 'ppv_pro_metabox', array(
        array(
          'id'    => 'doc',
          'type'  => 'upload',
          'title' => esc_html__('Document', 'ppv'),
        ),
        array(
            'id' => 'width',
            'type' => 'dimensions',
            'title' => esc_html__('Width', 'ppv'),
            'height' => false,
            'default' => ['width' => '100', 'unit' => '%']
        ),
        array(
            'id' => 'height',
            'type' => 'dimensions',
            'title' => esc_html__('Height', 'ppv'),
            'width' => false,
            'default' => ['height' => 600, 'unit' => 'px']
        ),
        array(
            'id' => 'showName',
            'type' => 'switcher',
            'title' => esc_html__('Show file name on top', 'ppv'),
            'default' => 0
        ),
        [
          'id' => 'download',
          'type' => 'switcher',
          'title' => esc_html__('Show downlaod button', 'ppv'),
          'desc' => esc_html__('is not available for google drive and dropbox', 'ppv'),
          'default' => 1
        ],
        
        ) )
    ) );

    apply_filters('ppv_settings', '');
}  
});