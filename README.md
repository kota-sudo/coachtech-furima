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
| テーブル設計書 | [docs/table_design.xlsx](docs/table_design.xlsx) |
| ER図 | [docs/er_diagram.jpg](docs/er_diagram.jpg) |
| 要件メモ | [docs/requirements_note.md](docs/requirements_note.md) |

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

主なテストクラス: `Auth/RegistrationTest`, `Auth/AuthenticationTest`, `ItemIndexTest`, `ItemDetailTest`, `LikeTest`, `CommentTest`, `PurchaseTest`, `PurchaseAddressTest`, `MypageTest`, `ProfileUpdateTest`, `ExhibitionTest`

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

- 会員登録: `/register`（登録後は `/mypage/profile` へ遷移）
- ログイン: `/login`
- ログアウト: ヘッダーの「ログアウト」ボタン（`POST /logout`）
- `Fortify::ignoreRoutes()` により Fortify 付属の HTTP ルートは無効化し、`routes/auth.php` でパス・FormRequest・リダイレクトを要件どおりに制御

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
- 購入完了時に `purchases` へ保存し、`items.is_sold` を `true` に更新
- 購入後は商品一覧 `/` へリダイレクト
- **Stripe 決済は未実装**（支払い方法の選択と購入記録のみ）

### 送付先住所変更（PG07）

- パス: `/purchase/address/{item_id}`（要ログイン）
- 購入画面から遷移し、郵便番号・住所・建物名を変更可能
- 変更した住所はセッション（`purchase_address.{item_id}`）に保持し、購入画面へ反映
- `users` テーブルの住所は更新しない

### 商品出品（PG08）

- パス: `/sell`（要ログイン）
- 商品画像・カテゴリ（複数）・商品の状態・商品名・ブランド名・説明・価格を登録

## 未実装機能（スコープ外）

以下は基本設計書の範囲外として、**意図的に未実装**です。

| 機能 | 備考 |
| --- | --- |
| Stripe 決済 | 支払い方法の選択と `purchases` への記録のみ |
| メール認証 | Fortify のメール認証ルートは無効 |
| 商品編集・削除 | — |
| 他ユーザーのプロフィール表示 | マイページはログインユーザー本人のみ |

## DB設計との整合

- テーブル・カラムは `docs/table_design.xlsx` / `docs/er_diagram.jpg` に準拠
- `items` に `category_id` は持たせず、`category_item` 中間テーブルを使用
- `likes` を使用（`favorites` テーブルは作成しない）
- `comments` の本文カラムは `comment`（`body` は使用しない）

## GitHub 提出時の確認事項

提出・レビュー時に次を確認してください。

1. `.env` をコミットしていない（`.gitignore` で除外済み）
2. `docs/table_design.xlsx` と `docs/er_diagram.jpg` がリポジトリに含まれている
3. `docker compose up -d --build` → `composer install` → `npm run build` → `key:generate` → `storage:link` → `migrate:fresh --seed` が README どおり実行できる
4. `docker compose exec php php artisan test` が成功する（基本機能 #1〜#15 の Feature テスト）
5. 未実装機能（Stripe・メール認証等）を課題範囲外として明記している

## 注意事項

- `.env` はコミットしないでください（`.gitignore` に登録済み）
- 商品のダミーデータは `database/seeders/data/sample_items.php` に定義しています（設計書に商品データ一覧シートはありません）
- Breeze 導入時の未使用 Blade（`dashboard.blade.php`、`auth/forgot-password.blade.php` 等）はルート未登録のため画面からは到達しません

## ライセンス

MIT License（Laravel ベース）
