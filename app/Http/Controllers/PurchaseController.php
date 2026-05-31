<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Stripe\StripeClient;
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
            'paymentMethods' => Purchase::PAYMENT_METHODS,
            'shippingAddress' => $this->resolveShippingAddress($item),
        ]);
    }

    public function store(PurchaseRequest $request, Item $item): RedirectResponse
    {
        $this->ensurePurchasable($item);

        $validated = $request->validated();
        $shippingAddress = $this->resolveShippingAddress($item);
        $paymentMethod = (int) $validated['payment_method'];

        $purchaseData = [
            'payment_method' => $paymentMethod,
            'postal_code' => $shippingAddress['postal_code'] ?? $validated['postal_code'],
            'address' => $shippingAddress['address'] ?? $validated['address'],
            'building' => $shippingAddress['building'] ?? $validated['building'] ?? null,
        ];

        if ($this->stripeEnabled()) {
            return $this->redirectToStripeCheckout($item, $paymentMethod, $purchaseData);
        }

        $this->completePurchase($item, $purchaseData);

        return redirect()->route('items.index');
    }

    public function success(Request $request, Item $item): RedirectResponse
    {
        $sessionId = $request->query('session_id');
        $pending = session($this->checkoutSessionKey($item));

        if (! $sessionId || ! is_array($pending) || $pending['session_id'] !== $sessionId) {
            return redirect()->route('purchases.create', $item);
        }

        if (! $this->ensurePurchasableSilently($item)) {
            session()->forget($this->checkoutSessionKey($item));

            return redirect()->route('items.index');
        }

        $checkoutSession = $this->stripe()->checkout->sessions->retrieve($sessionId);

        if ($checkoutSession->payment_status !== 'paid') {
            return redirect()->route('purchases.create', $item);
        }

        $this->completePurchase($item, $pending['purchase']);

        session()->forget($this->checkoutSessionKey($item));

        return redirect()->route('items.index');
    }

    public function cancel(Item $item): RedirectResponse
    {
        session()->forget($this->checkoutSessionKey($item));

        return redirect()->route('purchases.create', $item);
    }

    private function completePurchase(Item $item, array $purchaseData): void
    {
        DB::transaction(function () use ($item, $purchaseData) {
            Purchase::create(array_merge(['user_id' => auth()->id(), 'item_id' => $item->id], $purchaseData));

            $item->update(['is_sold' => true]);
        });

        session()->forget($this->sessionKey($item));
    }

    private function redirectToStripeCheckout(Item $item, int $paymentMethod, array $purchaseData): RedirectResponse
    {
        $checkoutSession = $this->stripe()->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => $paymentMethod === Purchase::PAYMENT_CARD ? ['card'] : ['konbini'],
            'customer_email' => auth()->user()->email,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'jpy',
                    'unit_amount' => $item->price,
                    'product_data' => [
                        'name' => $item->name,
                    ],
                ],
            ]],
            'success_url' => route('purchases.success', $item).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('purchases.cancel', $item),
        ]);

        session([$this->checkoutSessionKey($item) => [
            'session_id' => $checkoutSession->id,
            'purchase' => $purchaseData,
        ]]);

        return redirect()->away($checkoutSession->url);
    }

    private function stripeEnabled(): bool
    {
        return ! empty(config('services.stripe.secret'));
    }

    private function stripe(): StripeClient
    {
        return new StripeClient(config('services.stripe.secret'));
    }

    private function sessionKey(Item $item): string
    {
        return 'purchase_address.'.$item->id;
    }

    private function checkoutSessionKey(Item $item): string
    {
        return 'purchase_checkout.'.$item->id;
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

    private function ensurePurchasableSilently(Item $item): bool
    {
        return $item->user_id !== auth()->id()
            && ! $item->is_sold
            && ! $item->purchase()->exists();
    }
}
