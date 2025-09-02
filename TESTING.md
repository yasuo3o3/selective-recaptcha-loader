# TESTING – Selective reCAPTCHA Loader for CF7

このドキュメントは **本番前の手動テスト** と **自動化テスト（CI）** の手順を示します。  
対象バージョン: v0.01（Unreleased）

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
- [ ] 「v3バッジを隠して開示テキスト」ON → フォームありページでバッジが最小化され、開示文が表示  
  - 期待：CSS `badge.css` が読み込まれ、開示テキストに Privacy/ToS リンク

### 1-2. 動作モード別
- Auto mode
  - [ ] フッターにCF7フォームを出す → **全ページ**でreCAPTCHAが読み込まれる  
  - [ ] フッターからフォームを外す → **フォームがあるページのみ**で読み込まれる  
- Global mode
  - [ ] すべてのページで読み込まれる
- Selective mode
  - [ ] フォームのあるページのみ読み込まれる
- ページ別メタ（「このページで読み込む」）
  - [ ] フォームなしでもチェックONなら読み込まれる
  - [ ] チェックOFFなら読み込まれない

### 1-3. Continue（GPT-5）指摘の再現/確認

**A) `is_login()` 参照の有無**
- 手順：
  1. `/wp-login.php` にアクセス
  2. 通常ページ/管理画面/ログインページを行き来
- 期待：致命的エラーやNoticeが出ない。  
  補足：コアに `is_login()` は無い。もしコードに残っていればログイン画面でFatalになる可能性あり → 修正要。

**B) トランジェントキーの統一**
- 手順：
  1. サイトを数ページ巡回（Auto mode）
  2. 設定を保存→再読込
- 期待：検出側と削除側のキーが一致し、不要なトランジェントが残らない  
  補助確認（WP-CLI）：
  ```bash
  wp transient list | grep selective_recaptcha_loader_sitewide_detection
残存/不一致があれば修正要（blog_idサフィックスの有無も確認）

C) dynamic_sidebar() 検出の副作用

手順：

フッターのウィジェットでCF7フォームを出す

ページ表示を2回リロード、console/networkに異常がないか

期待：表示が二重にならない、Noticeが出ない、余計なフック実行が増えない
懸念がある場合は メタ情報ベース（is_active_widget や widgetオプション走査）に切替を検討

1-4. キャッシュ/互換性
 LiteSpeed Cache（ON）で同様の結果になる（フォームの有無に応じて読み込み可否が変わる）

 他プラグインと競合しない（console error なし）

1-5. アンインストール
 設定を保存後、停止→削除

期待：wp_options の srl_settings、wp_postmeta の srl_force_load、関連トランジェントが削除される

1-6. 翻訳（i18n）
 管理画面が日本語・英語で適切に切り替わる（languages/*.mo を認識）

2. 自動化（CIで実行）
まずは 静的チェックから導入。PHPUnit等は後追い。

PHPCS（WordPress Coding Standards）

実行例（ローカル）：

bash
コードをコピーする
./vendor/bin/phpcs --standard=WordPress .
期待：Error 0 / Major Warning なし（新規差分にBlocker級が出ない）

ビルドZIP（任意）

trunk/ をベースに .claude 等の開発ファイルを除外してzip化

3. 判定と記録
ケースID	結果(Pass/Fail)	備考
1-1-a		
1-1-b		
1-2-A1		
1-3-A		
…		

NGが出た場合は GitHub Issue を作成し、再現手順/期待/実際/ログを添付してください。

yaml
コードをコピーする

---

## いま“テストに入れるべき”Continue指摘の扱い

- **is_login()**：**最優先でテスト**。Fatal/Noticeが出るなら即修正（`is_login()`の自作ヘルパを用意するか、単純に`is_admin()`判定に寄せる）。  
- **トランジェントキー統一**：Auto/設定保存の前後でキーが統一されているかを**WP-CLI**で確認。ズレがあれば修正。  
- **dynamic_sidebar検出**：副作用が出るなら、**メタ情報ベース検出**に切替を検討（今回のTESTING.mdに“副作用がないこと”を明記済み）。

---

## もし自動化を少し足すなら（任意）

`.github/workflows/ci.yml`（PHPCSのみの最小）：

```yaml
name: CI
on:
  pull_request:
  push:
    branches: [ main ]
jobs:
  phpcs:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: phpcs
      - name: WordPress Coding Standards
        run: |
          phpcs --version
          phpcs --standard=WordPress --extensions=php . || true
（まずは警告を見るだけ。厳格に落とすかは後で調整）

