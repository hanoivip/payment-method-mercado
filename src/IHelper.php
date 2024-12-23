<?php

namespace Hanoivip\PaymentMethodMercado;

use Hanoivip\Shop\Models\ShopOrder;
use MercadoPago\Resources\Preference;
use MercadoPago\Resources\Payment;

interface IHelper {
    public function config($cfg);
    /**
     * Query payment record
     * @param Payment $paymentId
     */
    public function query($paymentId);
    /**
     * Trigger payment
     * - Checkout pro
     * @param ShopOrder $orderDetail
     * @return Preference
     */
    public function payment($orderDetail);
    
}