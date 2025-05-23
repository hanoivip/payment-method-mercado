<?php

use Illuminate\Support\Facades\Route;

Route::middleware([
    'web'
])->namespace('Hanoivip\PaymentMethodMercado')
->prefix('mercado')
->group(function () {
    Route::get('/success/{pid}', 'Callback@success')->name('mercado.success');
    Route::get('/failure/{pid}', 'Callback@failure')->name('mercado.failure');
    Route::get('/pending/{pid}', 'Callback@pending')->name('mercado.pending');
});

Route::middleware([
    'web',
    'admin'
])->namespace('Hanoivip\PaymentMethodMercado')
->prefix('ecmin')
->group(function () {
    // Module index
    Route::get('/mercado', 'Admin@index')->name('ecmin.mercado');
    // Manual callback by admin
    Route::any('/mercado/callback', 'Admin@index')->name('ecmin.mercado.callback');
});