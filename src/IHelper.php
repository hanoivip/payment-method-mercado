<?php

namespace Hanoivip\PaymentMethodMercado;

interface IHelper {
    public function config($cfg);
    
    public function listMethods();
    
    public function query($paymentId);
    
    public function payment($method, $item, $price);
}