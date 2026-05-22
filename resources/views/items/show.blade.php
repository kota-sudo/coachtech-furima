<x-main-layout>
    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-4">
                @forelse ($item->itemImages as $image)
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <img
                            src="{{ asset('storage/'.$image->image_path) }}"
                            alt="{{ $item->name }}"
                            class="w-full aspect-square object-cover"
                        >
                    </div>
                @empty
                    <div class="bg-white rounded-lg shadow-sm p-12 text-center text-gray-400">
                        No Image
                    </div>
                @endforelse
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $item->name }}</h1>

                    <p class="mt-2 text-sm text-gray-600">
                        ブランド: {{ $item->brand_name ?? 'なし' }}
                    </p>

                    <p class="mt-4 text-3xl font-bold text-gray-900">
                        ¥{{ number_format($item->price) }}
                    </p>

                    <div class="mt-4 flex items-center gap-6 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            @auth
                                <form method="POST" action="{{ route('items.like', $item) }}" class="inline">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1 transition"
                                        aria-label="{{ $isLiked ? 'いいねを解除' : 'いいねする' }}"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            class="w-6 h-6 {{ $isLiked ? 'text-red-500 fill-red-500' : 'text-gray-400 fill-none' }}"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                            />
                                        </svg>
                                    </button>
                                </form>
                            @else
                                <a
                                    href="{{ route('login') }}"
                                    class="inline-flex items-center gap-1 text-gray-400 hover:text-gray-600"
                                    aria-label="ログインしていいねする"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        class="w-6 h-6 fill-none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                        />
                                    </svg>
                                </a>
                            @endauth
                            <span>{{ $item->likes_count }}</span>
                        </div>
                        <span>💬 {{ $item->comments_count }}</span>
                    </div>

                    @php
                        $canPurchase = auth()->check()
                            && $item->user_id !== auth()->id()
                            && ! $item->is_sold
                            && ! $item->purchase()->exists();
                    @endphp

                    <div class="mt-6">
                        @if ($canPurchase)
                            <a href="{{ route('purchases.create', $item) }}" class="block">
                                <x-primary-button type="button" class="w-full justify-center">
                                    購入手続きへ
                                </x-primary-button>
                            </a>
                        @elseif (auth()->check() && ($item->is_sold || $item->purchase()->exists()))
                            <x-primary-button type="button" class="w-full justify-center opacity-50 cursor-not-allowed" disabled>
                                売り切れ
                            </x-primary-button>
                        @elseif (auth()->check() && $item->user_id === auth()->id())
                            <x-primary-button type="button" class="w-full justify-center opacity-50 cursor-not-allowed" disabled>
                                購入手続きへ
                            </x-primary-button>
                        @else
                            <a href="{{ route('login') }}" class="block">
                                <x-primary-button type="button" class="w-full justify-center">
                                    購入手続きへ
                                </x-primary-button>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900">商品説明</h2>
                    <p class="mt-3 text-sm text-gray-700 whitespace-pre-line">{{ $item->description }}</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900">商品情報</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div>
                            <dt class="font-medium text-gray-700">カテゴリ</dt>
                            <dd class="mt-1 text-gray-600">
                                @if ($item->categories->isNotEmpty())
                                    {{ $item->categories->pluck('name')->join(' / ') }}
                                @else
                                    なし
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">商品の状態</dt>
                            <dd class="mt-1 text-gray-600">{{ $item->condition->name }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="mt-8 bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900">
                コメント（{{ $item->comments_count }}）
            </h2>

            <div class="mt-6">
                @auth
                    <form method="POST" action="{{ route('items.comment', $item) }}" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="comment" value="コメントを入力" />
                            <textarea
                                id="comment"
                                name="comment"
                                rows="4"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                placeholder="コメントを入力してください"
                            >{{ old('comment') }}</textarea>
                            <x-input-error :messages="$errors->get('comment')" class="mt-2" />
                        </div>
                        <x-primary-button>送信する</x-primary-button>
                    </form>
                @else
                    <p class="text-sm text-gray-600">
                        コメントするには<a href="{{ route('login') }}" class="underline text-indigo-600 hover:text-indigo-800">ログイン</a>してください。
                    </p>
                @endauth
            </div>

            @if ($item->comments->isEmpty())
                <p class="mt-4 text-sm text-gray-500">コメントはまだありません。</p>
            @else
                <ul class="mt-4 divide-y divide-gray-100">
                    @foreach ($item->comments as $comment)
                        <li class="py-4">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $comment->user->name }}
                            </p>
                            <p class="mt-1 text-sm text-gray-700">
                                {{ $comment->comment }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-main-layout>
