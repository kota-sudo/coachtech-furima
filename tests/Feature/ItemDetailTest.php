<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryItem;
use App\Models\Comment;
use App\Models\Condition;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\Like;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createItemWithRelations(): Item
    {
        $seller = User::factory()->create(['name' => '出品者']);
        $commenter = User::factory()->create(['name' => 'コメントユーザー']);

        $condition = Condition::create(['name' => '良好']);
        $category1 = Category::create(['name' => 'ファッション']);
        $category2 = Category::create(['name' => 'メンズ']);

        $item = Item::create([
            'user_id' => $seller->id,
            'condition_id' => $condition->id,
            'name' => '腕時計',
            'brand_name' => 'Rolax',
            'description' => "スタイリッシュなデザイン\nメンズ腕時計",
            'price' => 15000,
            'is_sold' => false,
        ]);

        ItemImage::create([
            'item_id' => $item->id,
            'image_path' => 'items/first.jpg',
            'sort_order' => 0,
        ]);
        ItemImage::create([
            'item_id' => $item->id,
            'image_path' => 'items/second.jpg',
            'sort_order' => 1,
        ]);

        CategoryItem::create(['item_id' => $item->id, 'category_id' => $category1->id]);
        CategoryItem::create(['item_id' => $item->id, 'category_id' => $category2->id]);

        Like::create(['user_id' => $commenter->id, 'item_id' => $item->id]);
        Like::create(['user_id' => $seller->id, 'item_id' => $item->id]);

        Comment::create([
            'user_id' => $commenter->id,
            'item_id' => $item->id,
            'comment' => 'とても良い商品です。',
        ]);

        return $item;
    }

    public function test_guest_can_view_item_detail(): void
    {
        $item = $this->createItemWithRelations();

        $this->get(route('items.show', $item))
            ->assertOk()
            ->assertSee('腕時計', false);
    }

    public function test_item_detail_displays_product_information(): void
    {
        $item = $this->createItemWithRelations();

        $this->get(route('items.show', $item))
            ->assertOk()
            ->assertSee('Rolax', false)
            ->assertSee('¥15,000', false)
            ->assertSee('スタイリッシュなデザイン', false)
            ->assertSee('良好', false)
            ->assertSee('ファッション', false)
            ->assertSee('メンズ', false);
    }

    public function test_item_detail_displays_multiple_categories(): void
    {
        $item = $this->createItemWithRelations();

        $response = $this->get(route('items.show', $item));

        $response->assertOk();
        $this->assertStringContainsString('ファッション', $response->getContent());
        $this->assertStringContainsString('メンズ', $response->getContent());
        $this->assertStringContainsString('ファッション / メンズ', $response->getContent());
    }

    public function test_item_detail_displays_likes_and_comments_count(): void
    {
        $item = $this->createItemWithRelations();

        $this->get(route('items.show', $item))
            ->assertOk()
            ->assertSee('♥ 2', false)
            ->assertSee('💬 1', false);
    }

    public function test_item_detail_displays_comments_with_user(): void
    {
        $item = $this->createItemWithRelations();

        $this->get(route('items.show', $item))
            ->assertOk()
            ->assertSee('コメントユーザー', false)
            ->assertSee('とても良い商品です。', false);
    }

    public function test_item_detail_displays_all_images(): void
    {
        $item = $this->createItemWithRelations();

        $this->get(route('items.show', $item))
            ->assertOk()
            ->assertSee('items/first.jpg', false)
            ->assertSee('items/second.jpg', false);
    }

    public function test_item_detail_displays_na_when_brand_is_null(): void
    {
        $seller = User::factory()->create();
        $condition = Condition::create(['name' => '良好']);

        $item = Item::create([
            'user_id' => $seller->id,
            'condition_id' => $condition->id,
            'name' => 'テスト商品',
            'brand_name' => null,
            'description' => '説明',
            'price' => 1000,
            'is_sold' => false,
        ]);

        $this->get(route('items.show', $item))
            ->assertOk()
            ->assertSee('ブランド: なし', false);
    }

    public function test_item_index_links_to_detail_page(): void
    {
        $item = $this->createItemWithRelations();

        $this->get('/')
            ->assertOk()
            ->assertSee(route('items.show', $item), false);
    }
}
