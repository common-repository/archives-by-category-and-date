<?php
/**
 * Plugin Name: Archives by Category and Date
 * Description: This plugin shows archives in a categorized way that is archives are categorized under category name and date. It filters archives based on that category and clicked date when they are being displayed in archives page.
 * Plugin URI: https://wensolutions.com/plugins/archives-by-category-and-date/
 * Author:      WEN Solutions
 * Author URI:  http://wensolutions.com
 * Version:           1.0.4
 * Requires at least: 3.5
 * Requires PHP: 5.6
 * Tested up to: 6.2
 * License: GPL2
 * Text Domain: archives-category-date
 * Domain Path: /languages
 *
 * @package  Archives by Category and Date
 */

/*
* Exit if accessed directly.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin base URL.
define( 'ACD_BASE_URL', plugin_dir_url( __FILE__ ) );

// Plugin base path.
define( 'ACD_BASE_PATH', dirname( __FILE__ ) );

/**
 * Enqueue plugin style.
 *
 * @since 1.0.0
 */
function archives_category_date_load_plugin_css() {
	wp_enqueue_style( 'acd-style', ACD_BASE_URL . 'css/acd-style.css' );
}
add_action( 'wp_enqueue_scripts', 'archives_category_date_load_plugin_css' );


/**
 * Function to get substring between 2 characters.
 *
 * @param string $string  String to be stripped.
 * @param string $start First delimeter.
 * @param string $end Second delimeter.
 * @return string Substring between 2 delimeters.
 * @since 1.0.0
 */
function archives_category_date_extractstring( $string, $start, $end ) {
	$string = ' '.$string;
	$ini = strpos( $string, $start );
	if ( 0 === $ini ) {
		return '';
	}
	$ini += strlen( $start );
	$len = strpos( $string, $end, $ini ) - $ini;
	return substr( $string, $ini, $len );
}


/**
 * Validates month & year retrieved from url.
 * Filter to show posts from the given category as well as date in Archive page.
 *
 * @param object $query Global variable to filter the posts on archives page.
 * @since 1.0.0
 */
function archives_category_date_posts_by_date( $query ) {
	$archives_category_date_category = '';
	$archives_category_date_month_no = '';
	$archives_category_date_year = '';

	if ( isset( $_GET['date'] ) ) { // Input var okay.
		$date = sanitize_text_field( wp_unslash( $_GET['date'] ) ); // Input var okay.
		$month = explode( '-', $date, 2 );
			$month_slug_type = get_archives_category_date_month_slug_type();
			if ( 'numeric' !== $month_slug_type ) {
				$archives_category_date_month_no = date( 'm', strtotime( $month[0] ) ); // Changing month-name to its numeric value.
			} else {
				$archives_category_date_month_no = $month[0];
			}
			$archives_category_date_year = substr( $date, strpos( $date, '-' ) + 1 );
			$str = esc_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ); // Input var okay.
			$array = explode( '/', $str, 3 ); // Stripping url to get category name from url.
			$str = $array[2];
			$cate = archives_category_date_extractstring( $str, '/', '/' ); // Getting category name between two delimeters.
			$archives_category_date_category = get_cat_ID( $cate ); // Category id from name.
	}

	if ( ! is_admin() && $query->is_main_query() ) {
		if ( is_archive() ) {
			// Date validation.
			$result = preg_match( '/^([0-9]+)$/',$archives_category_date_year );
			$res = strlen( $archives_category_date_year );

			if ( $archives_category_date_month_no > 0 && $archives_category_date_month_no < 13 && is_numeric( $archives_category_date_month_no ) ) {
				if ( $result && 4 === $res ) {

					$query->set( 'cat', $archives_category_date_category );
					$query->set( 'year', $archives_category_date_year );
					$query->set( 'monthnum', $archives_category_date_month_no );

				}
			}
		}
	}
}

add_action( 'pre_get_posts', 'archives_category_date_posts_by_date' );

/**
 * Function will return the Month Slug Type.
 *
 * @return String Month Slug.
 */
function get_archives_category_date_month_slug_type() {
	return apply_filters( 'get_archives_category_date_month_slug_type', 'abbrev' ); // options : abbrev / numeric
}

/**
 * Widget file included.
 */
require_once( ACD_BASE_PATH . '/widget/acd-widget.php' );
