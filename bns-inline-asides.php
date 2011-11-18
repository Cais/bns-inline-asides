<?php
/*
Plugin Name: BNS Inline Asides
Plugin URI: http://buynowshop.com/plugins/bns-inline-asides/
Description: This plugin will allow you to style sections of post content with added emphasis by leveraging a style element from the active theme.
Version: 0.5.1
Author: Edward Caissie
Author URI: http://edwardcaissie.com/
License: GNU General Public License v2
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/* Last revision: June 4, 2011 v0.5.1 */

/*  Copyright 2011  Edward Caissie  (email : edward.caissie@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

    The license for this software can also likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html
*/

/** Credits for jQuery assistance: Trevor Mills www.topquarkproductions.ca */

// WordPress Version testing ...
global $wp_version;
$exit_ver_msg = 'BNS Inline Asides requires a minimum of WordPress 3.0, <a href="http://codex.wordpress.org/Upgrading_WordPress">Please Update!</a>';
if ( version_compare( $wp_version, "3.0", "<" ) ) { // per home_url() function
	exit ( $exit_ver_msg );
}

// Define some constants to save some keying
define( 'BNSIA_PATH', plugin_dir_url( __FILE__ ) );

// Add BNS Inline Asides scripts
function BNSIA_Scripts_and_Styles_Action() {
    /* Enqueue Scripts */
		wp_enqueue_script( 'jquery' );
		// TO-DO: Move scripts into their own folder? ... and localize?
		// wp_enqueue_script( 'bnsia_script', BNSIA_PATH . 'bnsia-script.js', array( 'jquery' ) );
		// wp_localize_script( 'bnsia_script', 'BNSIA_Settings', array( 'variable_1' => "some value", 'variable_2' => "another value" ) );

    /* Enqueue Style Sheets */
  	wp_enqueue_style( 'BNSIA-Style', plugin_dir_url( __FILE__ ) . '/bnsia-style.css', array(), '0.5', 'screen' );
  	wp_enqueue_style( 'BNSIA-Custom-Types', plugin_dir_url( __FILE__ ) . '/bnsia-custom-types.css', array(), '0.5', 'screen' );
}
add_action('wp_enqueue_scripts', 'BNSIA_Scripts_and_Styles_Action');

// Let's begin ...
function bns_inline_asides_shortcode( $atts, $content = null ) {
	extract( shortcode_atts( array(
				       'type'   => 'Aside',
				       'show'   => 'To see the <em>%s</em> click here.',
               'hide'   => 'To hide the <em>%s</em> click here.',
               'status' => 'open',
				       ), $atts ) );

	// clean up shortcode properties
    /** @var $status string */
    $status = esc_attr( strtolower( $status ) );
	if ( $status != "open" ) $status = "closed";
	
	// ... also leave any end-user capitalization for aesthetics
    /** @var $type string */
    $type_class = esc_attr( strtolower( $type ) );
	// ... replace space(s) with a hyphen to create nice CSS classes 
	$type_class = preg_replace( '/\s\s+/', '-', $type_class );
	
	// no need to duplicate the default 'aside' class
	if ( $type_class == 'aside' ) {
		$type_class = '';
	} else {
		$type_class = ' ' . $type_class;
	}

  // TO-DO: Option which style element to leverage, currently manual edits are required to change. Plugin currently only supports <blockquote>, <p> and <span>.
  // TO-DO: The <span> element requires additional review, use at your own risk.	
	$bnsia_theme_element = 'blockquote';
	// $bnsia_theme_element = 'p';
	// $bnsia_theme_element = 'span';

	// The secret sauce ...
    /** @var $show string - used as boolean control */
    /** @var $hide string - used as boolean control */
    $toggle_markup = '<div class="aside-toggler ' . $status . '">'
      . '<span class="open-aside' . $type_class . '">' . sprintf( __( $show ), esc_attr( $type ) ) . '</span>'
      . '<span class="close-aside' . $type_class . '">' . sprintf( __( $hide ), esc_attr( $type ) ) . '</span>
    </div>';
  $return = $toggle_markup . '<' . $bnsia_theme_element . ' class="aside' . $type_class . ' ' . $status . '">' . do_shortcode( $content ) . '</' . $bnsia_theme_element . '>';
  
  static $script_output;
  if ( ! isset( $script_output ) ) {
    $return .= '<script type="text/javascript">
      /* <![CDATA[ */
      jQuery(document).ready(function() {
        jQuery(".aside-toggler").click(function(){
          jQuery(this).toggleClass("open").toggleClass("closed").next("' . $bnsia_theme_element . '.aside").slideToggle("slow",function(){
            jQuery(this).toggleClass("open").toggleClass("closed");
          });
        });    
      });
      /* ]]> */
      </script>';
    $script_output = true;
  }
  return $return;
}

// We're done ... let's wrap this up into a simple shortcode!
add_shortcode( 'aside', 'bns_inline_asides_shortcode' );
?>