<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Models\Item;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PurchaseController extends Controller
{
    public function create(Item $item): View
    {
        $this->ensurePurchasable($item);

        $item->load([
            'itemImages' => fn ($query) => $query->orderBy('sort_order'),
        ]);

        return view('purchases.create', [
            'item' => $item,
            'paymentMethods' => PaymentMethod::orderBy('id')->get(),
            'user' => auth()->user(),
        ]);
    }

    public function store(PurchaseRequest $request, Item $item): RedirectResponse
    {
        $this->ensurePurchasable($item);

        $validated = $request->validated();

        DB::transaction(function () use ($item, $validated) {
            Purchase::create([
                'user_id' => auth()->id(),
                'item_id' => $item->id,
                'payment_method_id' => $validated['payment_method_id'],
                'postal_code' => $validated['postal_code'],
                'address' => $validated['address'],
                'building' => $validated['building'] ?? null,
            ]);

            $item->update(['is_sold' => true]);
        });

        return redirect()->route('items.index');
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
