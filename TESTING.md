# TESTING – Selective reCAPTCHA Loader for CF7

このドキュメントは **本番前の手動テスト** と **自動化テスト（CI）** の手順を示します。  
対象バージョン: 0.2

---

## 0. 前提 / 環境

- WordPress: 6.6.x
- PHP: 7.4 / 8.2
- Contact Form 7: 5.8+
- キャッシュ：なし / LiteSpeed Cache（ON）
- テーマ：Twenty Twenty-Four（デフォルト）

### 準備
- CF7で「お問い合わせ」フォームを1つ作成（ショートコードを固定ページに設置）
- 必要に応じて、フッターのウィジェットにフォームを置く（“全ページ出力”ケースの検証用）
- プラグイン設定はデフォルト（Auto mode）から開始

---

## 1. 手動テスト（Manual QA）

### 1-1. 基本挙動
- [ ] フォーム **あり** ページで reCAPTCHA（v3）スクリプトが読み込まれる  
  - 期待：ネットワークに `wpcf7-recaptcha` / `google-recaptcha`（v3）が出る
- [ ] フォーム **なし** ページで reCAPTCHA が読み込まれない  
  - 期待：上記ハンドルの読み込みなし、console error なし

### 1-2. 動作モード別
- Global mode
  - [ ] すべてのページで読み込まれる
- Selective mode
  - [ ] フォームのあるページのみ読み込まれる

### 1-3. Continue（GPT-5）指摘の再現/確認

**A) `is_login()` 参照の有無**
- 手順：
  1. `/wp-login.php` にアクセス
  2. 通常ページ/管理画面/ログインページを行き来
- 期待：致命的エラーやNoticeが出ない。  
  補足：コアに `is_login()` は無い。もしコードに残っていればログイン画面でFatalになる可能性あり → 修正要。



### 1-3. ホワイトリスト機能
- [ ] ページID指定（例：123）で該当ページに強制読み込み
- [ ] スラッグ指定（例：contact）で該当ページに強制読み込み
- [ ] 正規表現指定でパターン一致ページに強制読み込み

### 1-4. キャッシュ/互換性
- [ ] LiteSpeed Cache（ON）で同様の結果になる（フォームの有無に応じて読み込み可否が変わる）
- [ ] 他プラグインと競合しない（console error なし）

### 1-5. アンインストール
- [ ] 設定を保存後、**停止→削除**  
  - 期待：`wp_options` の `selerelo_settings` が削除される

### 1-6. 翻訳（i18n）
- [ ] 管理画面が日本語・英語で適切に切り替わる（`languages/*.mo` を認識）

---

## 2. 自動化（CIで実行）

> まずは **静的チェック**から導入。PHPUnit等は後追い。

- PHPCS（WordPress Coding Standards）
  - 実行例（ローカル）：
    ```bash
    ./vendor/bin/phpcs --standard=WordPress .
    ```
  - 期待：Error 0 / Major Warning なし（新規差分にBlocker級が出ない）
- ビルドZIP（任意）
  - `trunk/` をベースに `.claude` 等の開発ファイルを除外してzip化

---

## 3. 判定と記録

| ケースID | 結果(Pass/Fail) | 備考 |
|---------|------------------|------|
| 1-1-a   |                  |      |
| 1-1-b   |                  |      |
| 1-2-A1  |                  |      |
| 1-3-A   |                  |      |
| …       |                  |      |

NGが出た場合は GitHub Issue を作成し、再現手順/期待/実際/ログを添付してください。
