<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExhibitionRequest;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExhibitController extends Controller
{
    public function create(): View
    {
        return view('items.create', [
            'categories' => Category::orderBy('id')->get(),
            'conditions' => Condition::orderBy('id')->get(),
        ]);
    }

    public function store(ExhibitionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $item = DB::transaction(function () use ($request, $validated) {
            $item = Item::create([
                'user_id' => auth()->id(),
                'condition_id' => $validated['condition_id'],
                'name' => $validated['name'],
                'brand_name' => $validated['brand_name'] ?? null,
                'description' => $validated['description'],
                'price' => $validated['price'],
                'is_sold' => false,
            ]);

            $imagePath = $request->file('image')->store('items', 'public');

            ItemImage::create([
                'item_id' => $item->id,
                'image_path' => $imagePath,
                'sort_order' => 0,
            ]);

            $item->categories()->attach($validated['category_ids']);

            return $item;
        });

        return redirect()->route('items.show', $item);
    }
}
