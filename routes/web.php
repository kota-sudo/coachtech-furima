<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\ExhibitController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseAddressController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/item/{item}', [ItemController::class, 'show'])->name('items.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/sell', [ExhibitController::class, 'create'])->name('items.sell');
    Route::post('/sell', [ExhibitController::class, 'store'])->name('items.sell.store');

    Route::post('/item/{item}/like', [LikeController::class, 'toggle'])->name('items.like');
    Route::post('/item/{item}/comment', [CommentController::class, 'store'])->name('items.comment');

    Route::get('/purchase/address/{item}', [PurchaseAddressController::class, 'edit'])->name('purchases.address');
    Route::put('/purchase/address/{item}', [PurchaseAddressController::class, 'update'])->name('purchases.address.update');

    Route::get('/purchase/{item}', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchase/{item}', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/purchase/{item}/success', [PurchaseController::class, 'success'])->name('purchases.success');
    Route::get('/purchase/{item}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');

    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage.index');

    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('mypage.profile');
    Route::put('/mypage/profile', [ProfileController::class, 'update'])->name('mypage.profile.update');
});

require __DIR__.'/auth.php';
