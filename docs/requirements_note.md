# coachtechフリマ 要件メモ

## プロジェクト概要
- サービス名：coachtechフリマ
- サービス概要：ある企業が開発した独自のフリマアプリ
- 制作目的：アイテムの出品と購入を行うためのフリマアプリを開発する
- 作業範囲：設計、コーディング、テスト
- 納品方法：GitHubでのリポジトリ共有
- DB：MySQL
- 開発言語：PHP
- フレームワーク：Laravel
- バージョン管理：Docker, GitHub

## 画面定義

| 画面ID | 画面名称 | パス |
|---|---|---|
| PG01 | 商品一覧画面（トップ画面） | / |
| PG02 | 商品一覧画面（マイリスト） | /?tab=mylist |
| PG03 | 会員登録画面 | /register |
| PG04 | ログイン画面 | /login |
| PG05 | 商品詳細画面 | /item/{item_id} |
| PG06 | 商品購入画面 | /purchase/{item_id} |
| PG07 | 送付先住所変更画面 | /purchase/address/{item_id} |
| PG08 | 商品出品画面 | /sell |
| PG09 | プロフィール画面 | /mypage |
| PG10 | プロフィール編集画面 | /mypage/profile |
| PG11 | プロフィール画面_購入した商品一覧 | /mypage?page=buy |
| PG12 | プロフィール画面_出品した商品一覧 | /mypage?page=sell |

※ 元資料では `/purchase/address/i{tem_id}` と見えるが、実装時は `/purchase/address/{item_id}` とする想定。

## デザイン要件
- Figmaデザインを参照してデザインする
- COACHTECHから提供された素材を使用する
- PC幅 1400〜1540px でレイアウトが崩れないようにする
- レスポンシブ対応を行う

## 認証要件
- 機能要件では Fortify 指定
- 会員登録、ログイン、ログアウトを実装
- 会員登録後はプロフィール設定画面へ遷移
- 未認証ユーザーが認証必須アクションを行った場合はログイン画面へ遷移

## プロフィール更新バリデーション（PG10）

`users` テーブルの既存カラムのみ使用する（DB設計変更なし）。

| 項目 | ルール |
|---|---|
| プロフィール画像 | JPEG または PNG、2MB以下（任意） |
| ユーザー名（name） | 入力必須、20文字以内 |
| 郵便番号（postal_code） | 入力必須、ハイフンありの8文字（例: `123-4567`） |
| 住所（address） | 入力必須 |
| 建物名（building） | 任意 |

## 商品ダミーデータ（Seeder）

基本設計書の正式データ10件を `database/seeders/data/sample_items.php` に定義する。

- 画像は COACHTECH 提供の S3 URL から取得
- `storage/app/public/items/` に保存
- `item_images.image_path` には `items/ファイル名.jpg` 形式の相対パスを保存
- 2回目以降の seed では既存ファイルがあれば再ダウンロードをスキップ

## テストケース一覧

| # | テスト対象 | 実装状況 | Featureテスト |
|---|---|---|---|
| 1 | 会員登録 | 実装済 | `Auth/RegistrationTest` |
| 2 | ログイン | 実装済 | `Auth/AuthenticationTest` |
| 3 | ログアウト | 実装済 | `Auth/AuthenticationTest` |
| 4 | 商品一覧取得 | 実装済 | `ItemIndexTest` |
| 5 | マイリスト一覧取得 | 実装済 | `ItemIndexTest` |
| 6 | 商品検索 | 実装済 | `ItemIndexTest` |
| 7 | 商品詳細 | 実装済 | `ItemDetailTest` |
| 8 | いいね | 実装済 | `LikeTest` |
| 9 | コメント | 実装済 | `CommentTest` |
| 10 | 購入 | 実装済 | `PurchaseTest` |
| 11 | 支払い方法選択 | 実装済 | `PurchaseTest` |
| 12 | 配送先変更 | 実装済 | `PurchaseAddressTest` |
| 13 | ユーザー情報取得 | 実装済 | `MypageTest` |
| 14 | ユーザー情報変更 | 実装済 | `ProfileUpdateTest` |
| 15 | 出品商品情報登録 | 実装済 | `ExhibitionTest` |

## 注意点
- DB設計の最新・正式版は `docs/table_design.md`（実装準拠）を参照する
  - テーブル仕様書どおりの **9 テーブル構成**。支払い方法は `purchases.payment_method`（integer：1=コンビニ / 2=カード）で保持し、マスタテーブルは作成しない
  - 旧 `docs/table_design.xlsx` / `docs/er_diagram.jpg` も `docs/table_design.md` に合わせて更新する
- items に category_id を直接持たせない
- items に condition 文字列カラムを持たせない
- likes テーブルを使い、favorites は使わない
- comments の本文カラム名は comment にする
- Docker と README は最終的に必要

## 実装済みの応用要件

- メール認証（Fortify + `MustVerifyEmail`、MailHog で確認）
- Stripe（カード支払い選択時に Checkout へ遷移）

## 未実装（スコープ外）

- 商品編集・削除
- 他ユーザーのプロフィール表示
- パスワードリセット / Breeze 標準プロフィール画面
