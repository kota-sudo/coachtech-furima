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

## 注意点
- DB設計は `docs/table_design.xlsx` と `docs/er_diagram.jpg` を最優先する
- 設計書にないテーブル・カラムは追加しない
- items に category_id を直接持たせない
- items に condition 文字列カラムを持たせない
- likes テーブルを使い、favorites は使わない
- comments の本文カラム名は comment にする
- Docker と README は最終的に必要