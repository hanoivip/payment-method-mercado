<?php

use Illuminate\Support\Facades\Route;

Route::any('/tsr/callback', 'Hanoivip\PaymentMethodTsr\TsrController@callback');