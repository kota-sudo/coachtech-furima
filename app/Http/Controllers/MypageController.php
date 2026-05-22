<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MypageController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $page = $request->string('page')->toString();
        $tab = in_array($page, ['buy', 'sell'], true) ? $page : 'sell';

        $query = Item::query()
            ->with(['itemImages' => fn ($query) => $query->orderBy('sort_order')])
            ->withExists('purchase');

        if ($tab === 'buy') {
            $items = $query
                ->whereHas('purchase', fn ($query) => $query->where('user_id', $user->id))
                ->orderBy('id')
                ->get();
        } else {
            $items = $query
                ->where('user_id', $user->id)
                ->orderBy('id')
                ->get();
        }

        return view('mypage.index', [
            'user' => $user,
            'items' => $items,
            'tab' => $tab,
        ]);
    }
}
