<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiscountTestController;

// Home page → Discount Test Dashboard
Route::get('/', [DiscountTestController::class, 'index'])->name('discounts.test');

// Optional: Keep a quick test route
Route::get('/test-discount', function () {
    return 'Laravel User Discounts Package loaded successfully!';
});

Route::middleware(['web'])->group(function () {
    Route::post('/discounts-test/create', [DiscountTestController::class, 'createDiscount'])->name('discounts.test.create');
    Route::post('/discounts-test/assign/{discount}', [DiscountTestController::class, 'assign'])->name('discounts.test.assign');
    Route::post('/discounts-test/revoke/{discount}', [DiscountTestController::class, 'revoke'])->name('discounts.test.revoke');
    Route::post('/discounts-test/apply', [DiscountTestController::class, 'apply'])->name('discounts.test.apply');
});