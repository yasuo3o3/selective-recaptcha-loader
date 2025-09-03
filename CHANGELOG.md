# Selective reCAPTCHA Loader for CF7 – Changelog

バージョン履歴と変更ログです。このプロジェクトは[Semantic Versioning](https://semver.org/spec/v2.0.0.html)に従います。

## [v0.02] - Unreleased

### Removed（削除）
- Autoモード機能の完全削除（Selectiveモードにマイグレーション）
- ページ別メタボックス機能の完全削除
- reCAPTCHA v3バッジ最小化機能の完全削除
- サイト全体フォーム検出ロジックの削除
- ファイル削除: admin/class-srl-metabox.php, assets/badge.css

### Changed（変更）
- 設定画面を2択に簡素化（Global/Selectiveのみ）
- デフォルトモードをSelectiveに変更
- プラグイン説明とドキュメントを簡素化

### Fixed（修正）
- is_login()関数の存在しない参照を削除

---

## [v0.01] - 2025-09-02

### Added（追加）
- 初回公開
- Global/Selective読み込みモード
- スマートフォーム検出（ショートコード、ブロック）
- ホワイトリスト・テンプレートヒント機能
- 開発者向けフィルターシステム
- WordPress 6.0+ / PHP 7.4+ 対応

---

## テンプレート（今後のリリース用）

### Added（追加）
- 新機能や新しいファイルの追加

### Changed（変更）
- 既存機能の変更や改善

### Deprecated（非推奨）
- 将来のバージョンで削除予定の機能

### Removed（削除）
- 削除された機能やファイル

### Fixed（修正）
- バグ修正

### Security（セキュリティ）
- セキュリティ関連の修正