<?php
/**
 * プラグイン削除時のクリーンアップ処理
 *
 * @package SelectiveRecaptchaLoader
 * @since 0.01
 */

// WordPress のアンインストールプロセス以外からの実行を禁止
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// プラグイン設定を削除
delete_option( 'selerelo_settings' );

// サイト全体フォーム検出のキャッシュを削除
delete_transient( 'selective_recaptcha_loader_sitewide_detection_' . get_current_blog_id() );

// 全投稿から個別設定メタを削除
delete_metadata( 'post', 0, 'selerelo_force_load', '', true );