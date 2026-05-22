<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Condition;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExhibitionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{category: Category, condition: Condition}
     */
    private function seedMasters(): array
    {
        $category = Category::create(['name' => 'ファッション']);
        $condition = Condition::create(['name' => '良好']);

        return compact('category', 'condition');
    }

    private function fakePng(): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent('item.png', $png);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(Category $category, Condition $condition, ?UploadedFile $image = null): array
    {
        return [
            'name' => '出品テスト商品',
            'description' => '商品の説明文です。',
            'image' => $image ?? $this->fakePng(),
            'category_ids' => [$category->id],
            'condition_id' => $condition->id,
            'price' => 3000,
            'brand_name' => 'テストブランド',
        ];
    }

    public function test_guest_is_redirected_to_login_when_accessing_sell_page(): void
    {
        $this->get(route('items.sell'))->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_when_posting_exhibition(): void
    {
        $masters = $this->seedMasters();

        $this->post(route('items.sell.store'), $this->validPayload($masters['category'], $masters['condition']))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_sell_page(): void
    {
        $this->seedMasters();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('items.sell'))
            ->assertOk()
            ->assertSee('商品の出品', false)
            ->assertSee('商品画像', false)
            ->assertSee('ファッション', false)
            ->assertSee('良好', false);
    }

    public function test_authenticated_user_can_exhibit_item(): void
    {
        Storage::fake('public');

        $masters = $this->seedMasters();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('items.sell.store'), $this->validPayload($masters['category'], $masters['condition']));

        $item = Item::first();
        $this->assertNotNull($item);

        $response->assertRedirect(route('items.show', $item));

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'user_id' => $user->id,
            'condition_id' => $masters['condition']->id,
            'name' => '出品テスト商品',
            'brand_name' => 'テストブランド',
            'description' => '商品の説明文です。',
            'price' => 3000,
            'is_sold' => false,
        ]);

        $itemImage = ItemImage::where('item_id', $item->id)->first();
        $this->assertNotNull($itemImage);
        $this->assertStringStartsWith('items/', $itemImage->image_path);
        Storage::disk('public')->assertExists($itemImage->image_path);

        $this->assertDatabaseHas('category_item', [
            'item_id' => $item->id,
            'category_id' => $masters['category']->id,
        ]);

        $this->actingAs($user)
            ->get(route('items.show', $item))
            ->assertOk()
            ->assertSee('出品テスト商品', false)
            ->assertSee('テストブランド', false)
            ->assertSee('ファッション', false);
    }

    public function test_name_is_required(): void
    {
        $masters = $this->seedMasters();
        $user = User::factory()->create();
        $payload = $this->validPayload($masters['category'], $masters['condition']);
        $payload['name'] = '';

        $this->actingAs($user)
            ->from(route('items.sell'))
            ->post(route('items.sell.store'), $payload)
            ->assertRedirect(route('items.sell'))
            ->assertSessionHasErrors(['name' => '商品名を入力してください']);
    }

    public function test_description_is_required_and_max_255(): void
    {
        $masters = $this->seedMasters();
        $user = User::factory()->create();

        $payload = $this->validPayload($masters['category'], $masters['condition']);
        $payload['description'] = '';

        $this->actingAs($user)
            ->from(route('items.sell'))
            ->post(route('items.sell.store'), $payload)
            ->assertRedirect(route('items.sell'))
            ->assertSessionHasErrors(['description' => '商品の説明を入力してください']);

        $payload = $this->validPayload($masters['category'], $masters['condition']);
        $payload['description'] = str_repeat('あ', 256);

        $this->actingAs($user)
            ->from(route('items.sell'))
            ->post(route('items.sell.store'), $payload)
            ->assertRedirect(route('items.sell'))
            ->assertSessionHasErrors(['description' => '商品の説明は255文字以内で入力してください']);
    }

    public function test_image_is_required_and_must_be_jpeg_or_png(): void
    {
        Storage::fake('public');
        $masters = $this->seedMasters();
        $user = User::factory()->create();

        $payload = $this->validPayload($masters['category'], $masters['condition']);
        unset($payload['image']);

        $this->actingAs($user)
            ->from(route('items.sell'))
            ->post(route('items.sell.store'), $payload)
            ->assertRedirect(route('items.sell'))
            ->assertSessionHasErrors(['image' => '商品画像を選択してください']);

        $payload = $this->validPayload($masters['category'], $masters['condition']);
        $payload['image'] = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->from(route('items.sell'))
            ->post(route('items.sell.store'), $payload)
            ->assertRedirect(route('items.sell'))
            ->assertSessionHasErrors(['image' => '商品画像はJPEGまたはPNG形式で選択してください']);
    }

    public function test_category_and_condition_are_required(): void
    {
        $masters = $this->seedMasters();
        $user = User::factory()->create();

        $payload = $this->validPayload($masters['category'], $masters['condition']);
        unset($payload['category_ids']);

        $this->actingAs($user)
            ->from(route('items.sell'))
            ->post(route('items.sell.store'), $payload)
            ->assertRedirect(route('items.sell'))
            ->assertSessionHasErrors(['category_ids' => '商品のカテゴリーを選択してください']);

        $payload = $this->validPayload($masters['category'], $masters['condition']);
        unset($payload['condition_id']);

        $this->actingAs($user)
            ->from(route('items.sell'))
            ->post(route('items.sell.store'), $payload)
            ->assertRedirect(route('items.sell'))
            ->assertSessionHasErrors(['condition_id' => '商品の状態を選択してください']);
    }

    public function test_price_is_required_numeric_and_non_negative(): void
    {
        $masters = $this->seedMasters();
        $user = User::factory()->create();

        $payload = $this->validPayload($masters['category'], $masters['condition']);
        unset($payload['price']);

        $this->actingAs($user)
            ->from(route('items.sell'))
            ->post(route('items.sell.store'), $payload)
            ->assertRedirect(route('items.sell'))
            ->assertSessionHasErrors(['price' => '販売価格を入力してください']);

        $payload = $this->validPayload($masters['category'], $masters['condition']);
        $payload['price'] = 'abc';

        $this->actingAs($user)
            ->from(route('items.sell'))
            ->post(route('items.sell.store'), $payload)
            ->assertRedirect(route('items.sell'))
            ->assertSessionHasErrors(['price' => '販売価格は数値で入力してください']);

        $payload = $this->validPayload($masters['category'], $masters['condition']);
        $payload['price'] = -1;

        $this->actingAs($user)
            ->from(route('items.sell'))
            ->post(route('items.sell.store'), $payload)
            ->assertRedirect(route('items.sell'))
            ->assertSessionHasErrors(['price' => '販売価格は0円以上で入力してください']);
    }

    public function test_brand_name_is_optional(): void
    {
        Storage::fake('public');
        $masters = $this->seedMasters();
        $user = User::factory()->create();

        $payload = $this->validPayload($masters['category'], $masters['condition']);
        unset($payload['brand_name']);

        $this->actingAs($user)
            ->post(route('items.sell.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('items', [
            'user_id' => $user->id,
            'brand_name' => null,
        ]);
    }
}
