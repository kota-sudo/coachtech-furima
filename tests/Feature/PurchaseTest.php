<?php

namespace Tests\Feature;

use App\Models\Condition;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    private function createItem(User $seller, array $attributes = []): Item
    {
        $condition = Condition::create(['name' => '良好']);

        $item = Item::create(array_merge([
            'user_id' => $seller->id,
            'condition_id' => $condition->id,
            'name' => '購入テスト商品',
            'brand_name' => null,
            'description' => '説明',
            'price' => 5000,
            'is_sold' => false,
        ], $attributes));

        ItemImage::create([
            'item_id' => $item->id,
            'image_path' => 'items/test.jpg',
            'sort_order' => 0,
        ]);

        return $item;
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(int $paymentMethod = Purchase::PAYMENT_CARD, array $overrides = []): array
    {
        return array_merge([
            'payment_method' => $paymentMethod,
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区',
            'building' => 'テストビル',
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login_when_accessing_purchase_page(): void
    {
        $item = $this->createItem(User::factory()->create());

        $this->get(route('purchases.create', $item))
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_when_purchasing(): void
    {
        $item = $this->createItem(User::factory()->create());

        $this->post(route('purchases.store', $item), $this->validPayload())
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_purchase_page(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create([
            'postal_code' => '100-0001',
            'address' => '東京都千代田区',
            'building' => '皇居',
        ]);
        $item = $this->createItem($seller);

        $this->actingAs($buyer)
            ->get(route('purchases.create', $item))
            ->assertOk()
            ->assertSee('商品の購入', false)
            ->assertSee('購入テスト商品', false)
            ->assertSee('¥5,000', false)
            ->assertSee('コンビニ支払い', false)
            ->assertSee('カード支払い', false)
            ->assertSee('value="100-0001"', false)
            ->assertSee('東京都千代田区', false)
            ->assertSee('皇居', false)
            ->assertSee('id="payment-method-summary"', false);
    }

    public function test_selected_payment_method_is_reflected_in_summary_area(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createItem($seller);

        $this->actingAs($buyer)
            ->get(route('purchases.create', $item))
            ->assertOk()
            ->assertSee('id="payment-method-summary"', false)
            ->assertSee('コンビニ支払い', false);

        $this->actingAs($buyer)
            ->from(route('purchases.create', $item))
            ->post(route('purchases.store', $item), [
                'payment_method' => Purchase::PAYMENT_CARD,
                'postal_code' => '',
                'address' => '',
            ])
            ->assertRedirect(route('purchases.create', $item));

        $this->actingAs($buyer)
            ->get(route('purchases.create', $item))
            ->assertOk()
            ->assertSee('カード支払い', false);
    }

    public function test_user_cannot_purchase_own_item(): void
    {
        $seller = User::factory()->create();
        $item = $this->createItem($seller);

        $this->actingAs($seller)
            ->get(route('purchases.create', $item))
            ->assertForbidden();

        $this->actingAs($seller)
            ->post(route('purchases.store', $item), $this->validPayload())
            ->assertForbidden();
    }

    public function test_user_cannot_purchase_sold_item(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createItem($seller, ['is_sold' => true]);

        $this->actingAs($buyer)
            ->get(route('purchases.create', $item))
            ->assertForbidden();

        $this->actingAs($buyer)
            ->post(route('purchases.store', $item), $this->validPayload())
            ->assertForbidden();
    }

    public function test_user_cannot_purchase_item_with_existing_purchase(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $otherBuyer = User::factory()->create();
        $item = $this->createItem($seller);

        Purchase::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'payment_method' => Purchase::PAYMENT_CARD,
            'postal_code' => '123-4567',
            'address' => '東京都',
        ]);

        $this->actingAs($otherBuyer)
            ->get(route('purchases.create', $item))
            ->assertForbidden();
    }

    public function test_authenticated_user_can_complete_purchase(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createItem($seller, ['name' => '購入完了商品']);

        $this->actingAs($buyer)
            ->post(route('purchases.store', $item), $this->validPayload())
            ->assertRedirect(route('items.index'));

        $this->assertDatabaseHas('purchases', [
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'payment_method' => Purchase::PAYMENT_CARD,
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区',
            'building' => 'テストビル',
        ]);

        $this->assertTrue($item->fresh()->is_sold);

        $this->get('/')
            ->assertOk()
            ->assertSee('Sold', false)
            ->assertSee('購入完了商品', false);
    }

    public function test_purchased_item_appears_on_mypage_buy_tab(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createItem($seller, ['name' => 'マイページ購入商品']);

        $this->actingAs($buyer)
            ->post(route('purchases.store', $item), $this->validPayload());

        $this->actingAs($buyer)
            ->get(route('mypage.index', ['page' => 'buy']))
            ->assertOk()
            ->assertSee('マイページ購入商品', false)
            ->assertSee('Sold', false);
    }

    public function test_payment_method_is_required(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createItem($seller);

        $payload = $this->validPayload();
        unset($payload['payment_method']);

        $this->actingAs($buyer)
            ->from(route('purchases.create', $item))
            ->post(route('purchases.store', $item), $payload)
            ->assertRedirect(route('purchases.create', $item))
            ->assertSessionHasErrors(['payment_method' => '支払い方法を選択してください']);
    }

    public function test_postal_code_and_address_are_required(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createItem($seller);

        $payload = $this->validPayload();
        $payload['postal_code'] = '';
        $payload['address'] = '';

        $this->actingAs($buyer)
            ->from(route('purchases.create', $item))
            ->post(route('purchases.store', $item), $payload)
            ->assertRedirect(route('purchases.create', $item))
            ->assertSessionHasErrors([
                'postal_code' => '郵便番号を入力してください',
                'address' => '住所を入力してください',
            ]);
    }

    public function test_postal_code_must_match_hyphenated_format(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createItem($seller);

        $payload = $this->validPayload(Purchase::PAYMENT_CARD, ['postal_code' => '1234567']);

        $this->actingAs($buyer)
            ->from(route('purchases.create', $item))
            ->post(route('purchases.store', $item), $payload)
            ->assertRedirect(route('purchases.create', $item))
            ->assertSessionHasErrors([
                'postal_code' => '郵便番号はハイフンありの8文字で入力してください',
            ]);
    }

    public function test_building_is_optional(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createItem($seller);

        $payload = $this->validPayload(Purchase::PAYMENT_CONVENIENCE);
        unset($payload['building']);

        $this->actingAs($buyer)
            ->post(route('purchases.store', $item), $payload)
            ->assertRedirect(route('items.index'));

        $this->assertDatabaseHas('purchases', [
            'item_id' => $item->id,
            'building' => null,
        ]);
    }
}
