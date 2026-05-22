<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Like;
use Illuminate\Http\RedirectResponse;

class LikeController extends Controller
{
    public function toggle(Item $item): RedirectResponse
    {
        $userId = auth()->id();

        $like = Like::query()
            ->where('user_id', $userId)
            ->where('item_id', $item->id)
            ->first();

        if ($like) {
            $like->delete();
        } else {
            Like::create([
                'user_id' => $userId,
                'item_id' => $item->id,
            ]);
        }

        return redirect()->route('items.show', $item);
    }
}
