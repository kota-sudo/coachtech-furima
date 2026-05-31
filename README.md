# coachtechフリマ

COACHTECH 課題用のメルカリ風 C2C フリマアプリ（Laravel 11）です。

## 使用技術

| 区分 | 技術 |
| --- | --- |
| 言語 | PHP 8.3 |
| フレームワーク | Laravel 11 |
| DB | MySQL 8.0 |
| Webサーバー | nginx |
| フロントビルド | Node.js 20 / npm / Vite |
| コンテナ | Docker / Docker Compose |
| 認証 | Laravel Fortify |

## 設計資料

| 資料 | パス |
| --- | --- |
| テーブル設計書（実装準拠・正式版） | [docs/table_design.md](docs/table_design.md) |
| テーブル設計書（旧 / 参考） | [docs/table_design.xlsx](docs/table_design.xlsx) |
| ER図 | [docs/er_diagram.jpg](docs/er_diagram.jpg) |
| 要件メモ | [docs/requirements_note.md](docs/requirements_note.md) |

> **DB設計の整合について**：支払い方法をマスタ化するため `payment_methods` テーブルを追加した **10 テーブル構成**で、`purchases` は `payment_method`（文字列）ではなく `payment_method_id`（外部キー）を持ちます。最新の正式定義は [docs/table_design.md](docs/table_design.md) を参照してください（旧 `table_design.xlsx` / `er_diagram.jpg` も同内容へ更新予定）。

## 画面ルート一覧

| 画面ID | 画面名称 | パス | 認証 |
| --- | --- | --- | --- |
| PG01 | 商品一覧（おすすめ） | `/` | 不要 |
| PG02 | 商品一覧（マイリスト） | `/?tab=mylist` | 任意（未ログイン時は空表示） |
| PG03 | 会員登録 | `/register` | ゲストのみ |
| PG04 | ログイン | `/login` | ゲストのみ |
| PG05 | 商品詳細 | `/item/{item_id}` | 不要 |
| PG06 | 商品購入 | `/purchase/{item_id}` | 必須 |
| PG07 | 送付先住所変更 | `/purchase/address/{item_id}` | 必須 |
| PG08 | 商品出品 | `/sell` | 必須 |
| PG09 | プロフィール | `/mypage` | 必須 |
| PG10 | プロフィール編集 | `/mypage/profile` | 必須 |
| PG11 | 購入した商品一覧 | `/mypage?page=buy` | 必須 |
| PG12 | 出品した商品一覧 | `/mypage?page=sell` | 必須（`page` 未指定時も sell） |

## `created_at` / `updated_at` について

`docs/table_design.xlsx` および `docs/er_diagram.jpg` のいずれのテーブルにも `created_at` / `updated_at` の定義がありません。  
設計書にないカラムは追加しない方針のため、各 Model で `$timestamps = false` を設定しています。

## 環境構築手順

### 前提

- Docker / Docker Compose がインストール済みであること
- Git が利用できること

### 1. リポジトリのクローン

```bash
git clone <リポジトリURL>
cd furima-app-submit
```

### 2. 環境変数ファイルの作成

```bash
cp .env.example .env
```

`.env` は Git 管理対象外です。Docker 利用時は `.env.example` の値（`DB_HOST=mysql` など）をそのまま使えます。

### 3. Docker コンテナの起動

```bash
docker compose up -d --build
```

起動後、アプリは **http://localhost:8080** でアクセスできます。

### 4. PHP 依存パッケージのインストール

```bash
docker compose exec php composer install
```

### 5. フロントエンド依存パッケージのインストールとビルド

```bash
docker compose run --rm node npm install
docker compose run --rm node npm run build
```

### 6. Laravel 初期設定

```bash
docker compose exec php php artisan key:generate
docker compose exec php php artisan storage:link
```

#### Stripe（カード決済）の設定（任意）

「カード支払い」で Stripe Checkout を利用する場合は、`.env` に Stripe のテスト用 API キーを設定してください（未設定の場合はカード支払いでも決済画面を経由せず購入が完了します）。

```dotenv
STRIPE_KEY=pk_test_xxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxx
```

決済画面ではテスト用カード番号（例: `4242 4242 4242 4242` / 任意の未来の有効期限 / 任意の3桁）で動作確認できます。

権限エラーが出る場合は、以下を実行してください。

```bash
docker compose exec php chmod -R 777 storage bootstrap/cache
```

### 7. データベースのマイグレーションとシード

```bash
docker compose exec php php artisan migrate:fresh --seed
```

