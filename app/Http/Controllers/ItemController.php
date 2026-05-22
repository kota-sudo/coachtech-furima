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

        $items = Item::query()
            ->with(['itemImages' => fn ($query) => $query->orderBy('sort_order')])
            ->withExists('purchase')
            ->when(auth()->check(), fn ($query) => $query->where('user_id', '!=', auth()->id()))
            ->when($keyword !== '', fn ($query) => $query->where('name', 'like', '%'.$keyword.'%'))
            ->orderBy('id')
            ->get();

        return view('items.index', [
            'items' => $items,
            'keyword' => $keyword,
        ]);
    }
}
