<x-main-layout>
    <div class="mb-6 border-b border-gray-200">
        <nav class="flex gap-6">
            <a
                href="{{ route('items.index', array_filter(['keyword' => $keyword ?: null])) }}"
                class="pb-3 text-sm font-medium border-b-2 {{ $tab === 'recommend' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
            >
                おすすめ
            </a>
            <a
                href="{{ route('items.index', array_filter(['tab' => 'mylist', 'keyword' => $keyword ?: null])) }}"
                class="pb-3 text-sm font-medium border-b-2 {{ $tab === 'mylist' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
            >
                マイリスト
            </a>
        </nav>
    </div>

    @if ($keyword !== '')
        <p class="mb-6 text-sm text-gray-600">
            「{{ $keyword }}」の検索結果: {{ $items->count() }}件
        </p>
    @endif

    @if ($items->isEmpty())
        <div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-600">
            @if ($tab === 'mylist')
                マイリストに表示できる商品がありません。
            @else
                表示できる商品がありません。
            @endif
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
            @foreach ($items as $item)
                @php
                    $primaryImage = $item->itemImages->first();
                    $isSold = $item->is_sold || $item->purchase_exists;
                @endphp
                <a href="{{ route('items.show', $item) }}" class="block bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition">
                    <div class="relative aspect-square bg-gray-100">
                        @if ($primaryImage)
                            <img
                                src="{{ asset('storage/'.$primaryImage->image_path) }}"
                                alt="{{ $item->name }}"
                                class="w-full h-full object-cover"
                            >
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">
                                No Image
                            </div>
                        @endif

                        @if ($isSold)
                            <span class="absolute top-2 left-2 bg-gray-800 text-white text-xs font-semibold px-2 py-1 rounded">
                                Sold
                            </span>
                        @endif
                    </div>

                    <div class="p-3">
                        <p class="text-sm text-gray-800 line-clamp-2">{{ $item->name }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-main-layout>
