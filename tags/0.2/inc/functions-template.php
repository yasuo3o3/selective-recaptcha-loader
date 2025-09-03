<?php
/**
 * Template functions.
 *
 * @package SelectiveRecaptchaLoader
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if reCAPTCHA should be loaded on current page.
 *
 * @return bool True if reCAPTCHA should be loaded.
 */
function srl_should_load_recaptcha() {
	if ( ! class_exists( 'WPCF7' ) ) {
		return false;
	}

	$detector = SRL_Detector::instance();
	return $detector->should_load_recaptcha();
}

/**
 * Check if current page has CF7 forms.
 *
 * @param int|WP_Post|null $post_id Post ID or post object.
 * @return bool True if forms are present.
 */
function srl_is_form_page( $post_id = null ) {
	if ( ! class_exists( 'WPCF7' ) ) {
		return false;
	}

	$detector = SRL_Detector::instance();
	return $detector->is_form_page( $post_id );
}


/**
 * Get plugin option.
 *
 * @param string $key Option key.
 * @param mixed  $default Default value.
 * @return mixed Option value.
 */
function srl_get_option( $key, $default = null ) {
	return srl()->get_option( $key, $default );
}

/**
 * Get all plugin options.
 *
 * @return array All plugin options.
 */
function srl_get_options() {
	return srl()->get_options();
}

/**
 * Get current plugin mode.
 *
 * @return string Current mode (global, selective).
 */
function srl_get_mode() {
	return srl()->get_option( 'mode', 'selective' );
}


/**
 * Check if Contact Form 7 is active and compatible.
 *
 * @return bool True if CF7 is compatible.
 */
function srl_is_cf7_compatible() {
	if ( ! class_exists( 'WPCF7' ) ) {
		return false;
	}

	// Check minimum CF7 version (5.8+).
	if ( defined( 'WPCF7_VERSION' ) ) {
		return version_compare( WPCF7_VERSION, '5.8', '>=' );
	}

	// Fallback if version constant is not defined.
	return true;
}

/**
 * Get plugin version.
 *
 * @return string Plugin version.
 */
function srl_get_version() {
	return SRL_VERSION;
}

/**
 * Check if current request is frontend.
 *
 * @return bool True if frontend request.
 */
function srl_is_frontend() {
	return ! is_admin() && ! wp_doing_ajax() && ! wp_is_json_request();
}