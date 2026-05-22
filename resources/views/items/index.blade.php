<x-main-layout>
    @if ($keyword !== '')
        <p class="mb-6 text-sm text-gray-600">
            「{{ $keyword }}」の検索結果: {{ $items->count() }}件
        </p>
    @endif

    @if ($items->isEmpty())
        <div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-600">
            表示できる商品がありません。
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
            @foreach ($items as $item)
                @php
                    $primaryImage = $item->itemImages->first();
                    $isSold = $item->is_sold || $item->purchase_exists;
                @endphp
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
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
                </div>
            @endforeach
        </div>
    @endif
</x-main-layout>
