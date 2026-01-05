<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/test-discount', function () {
    return 'Package loaded successfully!';
});