<?php
/*
Plugin Name: BNS Inline Asides
Plugin URI: http://buynowshop.com/plugins/bns-inline-asides/
Description: This plugin will allow you to style sections of post content with added emphasis by leveraging a style element from the active theme.
Version: 1.2
Text Domain: bns-inline-asides
Author: Edward Caissie
Author URI: http://edwardcaissie.com/
License: GNU General Public License v2
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/**
 * BNS Inline Asides
 *
 * This plugin will allow you to style sections of post content with added
 * emphasis by leveraging a style element from the active theme.
 *
 * @package        BNS_Inline_Asides
 * @version        1.2
 *
 * @link           http://buynowshop.com/plugins/bns-inline-asides/
 * @link           https://github.com/Cais/bns-inline-asides/
 * @link           http://wordpress.org/extend/plugins/bns-inline-asides/
 *
 * @author         Edward Caissie <edward.caissie@gmail.com>
 * @copyright      Copyright (c) 2011-2014, Edward Caissie
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 2, as published by the
 * Free Software Foundation.
 *
 * You may NOT assume that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to:
 *
 *      Free Software Foundation, Inc.
 *      51 Franklin St, Fifth Floor
 *      Boston, MA  02110-1301  USA
 *
 * The license for this software can also likely be found here:
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @version        1.1
 * @date           May 2014
 *
 * @version        1.2
 * @date           November 2014
 */

/** Credits for jQuery assistance: Trevor Mills www.topquarkproductions.ca */

/** Let's begin ... */
class BNS_Inline_Asides {
	/**
	 * Constructor
	 *
	 * @package     BNS_Inline_Asides
	 * @since       0.1
	 *
	 * @internal    Requires WordPress version 3.6
	 * @internal    @uses shortcode_atts - uses optional filter variable
	 *
	 * @uses        (CONSTANT)  WP_CONTENT_DIR
	 * @uses        (GLOBAL)    $wp_version
	 * @uses        add_action
	 * @uses        add_shortcode
	 * @uses        content_url
	 * @uses        plugin_dir_url
	 * @uses        plugin_dir_path
	 *
	 * @version     1.1
	 * @date        May 3, 2014
	 * Corrected textdomain typo
	 * Updated required version to 3.6 due to use of optional filter variable in `shortcode_atts`
	 * Define location for BNS plugin customizations
	 *
	 * @version     1.2
	 * @date        November 3, 2014
	 * Added sanity checks for `BNS_CUSTOM_*` define statements
	 * Corrections for textdomain to use plugin slug
	 */
	function __construct() {
		/**
		 * WordPress version compatibility
		 * Check installed WordPress version for compatibility
		 */
		global $wp_version;
		$exit_message = __( 'BNS Early Adopter requires WordPress version 3.6 or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please Update!</a>', 'bns-inline-asides' );
		if ( version_compare( $wp_version, "3.6", "<" ) ) {
			exit ( $exit_message );
		}
		/** End if - version compare */

		/** Define some constants to save some keying */
		define( 'BNSIA_URL', plugin_dir_url( __FILE__ ) );
		define( 'BNSIA_PATH', plugin_dir_path( __FILE__ ) );

		/** Define location for BNS plugin customizations */
		if ( ! defined( 'BNS_CUSTOM_PATH' ) ) {
			define( 'BNS_CUSTOM_PATH', WP_CONTENT_DIR . '/bns-customs/' );
		}
		/** End if - not defined */
		if ( ! defined( 'BNS_CUSTOM_URL' ) ) {
			define( 'BNS_CUSTOM_URL', content_url( '/bns-customs/' ) );
		}
		/** End if - not defined */

		/** Enqueue Scripts and Styles */
		add_action(
			'wp_enqueue_scripts', array(
				$this,
				'scripts_and_styles'
			)
		);

		/**
		 * Add Shortcode
		 * @example  [aside]text[/aside]
		 * @internal default type="Aside"
		 * @internal default element='' (an empty string)
		 * @internal default status="open"
		 * @internal default show="To see the <em>%s</em> click here."
		 * @internal default hide="To hide the <em>%s</em> click here."
		 */
		add_shortcode( 'aside', array( $this, 'bns_inline_asides_shortcode' ) );

	}
	/** End function - constructor */


