<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PurchaseAddressController extends Controller
{
    public function edit(Item $item): View
    {
        $this->ensurePurchasable($item);

        $shippingAddress = $this->resolveShippingAddress($item);

        return view('purchases.address', [
            'item' => $item,
            'shippingAddress' => $shippingAddress,
        ]);
    }

    public function update(AddressRequest $request, Item $item): RedirectResponse
    {
        $this->ensurePurchasable($item);

        $validated = $request->validated();

        session([
            $this->sessionKey($item) => [
                'postal_code' => $validated['postal_code'],
                'address' => $validated['address'],
                'building' => $validated['building'] ?? null,
            ],
        ]);

        return redirect()->route('purchases.create', $item);
    }

    private function sessionKey(Item $item): string
    {
        return 'purchase_address.'.$item->id;
    }

    /**
     * @return array{postal_code: ?string, address: ?string, building: ?string}
     */
    private function resolveShippingAddress(Item $item): array
    {
        $user = auth()->user();
        $sessionAddress = session($this->sessionKey($item));

        if (is_array($sessionAddress)) {
            return [
                'postal_code' => $sessionAddress['postal_code'] ?? $user->postal_code,
                'address' => $sessionAddress['address'] ?? $user->address,
                'building' => $sessionAddress['building'] ?? $user->building,
            ];
        }

        return [
            'postal_code' => $user->postal_code,
            'address' => $user->address,
            'building' => $user->building,
        ];
    }

    private function ensurePurchasable(Item $item): void
    {
        if ($item->user_id === auth()->id()) {
            throw new HttpException(403, '自分の商品は購入できません。');
        }

        if ($item->is_sold || $item->purchase()->exists()) {
            throw new HttpException(403, 'この商品は購入できません。');
        }
    }
}
