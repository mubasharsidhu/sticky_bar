<?php
/**
 * Plugin Name: Sticky Bar
 * Plugin URI:
 * Description: The plugin helps you mark any Post as Sticky bar easily.
 * Version: 1.0
 * Author: Muhammad Mubashar Hussain
 * Author URI:
 * Text Domain: stickybar
 *
 * @package Sticky_Bar
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'SBR_BASENAME' ) ) {
	define( 'SBR_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'SBR_ABSPATH' ) ) {
	define( 'SBR_ABSPATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'SBR_BASEPATH' ) ) {
	define( 'SBR_BASEPATH', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'SBR_VERSION' ) ) {
	define( 'SBR_VERSION', 1.0 );
}

// Load core packages and the autoloader.
require SBR_ABSPATH . '/inc/sbr-loader.php';
