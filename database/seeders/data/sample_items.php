<?php

/**
 * 商品ダミーデータ定義
 *
 * docs/table_design.xlsx に「商品データ一覧」シートは存在しません。
 * 本ファイルのデータは教材で一般的なサンプルをもとにした推測値です。
 * 正式な商品データが提供されたら、この配列を差し替えてください。
 *
 * @see database/seeders/ItemSeeder.php
 */
return [
    'seller' => [
        'name' => '出品者ユーザー',
        'email' => 'seller@example.com',
        'password' => 'password',
        'postal_code' => '123-4567',
        'address' => '東京都港区芝公園',
        'building' => 'ACビル101',
    ],
    'items' => [
        [
            'name' => '腕時計',
            'brand_name' => 'Rolax',
            'description' => 'スタイリッシュなデザインのメンズ腕時計',
            'price' => 15000,
            'condition' => '良好',
            'category' => 'ファッション',
            'image_path' => 'Clock.jpg',
            'is_sold' => true,
        ],
        [
            'name' => 'HDD',
            'brand_name' => '西芝',
            'description' => '高速で信頼性の高いハードディスク',
            'price' => 5000,
            'condition' => '目立った傷や汚れなし',
            'category' => '家電',
            'image_path' => 'Hard Disk.jpg',
            'is_sold' => false,
        ],
        [
            'name' => '玉ねぎ3束',
            'brand_name' => null,
            'description' => '新鮮な玉ねぎ3束のセット',
            'price' => 300,
            'condition' => 'やや傷や汚れあり',
            'category' => 'キッチン',
            'image_path' => 'Onion.jpg',
            'is_sold' => false,
        ],
        [
            'name' => '革靴',
            'brand_name' => null,
            'description' => 'クラシックなデザインの革靴',
            'price' => 4000,
            'condition' => '状態が悪い',
            'category' => 'メンズ',
            'image_path' => 'Shoes.jpg',
            'is_sold' => false,
        ],
        [
            'name' => 'ショルダーバッグ',
            'brand_name' => null,
            'description' => 'おしゃれなショルダーバッグ',
            'price' => 3500,
            'condition' => '目立った傷や汚れなし',
            'category' => 'ファッション',
            'image_path' => 'Bag.jpg',
            'is_sold' => false,
        ],
        [
            'name' => 'マイク',
            'brand_name' => null,
            'description' => '高音質のマイク',
            'price' => 8000,
            'condition' => '良好',
            'category' => '家電',
            'image_path' => 'Microphone.jpg',
            'is_sold' => false,
        ],
        [
            'name' => 'HDMICable',
            'brand_name' => null,
            'description' => '高品質なHDMIケーブル',
            'price' => 500,
            'condition' => '目立った傷や汚れなし',
            'category' => '家電',
            'image_path' => 'HDMICable.jpg',
            'is_sold' => false,
        ],
        [
            'name' => 'Tシャツ',
            'brand_name' => null,
            'description' => '快適な素材のTシャツ',
            'price' => 1500,
            'condition' => '良好',
            'category' => 'メンズ',
            'image_path' => 'T-Shirt.jpg',
            'is_sold' => false,
        ],
        [
            'name' => '竹製の箸',
            'brand_name' => null,
            'description' => '環境に優しい竹製の箸',
            'price' => 800,
            'condition' => 'やや傷や汚れあり',
            'category' => 'キッチン',
            'image_path' => 'Chopsticks.jpg',
            'is_sold' => false,
        ],
        [
            'name' => 'COACHTECHロゴ入りTシャツ',
            'brand_name' => 'COACHTECH',
            'description' => 'COACHTECHのロゴ入りTシャツ',
            'price' => 2000,
            'condition' => '良好',
            'category' => 'ファッション',
            'image_path' => 'COACHTECH.jpg',
            'is_sold' => false,
        ],
    ],
];
