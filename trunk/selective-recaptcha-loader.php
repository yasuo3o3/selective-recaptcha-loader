<?php
/**
 * Plugin Name:       Selective reCAPTCHA Loader for CF7
 * Description:       Load reCAPTCHA only where Contact Form 7 forms are present or on all pages as desired.
 * Version:           0.2
 * Author:            Netservice
 * Author URI:        https://netservice.jp/
 * License:           GPLv2 or later
 * Text Domain:       selective-recaptcha-loader
 * Domain Path:       /languages
 *
 * @package SelectiveRecaptchaLoader
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'SELERELO_VERSION', '0.2' );
define( 'SELERELO_PLUGIN_FILE', __FILE__ );
define( 'SELERELO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SELERELO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class.
 */
class Selective_Recaptcha_Loader {

	/**
	 * Plugin instance.
	 *
	 * @var Selective_Recaptcha_Loader
	 */
	private static $instance = null;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Get plugin instance.
	 *
	 * @since 0.01
	 * @return Selective_Recaptcha_Loader
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
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Initialize plugin.
	 */
	public function init() {
		// Bail if Contact Form 7 is not active.
		if ( ! class_exists( 'WPCF7' ) ) {
			return;
		}

		// Load plugin classes.
		$this->load_classes();

		// Initialize components.
		$this->init_components();
	}

	/**
	 * Load plugin classes.
	 */
	private function load_classes() {
		require_once SELERELO_PLUGIN_DIR . 'inc/class-selerelo-detector.php';
		require_once SELERELO_PLUGIN_DIR . 'inc/class-selerelo-enqueuer.php';
		require_once SELERELO_PLUGIN_DIR . 'inc/functions-template.php';

		if ( is_admin() ) {
			require_once SELERELO_PLUGIN_DIR . 'admin/class-selerelo-settings-page.php';
		}
	}

	/**
	 * Initialize plugin components.
	 */
	private function init_components() {
		// Initialize detector and enqueuer.
		Selerelo_Detector::instance();
		Selerelo_Enqueuer::instance();

		// Initialize admin components.
		if ( is_admin() ) {
			Selerelo_Settings_Page::instance();
		}
	}

	/**
	 * Get plugin option.
	 *
	 * @param string $key Option key.
	 * @param mixed  $default Default value.
	 * @return mixed Option value.
	 */
	public function get_option( $key, $default = null ) {
		if ( empty( $this->options ) ) {
			$this->options = get_option( 'selerelo_settings', array() );
		}

		return isset( $this->options[ $key ] ) ? $this->options[ $key ] : $default;
	}

	/**
	 * Get all plugin options.
	 *
	 * @return array All plugin options.
	 */
	public function get_options() {
		if ( empty( $this->options ) ) {
			$this->options = get_option( 'selerelo_settings', array() );
		}

		return $this->options;
	}

	/**
	 * Update plugin options.
	 *
	 * @param array $options New options.
	 */
	public function update_options( $options ) {
		$this->options = $options;
		update_option( 'selerelo_settings', $options, false );
	}
}

/**
 * Get main plugin instance.
 *
 * @since 0.01
 * @return Selective_Recaptcha_Loader
 */
function selerelo() {
	return Selective_Recaptcha_Loader::instance();
}

// Initialize the plugin.
selerelo();