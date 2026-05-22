<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryItem;
use App\Models\Condition;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array{seller: array<string, string>, items: list<array<string, mixed>>} $data */
        $data = require database_path('seeders/data/sample_items.php');

        $sellerData = $data['seller'];
        $seller = User::create([
            'name' => $sellerData['name'],
            'email' => $sellerData['email'],
            'password' => Hash::make($sellerData['password']),
            'postal_code' => $sellerData['postal_code'],
            'address' => $sellerData['address'],
            'building' => $sellerData['building'],
            'email_verified_at' => now(),
        ]);

        $conditions = Condition::pluck('id', 'name');
        $categories = Category::pluck('id', 'name');

        foreach ($data['items'] as $itemData) {
            $brandName = $itemData['brand_name'] ?? null;
            if (in_array($brandName, ['なし', ''], true)) {
                $brandName = null;
            }

            $item = Item::create([
                'user_id' => $seller->id,
                'condition_id' => $conditions[$itemData['condition']],
                'name' => $itemData['name'],
                'brand_name' => $brandName,
                'description' => $itemData['description'],
                'price' => $itemData['price'],
                'is_sold' => $itemData['is_sold'],
            ]);

            ItemImage::create([
                'item_id' => $item->id,
                'image_path' => $this->resolveImagePath(
                    $itemData['image_url'],
                    $itemData['image_filename']
                ),
                'sort_order' => 0,
            ]);

            CategoryItem::create([
                'item_id' => $item->id,
                'category_id' => $categories[$itemData['category']],
            ]);
        }
    }

    private function resolveImagePath(string $imageUrl, string $filename): string
    {
        $relativePath = 'items/'.$filename;

        if (Storage::disk('public')->exists($relativePath)) {
            return $relativePath;
        }

        $response = Http::timeout(30)->get($imageUrl);

        if (! $response->successful()) {
            throw new \RuntimeException("商品画像の取得に失敗しました: {$imageUrl}");
        }

        Storage::disk('public')->put($relativePath, $response->body());

        return $relativePath;
    }
}