**注意:** `ItemSeeder` は商品画像を COACHTECH 提供の S3 URL からダウンロードします。**インターネット接続が必要**です。オフライン環境では画像の取得に失敗する場合があります（2回目以降は既存ファイルがあればスキップ）。

### 8. 動作確認

ブラウザで http://localhost:8080 を開き、商品一覧が表示されることを確認してください。

| ツール | URL | 用途 |
| --- | --- | --- |
| アプリ | http://localhost:8080 | フリマアプリ本体 |
| phpMyAdmin | http://localhost:8081 | DB の確認（サーバー `mysql` / ユーザー `furima` / パスワード `furima`） |
| MailHog | http://localhost:8025 | 送信メール（会員登録の認証メール）の確認 |

シード後の出品者アカウント例（`database/seeders/data/sample_items.php`）:

| 項目 | 値 |
| --- | --- |
| メール | `seller@example.com` |
| パスワード | `password` |

## Docker 構成

| サービス | 役割 | 備考 |
| --- | --- | --- |
| `php` | PHP-FPM 8.3 / Composer | Artisan・Composer コマンドはこのコンテナで実行 |
| `nginx` | Webサーバー | ホスト `8080` → コンテナ `80` |
| `mysql` | MySQL 8.0 | DB名 `furima`、ユーザー `furima` / パスワード `furima`。ポートマッピングは **`3307:3306`**（ホスト `3307` → コンテナ `3306`）。アプリ（php コンテナ）からは `DB_HOST=mysql`・`DB_PORT=3306` で接続 |
| `phpmyadmin` | DB管理ツール | ホスト `8081` → コンテナ `80`（http://localhost:8081） |
| `mailhog` | メール確認ツール | SMTP `1025`、Web UI `8025`（http://localhost:8025）。`MAIL_HOST=mailhog` / `MAIL_PORT=1025` で送信 |
| `node` | Node.js 20 / npm | `npm install` / `npm run build` 用 |

### コンテナの停止・再起動

```bash
# 停止
docker compose down

# 再起動（DBデータを保持）
docker compose up -d

# DBボリュームも含めて完全削除する場合
docker compose down -v
```

## テスト実行手順

基本設計書のテストケース一覧（#1〜#15）に対応する Feature テストを実装しています。

```bash
# 全テスト
docker compose exec php php artisan test

# 特定のテストのみ
docker compose exec php php artisan test --filter=ItemIndexTest
```

主なテストクラス: `Auth/RegistrationTest`, `Auth/AuthenticationTest`, `Auth/EmailVerificationTest`, `ItemIndexTest`, `ItemDetailTest`, `LikeTest`, `CommentTest`, `PurchaseTest`, `PurchaseAddressTest`, `MypageTest`, `ProfileUpdateTest`, `ExhibitionTest`

## よく使うコマンド

```bash
# Artisan 全般
docker compose exec php php artisan <command>

# Composer
docker compose exec php composer <command>

# npm（開発サーバー）
docker compose run --rm node npm run dev
```

## 認証（Fortify）

機能要件の「使用技術: Fortify」に合わせ、**Laravel Fortify を導入**しています。  
Fortify を導入し、`CreateNewUser` Action 等を利用しつつ、指定エラーメッセージ対応のため独自 Controller / FormRequest でルートを制御しています。

| 処理 | Fortify の利用 | 実装 |
| --- | --- | --- |
| 会員登録 | **利用** | `CreatesNewUsers` 契約の `App\Actions\Fortify\CreateNewUser` でユーザー作成。検証・遷移は `RegisterRequest` / `RegisterController` |
| ログイン | **補助** | Fortify 標準ルートは使わず、`LoginRequest` + `Auth::attempt()`（Fortify 内部も同じ Laravel 認証基盤を使用）。ログイン試行のレート制限定義は `FortifyServiceProvider` で設定 |
| ログアウト | **補助** | `LogoutController` でセッション破棄（Fortify 標準 Controller と同等の処理） |

- 会員登録: `/register`（登録後はメール認証案内画面 `/email/verify` へ遷移）
- ログイン: `/login`
- ログアウト: ヘッダーの「ログアウト」ボタン（`POST /logout`）
- `Fortify::ignoreRoutes()` により Fortify 付属の HTTP ルートは無効化し、`routes/auth.php` でパス・FormRequest・リダイレクトを要件どおりに制御

### メール認証（PG: 会員登録後）

