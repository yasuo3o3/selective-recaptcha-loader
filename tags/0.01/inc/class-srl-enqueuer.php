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
class SRL_Enqueuer {

	/**
	 * Class instance.
	 *
	 * @var SRL_Enqueuer
	 */
	private static $instance = null;

	/**
	 * Get class instance.
	 *
	 * @return SRL_Enqueuer
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
		add_action( 'wp_footer', array( $this, 'add_disclosure_text' ), 999 );
		add_action( 'wp_head', array( $this, 'add_badge_css' ) );
	}

	/**
	 * Control reCAPTCHA asset loading.
	 */
	public function control_recaptcha_assets() {
		// Skip if not on frontend.
		if ( is_admin() || is_login() || wp_doing_ajax() || wp_is_json_request() ) {
			return;
		}

		// Skip if Contact Form 7 is not active.
		if ( ! class_exists( 'WPCF7' ) ) {
			return;
		}

		$detector = SRL_Detector::instance();

		// If we shouldn't load reCAPTCHA on this page, dequeue the assets.
		if ( ! $detector->should_load_recaptcha() ) {
			$this->dequeue_recaptcha_assets();
		}
	}

	/**
	 * Dequeue and deregister reCAPTCHA assets.
	 */
	private function dequeue_recaptcha_assets() {
		$handles = apply_filters( 'srl_recaptcha_handles', array(
			'wpcf7-recaptcha',
			'google-recaptcha',
		) );

		foreach ( $handles as $handle ) {
			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
		}
	}

	/**
	 * Add reCAPTCHA disclosure text to footer.
	 */
	public function add_disclosure_text() {
		// Skip if disclosure is not enabled.
		if ( ! srl()->get_option( 'hide_badge_add_disclosure', false ) ) {
			return;
		}

		// Skip if not on frontend.
		if ( is_admin() || is_login() ) {
			return;
		}

		// Skip if reCAPTCHA should not be loaded.
		$detector = SRL_Detector::instance();
		if ( ! $detector->should_load_recaptcha() ) {
			return;
		}

		// Check if CF7 forms are actually present on the page.
		if ( ! $detector->is_form_page() && ! $detector->is_sitewide_form() ) {
			return;
		}

		$disclosure_html = $this->get_disclosure_html();
		if ( ! empty( $disclosure_html ) ) {
			echo $disclosure_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML is escaped in get_disclosure_html()
		}
	}

	/**
	 * Get reCAPTCHA disclosure HTML.
	 *
	 * @return string Disclosure HTML.
	 */
	public function get_disclosure_html() {
		$custom_text = srl()->get_option( 'custom_disclosure_text', '' );

		if ( ! empty( $custom_text ) ) {
			$text = $custom_text;
		} else {
			$text = __( 'This site is protected by reCAPTCHA and the Google {privacy_link} and {tos_link} apply.', 'selective-recaptcha-loader' );
		}

		// Replace placeholders.
		$privacy_link = sprintf(
			'<a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_html__( 'Privacy Policy', 'selective-recaptcha-loader' )
		);

		$tos_link = sprintf(
			'<a href="https://policies.google.com/terms" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_html__( 'Terms of Service', 'selective-recaptcha-loader' )
		);

		$text = str_replace(
			array( '{privacy_link}', '{tos_link}' ),
			array( $privacy_link, $tos_link ),
			$text
		);

		$html = sprintf(
			'<div class="srl-recaptcha-disclosure" style="font-size: 11px; color: #666; margin-top: 8px;">%s</div>',
			$text
		);

		return apply_filters( 'srl_disclosure_html', $html );
	}

	/**
	 * Add CSS to minimize reCAPTCHA badge.
	 */
	public function add_badge_css() {
		// Skip if badge hiding is not enabled.
		if ( ! srl()->get_option( 'hide_badge_add_disclosure', false ) ) {
			return;
		}

		// reCAPTCHA が読み込まれないページでは CSS も不要
		$detector = SRL_Detector::instance();
		if ( ! $detector->should_load_recaptcha() ) {
			return;
		}

		// Skip if not on frontend.
		if ( is_admin() || is_login() ) {
			return;
		}

		$css_url = SRL_PLUGIN_URL . 'assets/badge.css?ver=' . SRL_VERSION;
		wp_enqueue_style( 'srl-badge-css', $css_url, array(), SRL_VERSION );
	}
}