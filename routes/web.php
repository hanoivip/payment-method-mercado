<?php

use Illuminate\Support\Facades\Route;

Route::middleware([
    'web',
    'admin'
])->namespace('Hanoivip\PaymentMethodTsr')
->prefix('ecmin')
->group(function () {
    // Module index
    Route::get('/tsr', 'Admin@index')->name('ecmin.tsr');
    
});