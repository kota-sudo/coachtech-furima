<header class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center gap-4">
            <a href="{{ url('/') }}" class="shrink-0 text-lg font-semibold text-gray-800">
                {{ config('app.name', 'coachtechフリマ') }}
            </a>

            <form method="GET" action="{{ url('/') }}" class="flex-1 max-w-xl mx-auto">
                @if (request('tab') === 'mylist')
                    <input type="hidden" name="tab" value="mylist">
                @endif
                <input
                    type="search"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    placeholder="商品名で検索"
                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                >
            </form>

            <div class="flex shrink-0 items-center gap-4">
                @auth
                    <a href="{{ route('mypage.profile') }}" class="text-sm text-gray-600 hover:text-gray-900">
                        プロフィール
                    </a>
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