- `User` は `MustVerifyEmail` を実装し、会員登録時に認証メールを送信します。
- 登録直後はメール認証案内画面（`/email/verify`）へ遷移します。
- 送信メールは **MailHog（http://localhost:8025）** で確認できます。メール内のリンクをクリックすると認証が完了し、プロフィール設定画面（`/mypage/profile`）へ遷移します。
- 認証が必要なルート（出品・購入・いいね・コメント・マイページ等）は `verified` ミドルウェアで保護しており、未認証ユーザーは認証案内画面へリダイレクトされます。

Breeze の認証 Controller は削除済みです。Blade コンポーネント（`x-input-label` 等）は画面表示用に流用しています。

### マイページ（PG09 / PG11 / PG12）

- パス: `/mypage`（要ログイン）
- `?page=sell` … 出品した商品一覧（`page` 未指定時も sell を表示）
- `?page=buy` … 購入した商品一覧
- プロフィール画像・ユーザー名の表示、プロフィール編集（`/mypage/profile`）へのリンク

### プロフィール設定（PG10）

- パス: `/mypage/profile`（要ログイン）
- 編集項目: プロフィール画像、ユーザー名、郵便番号、住所、建物名
- 画像は `storage/app/public/profile_images` に保存（`php artisan storage:link` 必須）

### 商品Seeder

- 基本設計書の正式商品データ10件を投入
- 画像は seed 時に S3 から `storage/app/public/items/` へ保存（`item_images.image_path` は `items/xxx.jpg`）

### 商品購入（PG06）

- パス: `/purchase/{item_id}`（要ログイン）
- 支払い方法は `payment_methods` マスタ（コンビニ支払い / カード支払い）から選択
- **カード支払い**を選択し `STRIPE_SECRET` が設定されている場合は **Stripe Checkout** に遷移し、決済成功後に購入が確定します
- コンビニ支払い（および Stripe キー未設定時）は決済画面を経由せず購入を確定します
- 購入完了時に `purchases` へ保存し、`items.is_sold` を `true` に更新
- 購入後は商品一覧 `/` へリダイレクト

### 送付先住所変更（PG07）

- パス: `/purchase/address/{item_id}`（要ログイン）
- 購入画面から遷移し、郵便番号・住所・建物名を変更可能
- 変更した住所はセッション（`purchase_address.{item_id}`）に保持し、購入画面へ反映
- `users` テーブルの住所は更新しない

### 商品出品（PG08）

- パス: `/sell`（要ログイン）
- 商品画像・カテゴリ（複数）・商品の状態・商品名・ブランド名・説明・価格を登録

## 未実装機能（スコープ外）

基本設計書の応用要件である **Stripe 決済** と **メール認証** は実装済みです（上記参照）。以下は範囲外として **意図的に未実装**です。

| 機能 | 備考 |
| --- | --- |
| 商品編集・削除 | — |
| 他ユーザーのプロフィール表示 | マイページはログインユーザー本人のみ |
| パスワードリセット | Breeze 標準のパスワードリセットは無効 |

## DB設計との整合

- 正式なテーブル定義は `docs/table_design.md`（実装準拠・10テーブル）に集約
- 支払い方法はマスタ化（`payment_methods`）し、`purchases` は `payment_method_id`（外部キー）を保持
- `items` に `category_id` は持たせず、`category_item` 中間テーブルを使用
- `likes` を使用（`favorites` テーブルは作成しない）
- `comments` の本文カラムは `comment`（`body` は使用しない）

## GitHub 提出時の確認事項

提出・レビュー時に次を確認してください。

1. `.env` をコミットしていない（`.gitignore` で除外済み）
2. `docs/table_design.xlsx` と `docs/er_diagram.jpg` がリポジトリに含まれている
3. `docker compose up -d --build` → `composer install` → `npm run build` → `key:generate` → `storage:link` → `migrate:fresh --seed` が README どおり実行できる
4. `docker compose exec php php artisan test` が成功する（基本機能 #1〜#15 ＋メール認証の Feature テスト）
5. メール確認は MailHog（http://localhost:8025）、DB確認は phpMyAdmin（http://localhost:8081）で行える
6. Stripe を利用する場合は `.env` に `STRIPE_KEY` / `STRIPE_SECRET`（テストキー）を設定している

## 注意事項

- `.env` はコミットしないでください（`.gitignore` に登録済み）
- 商品のダミーデータは `database/seeders/data/sample_items.php` に定義しています（設計書に商品データ一覧シートはありません）
- Breeze 導入時の未使用 Blade（`dashboard.blade.php`、`auth/forgot-password.blade.php` 等）はルート未登録のため画面からは到達しません

## ライセンス

MIT License（Laravel ベース）
