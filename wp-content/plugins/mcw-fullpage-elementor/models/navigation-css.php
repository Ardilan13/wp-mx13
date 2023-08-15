<?php

/* Copyright 2019-2023 Mehmet Celik */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'McwFullPageElementorNavCSS' ) ) {

  class McwFullPageElementorNavCSS {
    public function __construct() {
    }

    private function hexToRgba( $color, $alpha = false ) {
      if ( preg_match( '/^#(?:[a-f0-9]{3}|[a-f0-9]{4}|[a-f0-9]{6}|[a-f0-9]{8})$/i', $color ) ) {
        // Hex color found
        $color = ltrim( $color, '#' );
        switch ( strlen( $color ) ) {
          case 3:
            $color = str_split( $color );
            break;

          case 4:
            $color = str_split( $color );
            // Remove the alpha element
            array_pop( $color );
            break;

          case 8:
            $color = str_split( $color, 2 );
            // Remove the alpha element
            array_pop( $color );
            break;

          default:
          case 6:
            $color = str_split( $color, 2 );
            break;
        }

        $color = array_map( 'hexdec', $color );
      } elseif ( preg_match( '/^ *rgb\( *\d{1,3} *, *\d{1,3} *, *\d{1,3} *\) *$/i', $color ) ) {
        // rgb() color found
        $matches = null;
        preg_match( '/rgb\( *(\d{1,3} *, *\d{1,3} *, *\d{1,3}) *\)/i', $color, $matches );
        $color = explode( ',', $matches[1] );
        $color = array_map( 'trim', $color );
      } elseif ( preg_match( '/^ *rgba\( *\d{1,3} *, *\d{1,3} *, *\d{1,3} *, *[0-1](\.\d{1,2})? *\) *$/i', $color ) ) {
        // rgba() color found
        $matches = null;
        preg_match( '/rgba\( *(\d{1,3} *, *\d{1,3} *, *\d{1,3} *, *[0-1](\.\d{1,2})?) *\)/i', $color, $matches );

        $color = explode( ',', $matches[1] );
        $color = array_map( 'trim', $color );
        array_pop( $color );
      } else {
        $color = array( 0, 0, 0 );
      }

      if ( $alpha ) {
        return 'rgba(' . implode( ',', $color ) . ',' . $alpha . ')';
      }
      return 'rgb(' . implode( ',', $color ) . ')';
    }

    public function GetCustomCSSBase( $style, $selector, $main, $active, $hover ) {
      $css = array();

      switch ( $style ) {
        case 'default':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a span",
              'value' => "background:{$main};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span",
              'value' => "background:{$active};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active):hover span",
              'value' => "background:{$hover};",
            );
          }
          break;

        case 'circles':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active) span",
              'value' => "background:transparent;border:2px solid {$main};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span",
              'value' => "background:{$active};border:2px solid {$active};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:hover a:not(.active) span",
              'value' => "background:{$hover};background:{$this->hexToRgba( $hover, 0.4 )};",
            );
          }
          break;

        case 'circles-inverted':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a span",
              'value' => "border-color:{$main};background:{$this->hexToRgba( $main, 0.5 )};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span,{$selector} ul li:hover a.active span",
              'value' => "background:transparent;box-shadow:0 0 0 2px {$this->hexToRgba( $active, 1 )};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:hover a:not(.active) span",
              'value' => "background:{$hover};",
            );
          }
          break;

        case 'crazy-text-effect':  // TODO: crazy-text-effect will be fixed. CSS is not right.
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a span::before",
              'value' => "background:{$main};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span::before",
              'value' => "background:{$active};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:hover a:not(.active) span::before",
              'value' => "background:{$hover};",
            );
          }
          break;

        case 'expanding-circles':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a span::before",
              'value' => "box-shadow:inset 0 0 0 10px {$main};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span::before",
              'value' => "box-shadow:inset 0 0 0 10px {$active};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active) span:focus::before,{$selector} ul li a:not(.active) span:hover::before",
              'value' => "box-shadow:inset 0 0 0 10px {$hover};",
            );
          }
          break;

        case 'expanding-squares':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active) span::before",
              'value' => "box-shadow: inset 0 0 0 9px {$main};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span::before",
              'value' => "box-shadow:inset 0 0 0 1px {$active};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active) span:focus::before,{$selector} ul li a:not(.active) span:hover::before",
              'value' => "box-shadow:inset 0 0 0 9px {$hover};",
            );
          }
          break;

        case 'filled-bars':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a span",
              'value' => "background:{$main};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a span::before",
              'value' => "background:{$active} !important;",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:hover a:not(.active) span",
              'value' => "background:{$hover};",
            );
          }
          break;

        case 'filled-circle-within':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active) span",
              'value' => "background:transparent;box-shadow:0 0 0 2px {$this->hexToRgba( $main, 1 )} inset;",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span",
              'value' => "background:transparent;box-shadow:0 0 0 18px {$this->hexToRgba( $active, 1 )} inset;",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:hover a:not(.active) span",
              'value' => "background:#fff;background:{$this->hexToRgba( $hover, 0.4 )};",
            );
          }
          break;

        case 'filled-circles':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a span",
              'value' => "background:{$main};",
            );
            $css[] = array(
              'selector' => "{$selector} ul li a span::before",
              'value' => "background:{$main};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span::before",
              'value' => "background:{$active};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:hover a:not(.active) span",
              'value' => "background:{$hover};",
            );
          }
          break;

        case 'filled-rombs':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a span",
              'value' => "background:{$main};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span::before",
              'value' => "background:{$active};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:hover a:not(.active) span",
              'value' => "background:{$hover};",
            );
          }
          break;

        case 'filled-squares':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active) span,{$selector} ul li a:not(.active) span::before",
              'value' => "background:{$main};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span,{$selector} ul li a.active span::before",
              'value' => "background:{$active};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:hover a:not(.active) span,{$selector} ul li:hover a:not(.active) span::before",
              'value' => "background:{$hover};",
            );
          }
          break;

        case 'multiple-circles':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active) span::before",
              'value' => "background:{$main};",
            );
            $css[] = array(
              'selector' => "{$selector} ul li a span:after",
              'value' => "box-shadow:inset 0 0 0 3px {$main};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span::after",
              'value' => "box-shadow:inset 0 0 0 3px {$active};background: {$active};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:hover a:not(.active) span::before",
              'value' => "background:{$hover};",
            );
          }
          break;

        case 'multiple-squares-to-rombs':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a span::before",
              'value' => "background:{$main};",
            );
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active) span::after",
              'value' => "box-shadow:inset 0 0 0 3px {$main};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span::after",
              'value' => "box-shadow:inset 0 0 0 3px {$active};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:hover a:not(.active) span::before",
              'value' => "background:{$hover};",
            );
          }
          break;

        case 'multiple-squares':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active) span::before",
              'value' => "background:{$main};",
            );
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active) span:after",
              'value' => "box-shadow:inset 0 0 0 3px {$main};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "${selector} ul li a span::before",
              'value' => "background: {$active};",
            );
            $css[] = array(
              'selector' => "{$selector} ul li a.active span::after",
              'value' => "box-shadow:inset 0 0 0 3px {$active};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:hover a:not(.active) span::before",
              'value' => "background:{$hover};",
            );
          }
          break;

        case 'rotating-circles':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active) span",
              'value' => "background:{$this->hexToRgba( $main, 0.2 )};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span",
              'value' => "background:{$active};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:hover a:not(.active) span",
              'value' => "background-color:{$this->hexToRgba( $hover, 0.6 )};",
            );
          }
          break;

        case 'rotating-circles2':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active) span",
              'value' => "background:{$this->hexToRgba( $main, 0.2 )};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span",
              'value' => "background:{$active};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:hover a:not(.active) span",
              'value' => "background-color:{$this->hexToRgba( $hover, 0.6 )};",
            );
          }
          break;

        case 'squares-border':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active) span",
              'value' => "border-color:{$this->hexToRgba( $main, 0.6 )};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span",
              'value' => "background:{$active};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:hover a:not(.active) span",
              'value' => "background-color:{$this->hexToRgba( $hover, 0.4 )};",
            );
          }
          break;

        case 'squares-to-rombs':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active) span",
              'value' => "background:transparent;border-color:{$this->hexToRgba( $main, 0.3 )};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span",
              'value' => "background:{$active};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:hover a:not(.active) span",
              'value' => "background-color:{$this->hexToRgba( $hover, 0.5 )};",
            );
          }
          break;

        case 'squares':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active) span",
              'value' => "background:{$main};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span",
              'value' => "background:{$active};",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:hover a:not(.active) span",
              'value' => "background:{$this->hexToRgba( $hover, 0.5 )};",
            );
          }
          break;

        case 'story-telling':
          if ( isset( $main ) && ! empty( $main ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li:not(:last-child) a:not(.active) span::before",
              'value' => "background:{$main};",
            );
          }
          if ( isset( $active ) && ! empty( $active ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a.active span:after",
              'value' => "border-color:{$active} !important;",
            );
          }
          if ( isset( $hover ) && ! empty( $hover ) ) {
            $css[] = array(
              'selector' => "{$selector} ul li a:not(.active):focus span::after,{$selector} ul li a:not(.active):hover span::after",
              'value' => "border-color:{$hover};",
            );
          }
          break;

        default:
          break;
      }

      return $css;
    }

    public function GetCustomCSS( $style, $selector, $main, $active, $hover ) {
      $css = $this->GetCustomCSSBase( $style, $selector, $main, $active, $hover );
      $output = '';

      foreach( $css as $value ) {
        $output .=  $value['selector'] . '{' . $value['value'] . '}';
      }

      return $output;
    }
  }

}
