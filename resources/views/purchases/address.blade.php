<x-main-layout>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900">送付先住所の変更</h1>
        <p class="mt-2 text-sm text-gray-600">
            商品: {{ $item->name }}
        </p>

        <form
            method="POST"
            action="{{ route('purchases.address.update', $item) }}"
            class="mt-8 bg-white rounded-lg shadow-sm p-6 space-y-6"
        >
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="postal_code" value="郵便番号" />
                <x-text-input
                    id="postal_code"
                    name="postal_code"
                    type="text"
                    class="block mt-1 w-full"
                    :value="old('postal_code', $shippingAddress['postal_code'])"
                    placeholder="123-4567"
                    required
                />
                <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="address" value="住所" />
                <x-text-input
                    id="address"
                    name="address"
                    type="text"
                    class="block mt-1 w-full"
                    :value="old('address', $shippingAddress['address'])"
                    required
                />
                <x-input-error :messages="$errors->get('address')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="building" value="建物名" />
                <x-text-input
                    id="building"
                    name="building"
                    type="text"
                    class="block mt-1 w-full"
                    :value="old('building', $shippingAddress['building'])"
                />
                <p class="mt-1 text-xs text-gray-500">任意</p>
                <x-input-error :messages="$errors->get('building')" class="mt-2" />
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <x-primary-button class="justify-center">
                    更新する
                </x-primary-button>
                <a
                    href="{{ route('purchases.create', $item) }}"
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                >
                    購入画面に戻る
                </a>
            </div>
        </form>
    </div>
</x-main-layout>
