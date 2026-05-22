<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            プロフィール設定
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('status') === 'profile-updated')
                        <p class="mb-4 text-sm text-green-600">
                            プロフィールを更新しました。
                        </p>
                    @endif

                    <form method="POST" action="{{ route('mypage.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="profile_image" value="プロフィール画像" />
                            @if ($user->profile_image)
                                <div class="mt-2 mb-3">
                                    <img
                                        src="{{ asset('storage/'.$user->profile_image) }}"
                                        alt="プロフィール画像"
                                        class="h-24 w-24 rounded-full object-cover border border-gray-200"
                                    >
                                </div>
                            @endif
                            <input
                                id="profile_image"
                                name="profile_image"
                                type="file"
                                accept="image/jpeg,image/png"
                                class="block w-full text-sm text-gray-600 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
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
                            <x-input-error :messages="$errors->get('building')" class="mt-2" />
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button>
                                更新する
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
