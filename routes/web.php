<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/item/{item}', [ItemController::class, 'show'])->name('items.show');

Route::middleware('auth')->group(function () {
    Route::post('/item/{item}/like', [LikeController::class, 'toggle'])->name('items.like');
    Route::post('/item/{item}/comment', [CommentController::class, 'store'])->name('items.comment');

    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('mypage.profile');
    Route::put('/mypage/profile', [ProfileController::class, 'update'])->name('mypage.profile.update');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

require __DIR__.'/auth.php';
