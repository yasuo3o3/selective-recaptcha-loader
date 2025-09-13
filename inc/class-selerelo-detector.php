<?php
/**
 * Form detection class.
 *
 * @package SelectiveRecaptchaLoader
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detector class for identifying CF7 forms.
 */
class Selerelo_Detector {

	/**
	 * Class instance.
	 *
	 * @var Selerelo_Detector
	 */
	private static $instance = null;

	/**
	 * Cache for form detection results.
	 *
	 * @var array
	 */
	private $cache = array();


	/**
	 * Get class instance.
	 *
	 * @return Selerelo_Detector
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
		// コンストラクタは空にする（Autoモードのsite-wide検出を削除）
	}

	/**
	 * Check if current page has CF7 forms.
	 *
	 * @param int|WP_Post|null $post_id Post ID or post object.
	 * @return bool True if forms are present.
	 */
	public function is_form_page( $post_id = null ) {
		$post = get_post( $post_id );
		$cache_key = $post ? $post->ID : 'no_post';

		// Return cached result if available.
		if ( isset( $this->cache[ $cache_key ] ) ) {
			return $this->cache[ $cache_key ];
		}

		$is_form_page = false;

		// Check if post content contains CF7 shortcode.
		if ( $post && $this->has_cf7_shortcode( $post->post_content ) ) {
			$is_form_page = true;
		}

		// Check if post content contains CF7 blocks.
		if ( ! $is_form_page && $post && $this->has_cf7_blocks( $post->post_content ) ) {
			$is_form_page = true;
		}


		// Check whitelist.
		if ( ! $is_form_page && $this->is_whitelisted_page() ) {
			$is_form_page = true;
		}


		// Apply filter.
		$is_form_page = apply_filters( 'selerelo_is_form_page', $is_form_page, $post );

		// Cache and return result.
		$this->cache[ $cache_key ] = $is_form_page;
		return $is_form_page;
	}

	/**
	 * Check if current mode should load reCAPTCHA.
	 *
	 * @return bool True if reCAPTCHA should be loaded.
	 */
	public function should_load_recaptcha() {
		$mode = selerelo()->get_option( 'mode', 'selective' );

		switch ( $mode ) {
			case 'global':
				return true;

			case 'selective':
			default:
				return $this->is_form_page();
		}
	}



	/**
	 * Check if content contains CF7 shortcode.
	 *
	 * @param string $content Content to check.
	 * @return bool True if CF7 shortcode is found.
	 */
	private function has_cf7_shortcode( $content ) {
		return false !== strpos( $content, '[contact-form-7' );
	}

	/**
	 * Check if content contains CF7 blocks.
	 *
	 * @param string $content Content to check.
	 * @return bool True if CF7 blocks are found.
	 */
	private function has_cf7_blocks( $content ) {
		if ( ! function_exists( 'parse_blocks' ) ) {
			return false;
		}

		$blocks = parse_blocks( $content );
		return $this->search_cf7_blocks( $blocks );
	}

	/**
	 * Recursively search for CF7 blocks.
	 *
	 * @param array $blocks Array of blocks to search.
	 * @return bool True if CF7 blocks are found.
	 */
	private function search_cf7_blocks( $blocks ) {
		foreach ( $blocks as $block ) {
			// Check block name for CF7.
			if ( isset( $block['blockName'] ) && false !== strpos( $block['blockName'], 'contact-form-7' ) ) {
				return true;
			}

			// Recursively check inner blocks.
			if ( ! empty( $block['innerBlocks'] ) ) {
				if ( $this->search_cf7_blocks( $block['innerBlocks'] ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if current page is whitelisted.
	 *
	 * @return bool True if page is whitelisted.
	 */
	private function is_whitelisted_page() {
		$whitelist = selerelo()->get_option( 'whitelist', '' );
		if ( empty( $whitelist ) ) {
			return false;
		}

		$whitelist_items = array_filter( array_map( 'trim', explode( "\n", $whitelist ) ) );
		$current_post = get_queried_object();

		foreach ( $whitelist_items as $item ) {
			if ( empty( $item ) ) {
				continue;
			}

			// Check if it's a regex pattern (starts and ends with /).
			if ( preg_match( '/^\/.*\/$/', $item ) ) {
				$current_url = home_url( add_query_arg( array() ) );
				$match_result = preg_match( $item, $current_url );
				if ( $match_result === 1 ) {
					return true;
				}
				// 正規表現エラーの場合は無視して継続
				if ( $match_result === false ) {
					continue;
				}
			}

			// Check post ID.
			if ( is_numeric( $item ) && $current_post && $current_post->ID == $item ) {
				return true;
			}

			// Check post slug.
			if ( $current_post && property_exists( $current_post, 'post_name' ) && $current_post->post_name === $item ) {
				return true;
			}
		}

		return false;
	}


}