<x-main-layout>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900">商品の出品</h1>

        <form
            method="POST"
            action="{{ route('items.sell.store') }}"
            enctype="multipart/form-data"
            class="mt-8 bg-white rounded-lg shadow-sm p-6 space-y-6"
        >
            @csrf

            <div>
                <x-input-label for="image" value="商品画像" />
                <input
                    id="image"
                    name="image"
                    type="file"
                    accept="image/jpeg,image/png"
                    class="block mt-1 w-full text-sm text-gray-600 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                <p class="mt-1 text-xs text-gray-500">JPEG / PNG</p>
                <x-input-error :messages="$errors->get('image')" class="mt-2" />
            </div>

            <div>
                <p class="text-sm font-medium text-gray-700">商品のカテゴリー</p>
                <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach ($categories as $category)
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input
                                type="checkbox"
                                name="category_ids[]"
                                value="{{ $category->id }}"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                @checked(in_array($category->id, old('category_ids', []), true))
                            >
                            <span>{{ $category->name }}</span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('category_ids')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="condition_id" value="商品の状態" />
                <select
                    id="condition_id"
                    name="condition_id"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                >
                    <option value="">選択してください</option>
                    @foreach ($conditions as $condition)
                        <option value="{{ $condition->id }}" @selected((int) old('condition_id') === $condition->id)>
                            {{ $condition->name }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('condition_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="name" value="商品名" />
                <x-text-input
                    id="name"
                    name="name"
                    type="text"
                    class="block mt-1 w-full"
                    :value="old('name')"
                    required
                />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="brand_name" value="ブランド名" />
                <x-text-input
                    id="brand_name"
                    name="brand_name"
                    type="text"
                    class="block mt-1 w-full"
                    :value="old('brand_name')"
                />
                <p class="mt-1 text-xs text-gray-500">任意</p>
                <x-input-error :messages="$errors->get('brand_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" value="商品の説明" />
                <textarea
                    id="description"
                    name="description"
                    rows="5"
                    maxlength="255"
                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required
                >{{ old('description') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="price" value="販売価格" />
                <div class="mt-1 flex items-center gap-2">
                    <span class="text-gray-700">¥</span>
                    <x-text-input
                        id="price"
                        name="price"
                        type="number"
                        min="0"
                        class="block w-full"
                        :value="old('price')"
                        required
                    />
                </div>
                <x-input-error :messages="$errors->get('price')" class="mt-2" />
            </div>

            <x-primary-button class="w-full justify-center">
                出品する
            </x-primary-button>
        </form>
    </div>
</x-main-layout>
