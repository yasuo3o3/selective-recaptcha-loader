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
class SRL_Detector {

	/**
	 * Class instance.
	 *
	 * @var SRL_Detector
	 */
	private static $instance = null;

	/**
	 * Cache for form detection results.
	 *
	 * @var array
	 */
	private $cache = array();

	/**
	 * Site-wide form detection cache.
	 *
	 * @var bool|null
	 */
	private $sitewide_cache = null;

	/**
	 * Get class instance.
	 *
	 * @return SRL_Detector
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
		add_action( 'wp_footer', array( $this, 'detect_sitewide_forms' ), 1 );
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

		// Check per-post meta.
		if ( ! $is_form_page && $post && get_post_meta( $post->ID, 'srl_force_load', true ) ) {
			$is_form_page = true;
		}

		// Check whitelist.
		if ( ! $is_form_page && $this->is_whitelisted_page() ) {
			$is_form_page = true;
		}

		// Check template hints.
		if ( ! $is_form_page && $this->matches_template_hints() ) {
			$is_form_page = true;
		}

		// Apply filter.
		$is_form_page = apply_filters( 'srl_is_form_page', $is_form_page, $post );

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
		$mode = srl()->get_option( 'mode', 'auto' );

		switch ( $mode ) {
			case 'global':
				return true;

			case 'selective':
				return $this->is_form_page();

			case 'auto':
			default:
				// In auto mode, check for site-wide forms.
				if ( $this->is_sitewide_form() ) {
					return true;
				}
				return $this->is_form_page();
		}
	}

	/**
	 * Check if there are site-wide CF7 forms.
	 *
	 * @return bool True if site-wide forms are detected.
	 */
	public function is_sitewide_form() {
		if ( null !== $this->sitewide_cache ) {
			return $this->sitewide_cache;
		}

		$is_sitewide = false;

		// Check if forms are commonly rendered in footer/header/widgets.
		$transient_key = 'srl_sitewide_detection';
		$cached_detection = get_transient( $transient_key );

		if ( false !== $cached_detection ) {
			$is_sitewide = (bool) $cached_detection;
		} else {
			// Simple heuristic: check if forms appear in widget areas.
			$is_sitewide = $this->detect_widget_forms();

			// Cache the result for 1 hour.
			set_transient( $transient_key, $is_sitewide ? 1 : 0, HOUR_IN_SECONDS );
		}

		// Apply filter for custom integrations.
		$is_sitewide = apply_filters( 'srl_is_sitewide_form', $is_sitewide );

		$this->sitewide_cache = $is_sitewide;
		return $is_sitewide;
	}

	/**
	 * Detect forms in widget areas during footer rendering.
	 */
	public function detect_sitewide_forms() {
		global $wp_registered_sidebars;

		if ( ! is_array( $wp_registered_sidebars ) ) {
			return;
		}

		foreach ( $wp_registered_sidebars as $sidebar_id => $sidebar ) {
			if ( in_array( $sidebar_id, array( 'footer', 'header' ), true ) ||
				 false !== strpos( $sidebar_id, 'footer' ) ||
				 false !== strpos( $sidebar_id, 'header' ) ) {

				ob_start();
				dynamic_sidebar( $sidebar_id );
				$sidebar_content = ob_get_clean();

				if ( $this->has_cf7_shortcode( $sidebar_content ) ) {
					set_transient( 'srl_sitewide_detection', 1, HOUR_IN_SECONDS );
					break;
				}
			}
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
		$whitelist = srl()->get_option( 'whitelist', '' );
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

	/**
	 * Check if current template matches hints.
	 *
	 * @return bool True if template matches hints.
	 */
	private function matches_template_hints() {
		$template_hints = srl()->get_option( 'template_hints', '' );
		if ( empty( $template_hints ) ) {
			return false;
		}

		$hints = array_filter( array_map( 'trim', explode( "\n", $template_hints ) ) );
		$current_template = basename( get_page_template() );

		foreach ( $hints as $hint ) {
			if ( $current_template === $hint ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Detect forms in widget areas.
	 *
	 * @return bool True if forms are found in widgets.
	 */
	private function detect_widget_forms() {
		global $wp_registered_widgets;

		if ( ! is_array( $wp_registered_widgets ) ) {
			return false;
		}

		// Check text widgets for CF7 shortcodes.
		$text_widgets = get_option( 'widget_text', array() );
		if ( is_array( $text_widgets ) ) {
			foreach ( $text_widgets as $widget ) {
				if ( is_array( $widget ) && ! empty( $widget['text'] ) ) {
					if ( $this->has_cf7_shortcode( $widget['text'] ) ) {
						return true;
					}
				}
			}
		}

		// Check custom HTML widgets.
		$custom_html_widgets = get_option( 'widget_custom_html', array() );
		if ( is_array( $custom_html_widgets ) ) {
			foreach ( $custom_html_widgets as $widget ) {
				if ( is_array( $widget ) && ! empty( $widget['content'] ) ) {
					if ( $this->has_cf7_shortcode( $widget['content'] ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}
}