<x-main-layout>
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                <div class="shrink-0">
                    @if ($user->profile_image)
                        <img
                            src="{{ asset('storage/'.$user->profile_image) }}"
                            alt="{{ $user->name }}"
                            class="h-24 w-24 rounded-full object-cover border border-gray-200"
                        >
                    @else
                        <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-sm border border-gray-200">
                            No Image
                        </div>
                    @endif
                </div>

                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                    <a
                        href="{{ route('mypage.profile') }}"
                        class="mt-3 inline-block text-sm text-indigo-600 hover:text-indigo-800 underline"
                    >
                        プロフィールを編集
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-8 mb-6 border-b border-gray-200">
            <nav class="flex gap-6">
                <a
                    href="{{ route('mypage.index', ['page' => 'sell']) }}"
                    class="pb-3 text-sm font-medium border-b-2 {{ $tab === 'sell' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                >
                    出品した商品
                </a>
                <a
                    href="{{ route('mypage.index', ['page' => 'buy']) }}"
                    class="pb-3 text-sm font-medium border-b-2 {{ $tab === 'buy' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                >
                    購入した商品
                </a>
            </nav>
        </div>

        @if ($items->isEmpty())
            <div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-600">
                @if ($tab === 'buy')
                    購入した商品はありません。
                @else
                    出品した商品はありません。
                @endif
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
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
    </div>
</x-main-layout>
