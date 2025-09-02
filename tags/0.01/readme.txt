=== Selective reCAPTCHA Loader for CF7 ===
Contributors: netservice
Tags: contact-form-7, recaptcha, performance, optimization, forms
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 0.01
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Load reCAPTCHA only where Contact Form 7 forms are present. Auto-detects site-wide footer forms and switches to Global mode when appropriate.

== Description ==

**Selective reCAPTCHA Loader for CF7** is a performance optimization plugin that intelligently controls when and where Google reCAPTCHA assets are loaded for Contact Form 7 forms. Instead of loading reCAPTCHA on every page, this plugin provides three intelligent modes to optimize your site's performance.

= Key Features =

**🚀 Three Loading Modes:**
- **Auto Mode (Default)**: Automatically detects if forms are used site-wide (e.g., in footer/header) and switches between Selective and Global mode accordingly
- **Global Mode**: Always loads reCAPTCHA on all front-end pages (traditional behavior)
- **Selective Mode**: Only loads reCAPTCHA on pages that contain Contact Form 7 forms

**🎯 Smart Detection:**
- Detects CF7 shortcodes in post content
- Identifies CF7 blocks in Gutenberg editor
- Recognizes forms in widget areas (text widgets, custom HTML widgets)
- Template-based detection for custom implementations
- Per-page override controls

**⚙️ Advanced Configuration:**
- Whitelist specific pages by ID, slug, or regex patterns
- Template hints for theme-specific form locations
- Per-post metabox to force reCAPTCHA loading
- Comprehensive filter system for developers

**🛡️ reCAPTCHA v3 Compliance:**
- Optional badge hiding with compliant disclosure text
- Customizable disclosure text with privacy/terms links
- Maintains Google's terms of service compliance

= How It Works =

The plugin hooks into WordPress's script enqueuing system with a priority of 120, analyzing each page to determine if Contact Form 7 forms are present. Based on your selected mode and detection results, it either allows or prevents reCAPTCHA assets from loading.

**Auto Mode Logic:**
1. First checks if forms appear in common site-wide locations (footer, header, widgets)
2. If site-wide forms are detected, behaves like Global mode
3. Otherwise, behaves like Selective mode for optimal performance

**Caching Considerations:**
- Uses WordPress transients for site-wide form detection (1-hour cache)
- Per-page detection results are cached per request
- Cache automatically clears when plugin settings change

= Developer Features =

**Filters:**
- `srl_is_form_page` - Modify form page detection
- `srl_is_sitewide_form` - Override site-wide form detection
- `srl_disclosure_html` - Customize disclosure text output
- `srl_recaptcha_handles` - Define which script handles to control

**Template Functions:**
- `srl_should_load_recaptcha()` - Check if reCAPTCHA will load
- `srl_is_form_page()` - Check if page has forms
- `srl_is_sitewide_form()` - Check for site-wide forms
- `srl_get_option()` - Get plugin options

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/selective-recaptcha-loader/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Settings → Selective reCAPTCHA to configure the plugin
4. Choose your preferred mode (Auto is recommended for most sites)

== Frequently Asked Questions ==

= Does this plugin work without Contact Form 7? =

No, this plugin requires Contact Form 7 to be active. It will gracefully deactivate functionality if CF7 is not detected.

= What happens if I have forms in my footer or header? =

Auto mode (default) will detect this and automatically switch to Global mode to ensure reCAPTCHA loads on all pages where the site-wide forms appear.

= Can I force reCAPTCHA to load on specific pages? =

Yes! Use the per-page metabox in the post editor, add pages to the whitelist in settings, or use the `srl_is_form_page` filter.

= Is this plugin compatible with caching plugins? =

Yes, the plugin is designed to work with caching plugins. The detection logic runs before caching occurs and uses WordPress transients for performance.

= Does hiding the reCAPTCHA badge violate Google's terms? =

No, the plugin provides compliant badge hiding that visually minimizes (but doesn't completely hide) the badge while adding the required disclosure text near forms.

= Can I customize the disclosure text? =

Yes, you can provide custom disclosure text in the settings. Use `{privacy_link}` and `{tos_link}` placeholders for the Google policy links.

== Screenshots ==

1. Main settings page showing the three modes and configuration options
2. Per-post metabox for forcing reCAPTCHA on specific pages
3. Badge & disclosure settings for reCAPTCHA v3 compliance
4. Example of minimized badge with disclosure text

== Changelog ==

= Unreleased =
* Security: uninstall.php追加、プラグイン削除時のデータクリーンアップ処理
* Performance: autoload無効化によるDB負荷軽減
* Performance: badge.css読み込み条件を最適化（必要ページのみ）
* Reliability: トランジェントキーをマルチサイト対応で一意化
* Code Quality: メタボックス保存時のnonce検証順序を効率化
* Code Quality: PHPDoc @sinceタグ追加、正規表現例外処理改善

= 0.01 - 2025-09-02 =
* Initial release
* Auto/Global/Selective loading modes
* Site-wide form detection
* Smart form detection (shortcodes, blocks, widgets)
* Per-page override controls
* Whitelist and template hint support
* reCAPTCHA v3 badge compliance features
* Complete internationalization (English/Japanese)
* Developer filter system
* WordPress 6.0+ and PHP 7.4+ compatibility

== Upgrade Notice ==

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

**3つの読み込みモード:**
- **Autoモード（推奨）**: フッター等にサイト全体フォームがある場合は自動でGlobalに切り替え
- **Globalモード**: 全ページでreCAPTCHA読み込み（従来方式）
- **Selectiveモード**: フォームがあるページのみ読み込み

**スマート検出:**
- ショートコード、Gutenbergブロック、ウィジェット内のフォームを検出
- ページ単位での強制読み込み設定
- ホワイトリスト機能（ページID、スラッグ、正規表現対応）

= インストール =

1. プラグインファイルを `/wp-content/plugins/selective-recaptcha-loader/` にアップロード
2. 管理画面の「プラグイン」でプラグインを有効化
3. 「設定」→「Selective reCAPTCHA」で設定
4. Autoモードのまま使用を推奨（ほとんどのサイトに最適）

= 使い方 =

**Autoモード**: 設定不要。フッター等にフォームがあれば自動でGlobalモードに、なければSelectiveモードで動作します。

**Globalモード**: 全ページでreCAPTCHAを読み込みます。サイト全体にフォームがある場合に適しています。

**Selectiveモード**: フォーム検出したページのみreCAPTCHAを読み込みます。最大のパフォーマンス向上が期待できます。

= 注意事項 =

**reCAPTCHA v3バッジについて**: 
バッジを非表示にする場合は、Googleの利用規約に従い適切な開示テキストが表示されます。設定画面でカスタマイズ可能です。

**キャッシュプラグイン**: 
ほとんどのキャッシュプラグインと互換性があります。検出ロジックはキャッシュ前に実行され、WordPressのトランジェントを使用して効率化されています。