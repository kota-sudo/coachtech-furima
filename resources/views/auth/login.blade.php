<x-guest-layout>
    <h1 class="text-xl font-semibold text-gray-800 mb-6">ログイン</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" value="メールアドレス" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="パスワード" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center">
                ログインする
            </x-primary-button>
        </div>

        <div class="mt-4 text-center">
            <a class="text-sm text-red-500 hover:text-red-600 underline" href="{{ route('register') }}">
                会員登録はこちら
            </a>
        </div>
    </form>
</x-guest-layout>