	/**
	 * Enqueue Plugin Scripts and Styles
	 *
	 * Adds plugin stylesheet and allows for custom stylesheet to be added by end-user.
	 *
	 * @package BNS_Inline_Asides
	 * @since   0.4.1
	 *
	 * @uses    (CONSTANT)   BNS_CUSTOM_PATH
	 * @uses    (CONSTANT)   BNS_CUSTOM_URL
	 * @uses    (CONSTANT)   BNSIA_PATH
	 * @uses    (CONSTANT)   BNSIA_URL
	 * @uses    BNS_Inline_Asides::plugin_data
	 * @uses    wp_enqueue_script
	 * @uses    wp_enqueue_style
	 *
	 * @version 1.0
	 * @date    April 3, 2013
	 * Adjusted path to scripts and styles files
	 * Removed direct jQuery enqueue
	 *
	 * @version 1.0.3
	 * @date    December 28, 2013
	 * Added functional option to put `bnsia-custom-types.css` in `/wp-content/` folder
	 *
	 * @version 1.1
	 * @date    May 4, 2014
	 * Apply `plugin_data` method
	 * Moved JavaScript enqueue to footer
	 * Moved custom CSS folder location to `/wp-content/bns-customs/`
	 *
	 * @version 1.2
	 * @date    November 3, 2014
	 * Renamed from `BNSIA_Scripts_and_Styles` to `scripts_and_styles`
	 */
	function scripts_and_styles() {
		/** @var object $bnsia_data - holds the plugin header data */
		$bnsia_data = $this->plugin_data();

		/** Enqueue Scripts */
		/** Enqueue toggling script which calls jQuery as a dependency */
		wp_enqueue_script( 'bnsia_script', BNSIA_URL . 'js/bnsia-script.js', array( 'jquery' ), $bnsia_data['Version'], true );

		/** Enqueue Style Sheets */
		wp_enqueue_style( 'BNSIA-Style', BNSIA_URL . 'css/bnsia-style.css', array(), $bnsia_data['Version'], 'screen' );

		/** This location is not recommended as it is not upgrade safe. */
		if ( is_readable( BNSIA_PATH . 'bnsia-custom-types.css' ) ) {
			wp_enqueue_style( 'BNSIA-Custom-Types', BNSIA_URL . 'bnsia-custom-types.css', array(), $bnsia_data['Version'], 'screen' );
		}
		/** End if - is readable */

		/** This location is recommended as upgrade safe */
		if ( is_readable( BNS_CUSTOM_PATH . 'bnsia-custom-types.css' ) ) {
			wp_enqueue_style( 'BNSIA-Custom-Types', BNS_CUSTOM_URL . 'bnsia-custom-types.css', array(), $bnsia_data['Version'], 'screen' );
		}
		/** End if - is readable */

	}
	/** End function - scripts and styles */


	/**
	 * BNS Inline Asides Shortcode
	 *
	 * @package    BNS_Inline_Asides
	 * @since      0.1
	 *
	 * @param        $atts    - shortcode attributes
	 * @param   null $content - the content
	 *
	 * @uses       BNS_Inline_Asides::bnsia_theme_element
	 * @uses       _x
	 * @uses       do_shortcode
	 * @uses       sanitize_html_class
	 * @uses       shortcode_atts
	 * @uses       wp_localize_script
	 *
	 * @return  string
	 *
	 * @version    0.9
	 * @date       January 4, 2013
	 * Moved JavaScript into its own file and pass the element variable via
	 * wp_localize_script
	 *
	 * @version    1.0
	 * @date       Rat Day, 2013
	 * Added missing `bnsia` class to theme elements other than default
	 * Refactored $bnsia_element to simply $element
	 * Removed global variable $bnsia_element as not used
	 *
	 * @version    1.0.2
	 * @date       August 3, 2013
	 * Added dynamic filter parameter
	 *
	 * @version    1.0.3
	 * @date       December 30, 2013
	 * Code reductions (see `replace_spaces` usage)
	 *
	 * @version    1.2
	 * @date       November 3, 2014
	 * Added `_x` i18n implementation to `show` and `hide` default messages
	 * Replaced `BNS_Inline_Asides::replace_spaces` with `sanitize_html_class` functionality
	 */
	function bns_inline_asides_shortcode( $atts, $content = null ) {
		extract(
			shortcode_atts(
				array(
					'type'    => 'Aside',
					'element' => '',
					'show'    => _x( 'To see the <em>%s</em> click here.', '%s is a PHP replacement variable', 'bns-inline-asides' ),
					'hide'    => _x( 'To hide the <em>%s</em> click here.', '%s is a PHP replacement variable', 'bns-inline-asides' ),
					'status'  => 'open',
				),
				$atts, 'aside'
			)
		);

		/** clean up shortcode properties */
		/** @var string $status - used as toggle switch */
		$status = esc_attr( strtolower( $status ) );
		if ( $status != "open" ) {
			$status = "closed";
		}
		/** End if - status is not open */

		/**
		 * @var string $type_class - leaves any end-user capitalization for aesthetics
		 * @var string $type       - Aside|end-user defined
		 */
		$type_class = sanitize_html_class( strtolower( $type ), 'aside' );

		/** no need to duplicate the default 'aside' class */
		if ( $type_class == 'aside' ) {
			$type_class = '';
		} else {
			$type_class = ' ' . $type_class;
		}
		/** End if - type class - aside */

		/** @var $element - default is null|empty */
		$element = sanitize_html_class( strtolower( $element ), '' );

		// The secret sauce ...
		/** @var string $show - used as boolean control */
		/** @var string $hide - used as boolean control */
		$toggle_markup = '<div class="aside-toggler ' . $status . '">'
		                 . '<span class="open-aside' . $type_class . '">' . sprintf( __( $show ), esc_attr( $type ) ) . '</span>'
		                 . '<span class="close-aside' . $type_class . '">' . sprintf( __( $hide ), esc_attr( $type ) ) . '</span>
                         </div>';
		if ( $this->bnsia_theme_element( $element ) == '' ) {
			$return = $toggle_markup . '<div class="bnsia aside' . $type_class . ' ' . $status . '">' . do_shortcode( $content ) . '</div>';
		} else {
			$return = $toggle_markup . '<' . $this->bnsia_theme_element( $element ) . ' class="bnsia aside' . $type_class . ' ' . $status . '">' . do_shortcode( $content ) . '</' . $this->bnsia_theme_element( $element ) . '>';
		}
		/** End if - theme element - null */

		/** Grab the element of choice and push it through the JavaScript */
		wp_localize_script( 'bnsia_script', 'element', $this->bnsia_theme_element( $element ) );

		return $return;

	}
	/** End function - shortcode */


