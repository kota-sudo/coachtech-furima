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
                        <span>♥ {{ $item->likes_count }}</span>
                        <span>💬 {{ $item->comments_count }}</span>
                    </div>

                    <div class="mt-6">
                        <x-primary-button type="button" class="w-full justify-center">
                            購入手続きへ
                        </x-primary-button>
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
