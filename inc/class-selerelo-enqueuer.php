<?php
/**
 * Asset enqueuer class.
 *
 * @package SelectiveRecaptchaLoader
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueuer class for controlling reCAPTCHA assets.
 */
class Selerelo_Enqueuer {

	/**
	 * Class instance.
	 *
	 * @var Selerelo_Enqueuer
	 */
	private static $instance = null;

	/**
	 * Get class instance.
	 *
	 * @return Selerelo_Enqueuer
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'control_recaptcha_assets' ), 120 );
	}

	/**
	 * Control reCAPTCHA asset loading.
	 */
	public function control_recaptcha_assets() {
		// Skip if not on frontend.
		if ( is_admin() || wp_doing_ajax() || wp_is_json_request() ) {
			return;
		}

		// Skip if Contact Form 7 is not active.
		if ( ! class_exists( 'WPCF7' ) ) {
			return;
		}

		$detector = Selerelo_Detector::instance();

		// If we shouldn't load reCAPTCHA on this page, dequeue the assets.
		if ( ! $detector->should_load_recaptcha() ) {
			$this->dequeue_recaptcha_assets();
		}
	}

	/**
	 * Dequeue and deregister reCAPTCHA assets.
	 */
	private function dequeue_recaptcha_assets() {
		$handles = apply_filters( 'selerelo_recaptcha_handles', array(
			'wpcf7-recaptcha',
			'google-recaptcha',
		) );

		foreach ( $handles as $handle ) {
			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
		}
	}



}