<?php

use Illuminate\Support\Facades\Route;
use Ajustatech\PrintService\Controllers\CouponController;

// Route::get('/teste', function(){

// });
Route::get('/teste', [CouponController::class, 'showCoupon']);