	/**
	 * Replace Spaces
	 * Takes a string and replaces the spaces with a single hyphen by default
	 *
	 * @package     BNS_Inline_asides
	 * @since       0.8
	 *
	 * @internal    Original code from Opus Primus by Edward "Cais" Caissie ( mailto:edward.caissie@gmail.com )
	 *
	 * @param   string $text
	 * @param   string $replacement
	 *
	 * @return  string - class
	 *
	 * @deprecated  1.2
	 * @date        November 3, 2014
	 * Replaced with `sanitize_html_class` functionality
	 */
	function replace_spaces( $text, $replacement = '-' ) {
		/** @var $new_text - initial text set to lower case */
		$new_text = esc_attr( strtolower( $text ) );
		/** replace whitespace with a single space */
		$new_text = preg_replace( '/\s\s+/', ' ', $new_text );
		/** replace space with a hyphen to create nice CSS classes */
		$new_text = preg_replace( '/\\040/', $replacement, $new_text );

		/** Return the string with spaces replaced by the replacement variable */

		return $new_text;

	}
	/** End function - replace spaces */


	/**
	 * BNSIA Theme Element
	 * Plugin currently supports the following HTML tags: aside, blockquote,
	 * code, h1 through h6, pre, and q; or uses the default <div class = bnsia>
	 *
	 * @package  BNS_Inline_Asides
	 * @since    0.6
	 *
	 * @param    (global) $element - string taken from shortcode $atts( 'element' )
	 *
	 * @internal The HTML `p` tag is not recommended at this time (version 0.8),
	 * especially for text that spans multiple paragraphs
	 *
	 * @return  string - accepted HTML tag | empty
	 *
	 * @version  0.6.1
	 * @date     November 22, 2011
	 * Corrected issue with conditional - Fatal error: Cannot re-declare bnsia_theme_element()
	 *
	 * @version  0.8
	 * @date     November 15, 2012
	 * Accept the shortcode $att( 'element' ) and return the value for use with
	 * the output strings if it is an accepted HTML tag
	 *
	 * @version  1.0
	 * @date     Rat Day, 2013
	 * Use an array of elements rather than a convoluted if statement
	 */
	function bnsia_theme_element( $element ) {

		/** @var $accepted_elements - array of block level container elements */
		$accepted_elements = array(
			'aside',
			'blockquote',
			'code',
			'h1',
			'h2',
			'h3',
			'h4',
			'h5',
			'h6',
			'pre',
			'q'
		);

		/**
		 * Check if an element has been used: if not, get out; otherwise,
		 * check if the element is accepted or return nothing if it is not.
		 */
		if ( empty( $element ) ) {
			return '';

		} elseif ( in_array( $element, $accepted_elements ) ) {
			return $element;

		} else {
			return '';

		}
		/** End if - empty - element */

	}
	/** End function - theme element */


	/**
	 * Plugin Data
	 * Returns the plugin header data as an array
	 *
	 * @package    BNS_Inline_Asides
	 * @since      1.1
	 *
	 * @uses       get_plugin_data
	 *
	 * @return array
	 */
	function plugin_data() {

		/** Call the wp-admin plugin code */
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		/** @var $plugin_data - holds the plugin header data */
		$plugin_data = get_plugin_data( __FILE__ );

		return $plugin_data;

	}
	/** End function - plugin data */


}

/** End class - inline asides */


/** @var $bns_inline_asides - instantiate the class */
$bns_inline_asides = new BNS_Inline_Asides();