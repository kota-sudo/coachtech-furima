# テーブル設計書（実装準拠版）

`docs/table_design.xlsx` / `docs/er_diagram.jpg` と実装の差異を解消するための、**マイグレーション実装に準拠した正式なテーブル定義**です。

## 差異の整理（旧設計書 → 実装）

| 項目 | 旧 `table_design.xlsx` | 実装（本ドキュメント） | 対応 |
| --- | --- | --- | --- |
| テーブル数 | 9 テーブル | **10 テーブル**（`payment_methods` を追加） | 支払い方法をマスタ化し正規化 |
| `purchases` の支払い方法 | `payment_method`（文字列カラム） | **`payment_method_id`（`payment_methods` への外部キー）** | マスタ参照に変更 |

> 上記のとおり、支払い方法をマスタ管理（`payment_methods`）するため、`purchases` は `payment_method`（文字列）ではなく `payment_method_id`（外部キー）を保持します。`table_design.xlsx` / `er_diagram.jpg` も本ドキュメントの内容に合わせて更新してください。

## 共通事項

- すべての文字コードは `utf8mb4`
- `docs/table_design.xlsx` / `er_diagram.jpg` に `created_at` / `updated_at` の定義がないため、各テーブルでタイムスタンプは持ちません（各 Model で `$timestamps = false`）。
- `users` のみ `softDeletes`（`deleted_at`）あり。

## 1. users（ユーザー）

| カラム | 型 | 制約 | 説明 |
| --- | --- | --- | --- |
| id | bigint unsigned | PK, AI | ユーザーID |
| name | varchar(255) | NOT NULL | ユーザー名 |
| email | varchar(255) | NOT NULL, UNIQUE | メールアドレス |
| password | varchar(255) | NOT NULL | ハッシュ化パスワード |
| profile_image | varchar(255) | NULL | プロフィール画像パス |
| postal_code | varchar(255) | NULL | 郵便番号 |
| address | varchar(255) | NULL | 住所 |
| building | varchar(255) | NULL | 建物名 |
| email_verified_at | timestamp | NULL | メール認証日時 |
| deleted_at | timestamp | NULL | 論理削除日時 |
| remember_token | varchar(100) | NULL | ログイン保持トークン |

## 2. items（商品）

| カラム | 型 | 制約 | 説明 |
| --- | --- | --- | --- |
| id | bigint unsigned | PK, AI | 商品ID |
| user_id | bigint unsigned | FK(users.id), NOT NULL | 出品者 |
| condition_id | bigint unsigned | FK(conditions.id), NOT NULL | 商品の状態 |
| name | varchar(255) | NOT NULL | 商品名 |
| brand_name | varchar(255) | NULL | ブランド名 |
| description | text | NOT NULL | 商品説明 |
| price | int unsigned | NOT NULL | 価格 |
| is_sold | boolean | NOT NULL, default false | 売却済みフラグ |

## 3. item_images（商品画像）

| カラム | 型 | 制約 | 説明 |
| --- | --- | --- | --- |
| id | bigint unsigned | PK, AI | 画像ID |
| item_id | bigint unsigned | FK(items.id), NOT NULL | 商品 |
| image_path | varchar(255) | NOT NULL | 画像パス |
| sort_order | int | NOT NULL, default 0 | 表示順 |

## 4. categories（カテゴリ）

| カラム | 型 | 制約 | 説明 |
| --- | --- | --- | --- |
| id | bigint unsigned | PK, AI | カテゴリID |
| name | varchar(255) | NOT NULL | カテゴリ名 |

## 5. category_item（商品×カテゴリ 中間）

| カラム | 型 | 制約 | 説明 |
| --- | --- | --- | --- |
| id | bigint unsigned | PK, AI | ID |
| item_id | bigint unsigned | FK(items.id), NOT NULL | 商品 |
| category_id | bigint unsigned | FK(categories.id), NOT NULL | カテゴリ |

## 6. conditions（商品の状態）

| カラム | 型 | 制約 | 説明 |
| --- | --- | --- | --- |
| id | bigint unsigned | PK, AI | 状態ID |
| name | varchar(255) | NOT NULL | 状態名 |

## 7. likes（いいね）

| カラム | 型 | 制約 | 説明 |
| --- | --- | --- | --- |
| id | bigint unsigned | PK, AI | いいねID |
| user_id | bigint unsigned | FK(users.id), NOT NULL | ユーザー |
| item_id | bigint unsigned | FK(items.id), NOT NULL | 商品 |

- `UNIQUE(user_id, item_id)`：同一ユーザーが同一商品へ複数いいね不可。

## 8. comments（コメント）

| カラム | 型 | 制約 | 説明 |
| --- | --- | --- | --- |
| id | bigint unsigned | PK, AI | コメントID |
| user_id | bigint unsigned | FK(users.id), NOT NULL | ユーザー |
| item_id | bigint unsigned | FK(items.id), NOT NULL | 商品 |
| comment | varchar(255) | NOT NULL | コメント本文 |

## 9. payment_methods（支払い方法マスタ）

| カラム | 型 | 制約 | 説明 |
| --- | --- | --- | --- |
| id | bigint unsigned | PK, AI | 支払い方法ID |
| name | varchar(255) | NOT NULL | 支払い方法名（コンビニ支払い / カード支払い） |

## 10. purchases（購入）

| カラム | 型 | 制約 | 説明 |
| --- | --- | --- | --- |
| id | bigint unsigned | PK, AI | 購入ID |
| user_id | bigint unsigned | FK(users.id), NOT NULL | 購入者 |
| item_id | bigint unsigned | FK(items.id), NOT NULL, UNIQUE | 購入商品（1商品1購入） |
| payment_method_id | bigint unsigned | FK(payment_methods.id), NOT NULL | 支払い方法 |
| postal_code | varchar(255) | NOT NULL | 送付先郵便番号 |
| address | varchar(255) | NOT NULL | 送付先住所 |
| building | varchar(255) | NULL | 送付先建物名 |

## リレーション概要（ER）

- `users` 1 — N `items` / `likes` / `comments` / `purchases`
- `conditions` 1 — N `items`
- `items` 1 — N `item_images` / `likes` / `comments`
- `items` 1 — 1 `purchases`（`purchases.item_id` UNIQUE）
- `items` N — N `categories`（中間 `category_item`）
- `payment_methods` 1 — N `purchases`
