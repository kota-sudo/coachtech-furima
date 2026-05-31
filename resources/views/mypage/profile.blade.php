<x-main-layout>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900">プロフィール設定</h1>

        @if (session('status') === 'profile-updated')
            <p class="mt-4 rounded-md bg-green-50 p-3 text-sm font-medium text-green-700">
                プロフィールを更新しました。
            </p>
        @endif

        <form
            method="POST"
            action="{{ route('mypage.profile.update') }}"
            enctype="multipart/form-data"
            class="mt-8 bg-white rounded-lg shadow-sm p-6 space-y-6"
        >
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="profile_image" value="プロフィール画像" />
                <div class="mt-2 flex items-center gap-4">
                    @if ($user->profile_image)
                        <img
                            src="{{ asset('storage/'.$user->profile_image) }}"
                            alt="プロフィール画像"
                            class="h-20 w-20 rounded-full object-cover border border-gray-200"
                        >
                    @else
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-200 text-xs text-gray-500 border border-gray-200">
                            No Image
                        </div>
                    @endif
                    <label for="profile_image" class="inline-flex cursor-pointer items-center rounded-md border border-red-500 px-4 py-1.5 text-sm font-medium text-red-500 transition hover:bg-red-50">
                        画像を選択する
                    </label>
                </div>
                <input
                    id="profile_image"
                    name="profile_image"
                    type="file"
                    accept="image/jpeg,image/png"
                    class="sr-only"
                    onchange="document.getElementById('profile-image-filename').textContent = this.files[0] ? this.files[0].name : ''"
                >
                <p id="profile-image-filename" class="mt-2 text-sm text-gray-700"></p>
                <p class="mt-1 text-xs text-gray-500">JPEG / PNG、2MB以下</p>
                <x-input-error :messages="$errors->get('profile_image')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="name" value="ユーザー名" />
                <x-text-input
                    id="name"
                    name="name"
                    type="text"
                    class="block mt-1 w-full"
                    :value="old('name', $user->name)"
                    maxlength="20"
                    required
                    autofocus
                />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="postal_code" value="郵便番号" />
                <x-text-input
                    id="postal_code"
                    name="postal_code"
                    type="text"
                    class="block mt-1 w-full"
                    :value="old('postal_code', $user->postal_code)"
                    placeholder="123-4567"
                    maxlength="8"
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
                    :value="old('address', $user->address)"
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
                    :value="old('building', $user->building)"
                />
                <p class="mt-1 text-xs text-gray-500">任意</p>
                <x-input-error :messages="$errors->get('building')" class="mt-2" />
            </div>

            <x-primary-button class="w-full justify-center">
                更新する
            </x-primary-button>
        </form>
    </div>
</x-main-layout>
