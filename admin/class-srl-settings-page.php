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

		// Template hints setting.
		add_settings_field(
			'template_hints',
			__( 'Template hints (filenames; one per line)', 'selective-recaptcha-loader' ),
			array( $this, 'render_template_hints_field' ),
			$this->page_slug,
			'srl_main_section'
		);

		// Badge and disclosure section.
		add_settings_section(
			'srl_badge_section',
			__( 'Badge & Disclosure', 'selective-recaptcha-loader' ),
			array( $this, 'render_badge_section' ),
			$this->page_slug
		);

		// Hide badge and add disclosure.
		add_settings_field(
			'hide_badge_add_disclosure',
			__( 'Hide v3 badge and append disclosure text', 'selective-recaptcha-loader' ),
			array( $this, 'render_hide_badge_field' ),
			$this->page_slug,
			'srl_badge_section'
		);

		// Custom disclosure text.
		add_settings_field(
			'custom_disclosure_text',
			__( 'Custom disclosure text', 'selective-recaptcha-loader' ),
			array( $this, 'render_custom_disclosure_field' ),
			$this->page_slug,
			'srl_badge_section'
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
			if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
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
	 * Render badge section description.
	 */
	public function render_badge_section() {
		echo '<p>' . esc_html__( 'Configure reCAPTCHA v3 badge visibility and disclosure text.', 'selective-recaptcha-loader' ) . '</p>';
	}

	/**
	 * Render mode field.
	 */
	public function render_mode_field() {
		$options = srl()->get_options();
		$mode = isset( $options['mode'] ) ? $options['mode'] : 'auto';
		?>
		<fieldset>
			<label>
				<input type="radio" name="<?php echo esc_attr( $this->option_name ); ?>[mode]" value="auto" <?php checked( $mode, 'auto' ); ?> />
				<?php esc_html_e( 'Auto (detect site-wide forms)', 'selective-recaptcha-loader' ); ?>
			</label><br />
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
			<?php esc_html_e( 'Auto mode detects forms in footer/header areas and switches to Global mode when needed.', 'selective-recaptcha-loader' ); ?>
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
			<?php esc_html_e( 'Enter post IDs, slugs, or regex patterns (one per line). Regex patterns should start and end with forward slashes.', 'selective-recaptcha-loader' ); ?>
		</p>
		<?php
	}

	/**
	 * Render template hints field.
	 */
	public function render_template_hints_field() {
		$options = srl()->get_options();
		$template_hints = isset( $options['template_hints'] ) ? $options['template_hints'] : '';
		?>
		<textarea name="<?php echo esc_attr( $this->option_name ); ?>[template_hints]" rows="3" cols="50" class="regular-text"><?php echo esc_textarea( $template_hints ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Enter template filenames (one per line) that should always load reCAPTCHA. Example: contact.php', 'selective-recaptcha-loader' ); ?>
		</p>
		<?php
	}

	/**
	 * Render hide badge field.
	 */
	public function render_hide_badge_field() {
		$options = srl()->get_options();
		$hide_badge = isset( $options['hide_badge_add_disclosure'] ) ? $options['hide_badge_add_disclosure'] : false;
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( $this->option_name ); ?>[hide_badge_add_disclosure]" value="1" <?php checked( $hide_badge, true ); ?> />
			<?php esc_html_e( 'Hide reCAPTCHA v3 badge and append disclosure text near forms', 'selective-recaptcha-loader' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'This helps comply with reCAPTCHA terms while maintaining good user experience. The badge will be visually minimized and disclosure text will be shown.', 'selective-recaptcha-loader' ); ?>
		</p>
		<?php
	}

	/**
	 * Render custom disclosure text field.
	 */
	public function render_custom_disclosure_field() {
		$options = srl()->get_options();
		$custom_text = isset( $options['custom_disclosure_text'] ) ? $options['custom_disclosure_text'] : '';
		?>
		<textarea name="<?php echo esc_attr( $this->option_name ); ?>[custom_disclosure_text]" rows="3" cols="70" class="regular-text"><?php echo esc_textarea( $custom_text ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Optional custom text. Use {privacy_link} and {tos_link} placeholders. Leave empty to use default text.', 'selective-recaptcha-loader' ); ?>
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

		// Sanitize mode.
		if ( isset( $input['mode'] ) && in_array( $input['mode'], array( 'auto', 'global', 'selective' ), true ) ) {
			$sanitized['mode'] = $input['mode'];
		} else {
			$sanitized['mode'] = 'auto';
		}

		// Sanitize whitelist.
		if ( isset( $input['whitelist'] ) ) {
			$sanitized['whitelist'] = sanitize_textarea_field( $input['whitelist'] );
		}

		// Sanitize template hints.
		if ( isset( $input['template_hints'] ) ) {
			$sanitized['template_hints'] = sanitize_textarea_field( $input['template_hints'] );
		}

		// Sanitize hide badge checkbox.
		$sanitized['hide_badge_add_disclosure'] = isset( $input['hide_badge_add_disclosure'] ) && $input['hide_badge_add_disclosure'];

		// Sanitize custom disclosure text.
		if ( isset( $input['custom_disclosure_text'] ) ) {
			$sanitized['custom_disclosure_text'] = wp_kses_post( $input['custom_disclosure_text'] );
		}

		// Clear relevant transients when settings change.
		delete_transient( 'selective_recaptcha_loader_sitewide_detection_' . get_current_blog_id() );

		return $sanitized;
	}
}