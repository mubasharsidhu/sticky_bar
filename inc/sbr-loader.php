<?php
/**
 * Sticky Bar Manager
 *
 * @package    Sticky_Bar
 * @subpackage Sticky_Bar/inc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * This hook `sticky_bar_loaded` triggers when WP has loaded all the plugins.
 * sbr_on_plugins_loaded
 *
 * @return void
 */
function sbr_on_plugins_loaded() {
	do_action( 'sticky_bar_loaded' );
}
add_action( 'plugins_loaded', 'sbr_on_plugins_loaded', -1 );


/**
 * WP init hook.
 *
 * @return void
 */
function sbr_init() {
	sbr_includes();
}
add_action( 'init', 'sbr_init' );


/**
 * Include the required files used in backend and the frontend.
 *
 * @return void
 */
function sbr_includes() {
	// Backend Files.
	require_once SBR_ABSPATH . '/inc/admin/class-sbr-admin-options.php';
	require_once SBR_ABSPATH . '/inc/admin/class-sbr-admin-metabox.php';
}


/**
 * Enqueue scripts for admin panel
 *
 * @return void
 */
function sbr_admin_enqueue_scripts() {
	$screen = get_current_screen();

	if ( is_admin() && $screen && ( 'settings_page_sbr-settings' === $screen->id || 'post' === $screen->id ) ) {
		// Add the color picker css file.
		wp_enqueue_style( 'wp-color-picker' );

		wp_register_script(
			'stickybar',
			SBR_BASEPATH . 'assets/js/sticky-bar.js',
			array( 'jquery', 'wp-color-picker' ),
			SBR_VERSION,
			true
		);
		wp_enqueue_script( 'stickybar' );
	}
}
add_action( 'admin_enqueue_scripts', 'sbr_admin_enqueue_scripts' );


/**
 * Get the sticky bar if available
 *
 * @return WP_Post|null|false
 */
function sbr_get_sticky_bar() {
	$sbr_options = (array) get_option( 'sbr_sticky_bar' );

	if ( isset( $sbr_options['sbr_is_active'] ) && 'yes' === $sbr_options['sbr_is_active'] && isset( $sbr_options['post_id'] ) ) {
		if (
			isset( $sbr_options['sbr_is_expirable'] ) && 'yes' === $sbr_options['sbr_is_expirable'] && // Sticky bar is expireable.
			isset( $sbr_options['sbr_expiry'] ) && '' !== $sbr_options['sbr_expiry'] // Check if expiry is available.
		) {

			$expiry_time  = strtotime( $sbr_options['sbr_expiry'] );
			$current_time = current_time( 'timestamp' );

			if ( $current_time > $expiry_time ) { // Sticky bar expired.
				update_option( 'sbr_sticky_bar', array() );
				return false;
			}
		}
		return get_post( $sbr_options['post_id'] );
	}

	return false;

}


/**
 * Sticky Bar HTML Print.
 *
 * @return void
 */
function sbr_the_sticky_bar() {
	$sbr_options       = get_option( 'sbr_options' );
	$mb_options        = get_option( 'sbr_sticky_bar' );
	$sticky_bar        = sbr_get_sticky_bar();
	$sbr_options_title = isset( $sbr_options['title'] ) ? $sbr_options['title'] : '';
	$background        = isset( $sbr_options['background'] ) ? 'background:' . $sbr_options['background'] . ';' : 'background:#1e73be;';
	$color             = isset( $sbr_options['color'] ) ? 'color:' . $sbr_options['color'] . ';' : 'color:#ffffff;';

	if ( $sticky_bar && 'publish' === $sticky_bar->post_status ) {
		$title = ( isset( $mb_options['sbr_custom_title'] ) && '' !== trim( $mb_options['sbr_custom_title'] ) ) ? $mb_options['sbr_custom_title'] : $sticky_bar->post_title;
		$link  = ( false === is_admin() ) ? get_the_permalink( $sticky_bar->ID ) : get_edit_post_link( $sticky_bar->ID );
		$style = ( false === is_admin() ) ? 'width:100%;position:fixed;' : 'width:95%;font-size:16px;';
		echo '
		<center>
			<div style="padding:10px;' . esc_attr( $style ) . esc_attr( $background ) . esc_attr( $color ) . '">' .
				esc_html( $sbr_options_title ) . ': <a href="' . esc_url( $link ) . '" style="' . esc_attr( $color ) . '">' . esc_html( $title ) . '</a>
			</div>
		</center>';
	}
}
add_action( 'wp_body_open', 'sbr_the_sticky_bar' );
