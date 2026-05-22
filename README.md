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

### 8. 動作確認

ブラウザで http://localhost:8080 を開き、Laravel の画面が表示されることを確認してください。

## Docker 構成

| サービス | 役割 | 備考 |
| --- | --- | --- |
| `php` | PHP-FPM 8.3 / Composer | Artisan・Composer コマンドはこのコンテナで実行 |
| `nginx` | Webサーバー | ホスト `8080` → コンテナ `80` |
| `mysql` | MySQL 8.0 | DB名 `furima`、ユーザー `furima` / パスワード `furima`（ホストから接続する場合はポート `3307`） |
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

## テスト実行手順（枠）

Feature テスト・Unit テストは今後追加予定です。実行する場合は次のコマンドを使用します。

```bash
# 全テスト
docker compose exec php php artisan test

# または PHPUnit 直接
docker compose exec php ./vendor/bin/phpunit
```

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

### プロフィール設定（PG10）

- パス: `/mypage/profile`（要ログイン）
- 編集項目: プロフィール画像、ユーザー名、郵便番号、住所、建物名
- 画像は `storage/app/public/profile_images` に保存（`php artisan storage:link` 必須）

### 商品Seeder

- 基本設計書の正式商品データ10件を投入
- 画像は seed 時に S3 から `storage/app/public/items/` へ保存（`item_images.image_path` は `items/xxx.jpg`）

## 注意事項

- `.env` はコミットしないでください
- 商品のダミーデータは `database/seeders/data/sample_items.php` に定義しています（設計書に商品データ一覧シートはありません）

## ライセンス

MIT License（Laravel ベース）
