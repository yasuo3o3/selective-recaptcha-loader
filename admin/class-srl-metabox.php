<?php
/**
 * Metabox class.
 *
 * @package SelectiveRecaptchaLoader
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Metabox class for per-post reCAPTCHA settings.
 */
class SRL_Metabox {

	/**
	 * Class instance.
	 *
	 * @var SRL_Metabox
	 */
	private static $instance = null;

	/**
	 * Meta key for force load setting.
	 *
	 * @var string
	 */
	private $meta_key = 'srl_force_load';

	/**
	 * Nonce action.
	 *
	 * @var string
	 */
	private $nonce_action = 'srl_metabox_nonce';

	/**
	 * Get class instance.
	 *
	 * @return SRL_Metabox
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
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
	}

	/**
	 * Add meta box.
	 */
	public function add_meta_box() {
		$post_types = get_post_types( array( 'public' => true ), 'names' );

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'srl-metabox',
				__( 'Selective reCAPTCHA', 'selective-recaptcha-loader' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render meta box.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_meta_box( $post ) {
		// Add nonce field.
		wp_nonce_field( $this->nonce_action, 'srl_metabox_nonce' );

		// Get current value.
		$force_load = get_post_meta( $post->ID, $this->meta_key, true );
		$checked = ! empty( $force_load );

		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( $this->meta_key ); ?>" value="1" <?php checked( $checked, true ); ?> />
			<?php esc_html_e( 'Load reCAPTCHA for this page', 'selective-recaptcha-loader' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Force reCAPTCHA to load on this page even if no Contact Form 7 forms are detected.', 'selective-recaptcha-loader' ); ?>
		</p>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_meta_box( $post_id ) {
		// Skip autosaves.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Skip revisions.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// nonce 検証を最優先で実行
		if ( ! isset( $_POST['srl_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['srl_metabox_nonce'], $this->nonce_action ) ) {
			return;
		}

		// Check permissions.
		$post_type = get_post_type( $post_id );
		$post_type_object = get_post_type_object( $post_type );
		
		if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->edit_post, $post_id ) ) {
			return;
		}

		// Save or delete meta value.
		if ( isset( $_POST[ $this->meta_key ] ) && $_POST[ $this->meta_key ] ) {
			update_post_meta( $post_id, $this->meta_key, '1' );
		} else {
			delete_post_meta( $post_id, $this->meta_key );
		}
	}
}