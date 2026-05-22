<header class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <a href="{{ url('/') }}" class="text-lg font-semibold text-gray-800">
                {{ config('app.name', 'coachtechフリマ') }}
            </a>

            <div class="flex items-center gap-4">
                @auth
                    <span class="text-sm text-gray-600">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <x-primary-button type="submit">ログアウト</x-primary-button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900">ログイン</a>
                    <a href="{{ route('register') }}" class="text-sm text-gray-600 hover:text-gray-900">会員登録</a>
                @endauth
            </div>
        </div>
    </div>
</header>
