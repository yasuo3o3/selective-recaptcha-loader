=== Selective reCAPTCHA Loader for CF7 ===
Contributors: netservice
Tags: contact-form-7, recaptcha, performance, optimization, forms
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Load reCAPTCHA only where Contact Form 7 forms are present or on all pages as desired.

== Description ==

**Selective reCAPTCHA Loader for CF7** is a performance optimization plugin that intelligently controls when and where Google reCAPTCHA assets are loaded for Contact Form 7 forms. Instead of loading reCAPTCHA on every page, this plugin provides two modes to optimize your site's performance.

= Key Features =

**🚀 Two Loading Modes:**
- **Selective Mode (Default)**: Only loads reCAPTCHA on pages that contain Contact Form 7 forms
- **Global Mode**: Always loads reCAPTCHA on all front-end pages (traditional behavior)

**🎯 Smart Detection:**
- Detects CF7 shortcodes in post content
- Identifies CF7 blocks in Gutenberg editor
- Whitelist-based override for specific pages

**⚙️ Advanced Configuration:**
- Whitelist specific pages by ID, slug, or regex patterns (pages matching the whitelist will **always** load reCAPTCHA)
- Comprehensive filter system for developers


= How It Works =

The plugin hooks into WordPress's script enqueuing system with a priority of 120, analyzing each page to determine if Contact Form 7 forms are present. Based on your selected mode and detection results, it either allows or prevents reCAPTCHA assets from loading.


**Caching Considerations:**
- Per-page detection results are cached per request
- Cache automatically clears when plugin settings change

= Developer Features =

**Filters:**
- `selerelo_is_form_page` - Modify form page detection
- `selerelo_recaptcha_handles` - Define which script handles to control

**Template Functions:**
- `selerelo_should_load_recaptcha()` - Check if reCAPTCHA will load
- `selerelo_is_form_page()` - Check if page has forms
- `selerelo_get_option()` - Get plugin options

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/selective-recaptcha-loader/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Settings → Selective reCAPTCHA to configure the plugin
4. Choose your preferred mode (Selective is recommended for most sites)

== Frequently Asked Questions ==

= Does this plugin work without Contact Form 7? =

No, this plugin requires Contact Form 7 to be active. It will gracefully deactivate functionality if CF7 is not detected.


= Can I force reCAPTCHA to load on specific pages? =

Yes! Add pages to the whitelist in settings using:
- Post ID (e.g., 123)
- Page slug (e.g., contact)
- Regular expression (e.g., /^https:\/\/example\.com\/custom/)
You can also use the `selerelo_is_form_page` filter for custom logic.

= Is this plugin compatible with caching plugins? =

Yes, the plugin is designed to work with caching plugins. The detection logic runs before caching occurs and uses WordPress transients for performance.


== Screenshots ==

1. Main settings page showing the two modes and configuration options

== Changelog ==

= Unreleased =
* Removed: Auto mode (automatically migrated to Selective mode)
* Removed: Per-post metabox functionality
* Removed: reCAPTCHA v3 badge hiding and disclosure features
* Simplified: Plugin now offers only Global and Selective modes for easier configuration
* Performance: Removed unused transient caching and site-wide detection logic

= 0.01 - 2025-09-02 =
* Initial release
* Global/Selective loading modes
* Smart form detection (shortcodes, blocks)
* Whitelist and template hint support
* Complete internationalization (English/Japanese)
* Developer filter system
* WordPress 6.0+ and PHP 7.4+ compatibility

== Upgrade Notice ==

= 0.2 =
Simplified to Global/Selective modes with whitelist support. Removed Auto mode, metabox, and badge features for better usability.

= 0.01 =
Initial release of Selective reCAPTCHA Loader for CF7.

== Technical Details ==

**Minimum Requirements:**
- WordPress 6.0 or higher
- PHP 7.4 or higher
- Contact Form 7 5.8 or higher

**Performance Impact:**
- Minimal overhead: Detection logic is lightweight and cached
- Reduces script loading on pages without forms
- Can significantly improve page load times on form-free pages

**Security:**
- All user inputs are sanitized and escaped
- Uses WordPress nonces for form submissions
- Follows WordPress coding standards
- No external API calls or tracking

=== 日本語版説明 ===

**Selective reCAPTCHA Loader for CF7** は、Contact Form 7 のパフォーマンスを最適化するプラグインです。全ページでreCAPTCHAを読み込む代わりに、フォームがあるページでのみ読み込みます。

= 主な特徴 =

**2つの読み込みモード:**
- **Selectiveモード（推奨）**: フォームがあるページのみ読み込み
- **Globalモード**: 全ページでreCAPTCHA読み込み（従来方式）

**スマート検出:**
- ショートコード、Gutenbergブロック内のフォームを検出
- ホワイトリスト機能（ページID、スラッグ、正規表現対応、ホワイトリストに一致したページは**必ず**reCAPTCHAを読み込みます）

= インストール =

1. プラグインファイルを `/wp-content/plugins/selective-recaptcha-loader/` にアップロード
2. 管理画面の「プラグイン」でプラグインを有効化
3. 「設定」→「Selective reCAPTCHA」で設定
4. Selectiveモードのまま使用を推奨（ほとんどのサイトに最適）

= 使い方 =


**Globalモード**: 全ページでreCAPTCHAを読み込みます。サイト全体にフォームがある場合に適しています。

**Selectiveモード**: フォーム検出したページのみreCAPTCHAを読み込みます。最大のパフォーマンス向上が期待できます。

= 注意事項 =

**キャッシュプラグイン**: 
ほとんどのキャッシュプラグインと互換性があります。検出ロジックはキャッシュ前に実行されます。