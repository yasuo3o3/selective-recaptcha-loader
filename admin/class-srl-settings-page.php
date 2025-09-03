<?php
/**
 * Settings page class.
 *
 * @package SelectiveRecaptchaLoader
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings page class.
 */
class SRL_Settings_Page {

	/**
	 * Class instance.
	 *
	 * @var SRL_Settings_Page
	 */
	private static $instance = null;

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	private $page_slug = 'srl-settings';

	/**
	 * Settings group name.
	 *
	 * @var string
	 */
	private $settings_group = 'srl_settings_group';

	/**
	 * Option name.
	 *
	 * @var string
	 */
	private $option_name = 'srl_settings';

	/**
	 * Get class instance.
	 *
	 * @return SRL_Settings_Page
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
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add admin menu item.
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'Selective reCAPTCHA', 'selective-recaptcha-loader' ),
			__( 'Selective reCAPTCHA', 'selective-recaptcha-loader' ),
			'manage_options',
			$this->page_slug,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		register_setting(
			$this->settings_group,
			$this->option_name,
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'srl_main_section',
			__( 'Main Settings', 'selective-recaptcha-loader' ),
			array( $this, 'render_main_section' ),
			$this->page_slug
		);

		// Mode setting.
		add_settings_field(
			'mode',
			__( 'Mode', 'selective-recaptcha-loader' ),
			array( $this, 'render_mode_field' ),
			$this->page_slug,
			'srl_main_section'
		);

		// Whitelist setting.
		add_settings_field(
			'whitelist',
			__( 'Whitelist (IDs / slugs / regex; one per line)', 'selective-recaptcha-loader' ),
			array( $this, 'render_whitelist_field' ),
			$this->page_slug,
			'srl_main_section'
		);


	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'selective-recaptcha-loader' ) );
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Selective reCAPTCHA Loader Settings', 'selective-recaptcha-loader' ); ?></h1>

			<?php
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $_GET['settings-updated'] ) && sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) ) ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings saved.', 'selective-recaptcha-loader' ); ?></p>
				</div>
				<?php
			}
			?>

			<?php if ( ! class_exists( 'WPCF7' ) ) : ?>
				<div class="notice notice-warning">
					<p><?php esc_html_e( 'Contact Form 7 is not active. This plugin requires Contact Form 7 to function properly.', 'selective-recaptcha-loader' ); ?></p>
				</div>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( $this->settings_group );
				do_settings_sections( $this->page_slug );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render main section description.
	 */
	public function render_main_section() {
		echo '<p>' . esc_html__( 'Configure how and when reCAPTCHA assets are loaded for Contact Form 7 forms.', 'selective-recaptcha-loader' ) . '</p>';
	}


	/**
	 * Render mode field.
	 */
	public function render_mode_field() {
		$options = srl()->get_options();
		$mode = isset( $options['mode'] ) ? $options['mode'] : 'selective';
		?>
		<fieldset>
			<label>
				<input type="radio" name="<?php echo esc_attr( $this->option_name ); ?>[mode]" value="global" <?php checked( $mode, 'global' ); ?> />
				<?php esc_html_e( 'Global (load reCAPTCHA on all pages)', 'selective-recaptcha-loader' ); ?>
			</label><br />
			<label>
				<input type="radio" name="<?php echo esc_attr( $this->option_name ); ?>[mode]" value="selective" <?php checked( $mode, 'selective' ); ?> />
				<?php esc_html_e( 'Selective (only on pages with CF7 forms)', 'selective-recaptcha-loader' ); ?>
			</label>
		</fieldset>
		<p class="description">
			<?php esc_html_e( 'Choose Global to load reCAPTCHA on all pages, or Selective to load only on pages with Contact Form 7 forms.', 'selective-recaptcha-loader' ); ?>
		</p>
		<?php
	}

	/**
	 * Render whitelist field.
	 */
	public function render_whitelist_field() {
		$options = srl()->get_options();
		$whitelist = isset( $options['whitelist'] ) ? $options['whitelist'] : '';
		?>
		<textarea name="<?php echo esc_attr( $this->option_name ); ?>[whitelist]" rows="5" cols="50" class="regular-text"><?php echo esc_textarea( $whitelist ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Pages matching the given Post ID, slug, or regular expression will always load reCAPTCHA. One entry per line. Regex must start and end with a slash.', 'selective-recaptcha-loader' ); ?><br><br>
			<?php esc_html_e( 'Examples:', 'selective-recaptcha-loader' ); ?><br>
			- <?php esc_html_e( 'Post ID (example: 123)', 'selective-recaptcha-loader' ); ?><br>
			- <?php esc_html_e( 'Page slug (example: contact)', 'selective-recaptcha-loader' ); ?><br>
			- <?php esc_html_e( 'Regular expression (example: /^https:\/\/example\.com\/custom/)', 'selective-recaptcha-loader' ); ?>
		</p>
		<?php
	}




	/**
	 * Sanitize settings.
	 *
	 * @param array $input Input data.
	 * @return array Sanitized data.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		// マイグレーション: 既存のautoモードをselectiveに変換
		$current_options = srl()->get_options();
		if ( isset( $current_options['mode'] ) && 'auto' === $current_options['mode'] ) {
			// 初回ロード時にautoをselectiveにマイグレーション
			$input['mode'] = 'selective';
		}

		// Sanitize mode.
		if ( isset( $input['mode'] ) && in_array( $input['mode'], array( 'global', 'selective' ), true ) ) {
			$sanitized['mode'] = $input['mode'];
		} else {
			$sanitized['mode'] = 'selective';
		}

		// Sanitize whitelist.
		if ( isset( $input['whitelist'] ) ) {
			$sanitized['whitelist'] = sanitize_textarea_field( $input['whitelist'] );
		}


		// 旧トランジェントの削除（Auto関連・バッジ関連）
		delete_transient( 'selective_recaptcha_loader_sitewide_detection_' . get_current_blog_id() );
		delete_transient( 'srl_sitewide_detection' );

		return $sanitized;
	}
}