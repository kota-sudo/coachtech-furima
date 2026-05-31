<header class="bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center gap-4">
            <a href="{{ url('/') }}" class="shrink-0 text-lg font-semibold tracking-wide text-white">
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
                    class="w-full border-transparent bg-white focus:border-red-400 focus:ring-red-400 rounded-md shadow-sm"
                >
            </form>

            <div class="flex shrink-0 items-center gap-4">
                @auth
                    <a href="{{ route('mypage.index') }}" class="text-sm text-gray-200 hover:text-white">
                        マイページ
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-200 hover:text-white">ログアウト</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-200 hover:text-white">ログイン</a>
                    <a href="{{ route('register') }}" class="text-sm text-gray-200 hover:text-white">会員登録</a>
                @endauth
                <a href="{{ route('items.sell') }}" class="inline-flex items-center rounded-md bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600">
                    出品
                </a>
            </div>
        </div>
    </div>
</header>
