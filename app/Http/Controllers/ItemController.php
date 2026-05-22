<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->string('keyword')->trim()->toString();
        $tab = $request->string('tab')->toString();
        $isMylist = $tab === 'mylist';

        if ($isMylist && ! auth()->check()) {
            $items = collect();
        } else {
            $query = Item::query()
                ->with(['itemImages' => fn ($query) => $query->orderBy('sort_order')])
                ->withExists('purchase');

            if ($isMylist) {
                $query->whereHas('likes', fn ($query) => $query->where('user_id', auth()->id()));
            } else {
                $query->when(auth()->check(), fn ($query) => $query->where('user_id', '!=', auth()->id()));
            }

            $query->when($keyword !== '', fn ($query) => $query->where('name', 'like', '%'.$keyword.'%'));

            $items = $query->orderBy('id')->get();
        }

        return view('items.index', [
            'items' => $items,
            'keyword' => $keyword,
            'tab' => $isMylist ? 'mylist' : 'recommend',
        ]);
    }

    public function show(Item $item): View
    {
        $item->load([
            'itemImages' => fn ($query) => $query->orderBy('sort_order'),
            'categories',
            'condition',
            'comments.user',
        ])->loadCount(['likes', 'comments']);

        $isLiked = auth()->check()
            && $item->likes()->where('user_id', auth()->id())->exists();

        return view('items.show', [
            'item' => $item,
            'isLiked' => $isLiked,
        ]);
    }
}
