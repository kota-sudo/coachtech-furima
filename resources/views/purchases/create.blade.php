<x-main-layout>
    @php
        $primaryImage = $item->itemImages->first();
        $selectedPaymentMethod = (int) old('payment_method', array_key_first($paymentMethods));
    @endphp

    <div class="max-w-5xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900">商品の購入</h1>

        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">商品情報</h2>

                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                    @if ($primaryImage)
                        <img
                            src="{{ asset('storage/'.$primaryImage->image_path) }}"
                            alt="{{ $item->name }}"
                            class="w-full h-full object-cover"
                        >
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">
                            No Image
                        </div>
                    @endif
                </div>

                <div>
                    <p class="text-sm text-gray-600">商品名</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $item->name }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">商品代金</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">¥{{ number_format($item->price) }}</p>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('purchases.store', $item) }}"
                class="space-y-6"
            >
                @csrf

                <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                    <h2 class="text-lg font-semibold text-gray-900">お支払い方法</h2>

                    <div>
                        <x-input-label for="payment_method" value="支払い方法" />
                        <select
                            id="payment_method"
                            name="payment_method"
                            class="block mt-1 w-full border-gray-300 focus:border-red-400 focus:ring-red-400 rounded-md shadow-sm"
                        >
                            @foreach ($paymentMethods as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected($selectedPaymentMethod === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-lg font-semibold text-gray-900">配送先</h2>
                        <a
                            href="{{ route('purchases.address', $item) }}"
                            class="text-sm text-red-500 hover:text-red-600 underline shrink-0"
                        >
                            送付先を変更する
                        </a>
                    </div>

                    <dl class="text-sm space-y-2">
                        <div>
                            <dt class="text-gray-600">郵便番号</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ old('postal_code', $shippingAddress['postal_code']) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-600">住所</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ old('address', $shippingAddress['address']) }}</dd>
                        </div>
                        @if (old('building', $shippingAddress['building']))
                            <div>
                                <dt class="text-gray-600">建物名</dt>
                                <dd class="mt-1 font-medium text-gray-900">{{ old('building', $shippingAddress['building']) }}</dd>
                            </div>
                        @endif
                    </dl>

                    <input type="hidden" name="postal_code" value="{{ old('postal_code', $shippingAddress['postal_code']) }}">
                    <input type="hidden" name="address" value="{{ old('address', $shippingAddress['address']) }}">
                    <input type="hidden" name="building" value="{{ old('building', $shippingAddress['building']) }}">

                    <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    <x-input-error :messages="$errors->get('building')" class="mt-2" />
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900">小計</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-600">商品代金</dt>
                            <dd class="font-medium text-gray-900">¥{{ number_format($item->price) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">支払い方法</dt>
                            <dd id="payment-method-summary" class="font-medium text-gray-900">
                                {{ $paymentMethods[$selectedPaymentMethod] ?? '—' }}
                            </dd>
                        </div>
                    </dl>
                    <p class="mt-4 text-xs text-gray-500">
                        ※ 「購入する」を押すと Stripe の決済画面に遷移します（カードはテストカード番号、コンビニはコンビニ決済で動作確認できます）。
                    </p>
                </div>

                <x-primary-button class="w-full justify-center">
                    購入する
                </x-primary-button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('payment_method')?.addEventListener('change', function () {
            const summary = document.getElementById('payment-method-summary');
            if (summary) {
                summary.textContent = this.options[this.selectedIndex].text;
            }
        });
    </script>
</x-main-layout>
