<?php

namespace Hanoivip\PaymentMethodMercado;

use Hanoivip\Payment\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

class MercadoTransaction extends Model
{
    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'trans_id', 'trans');
    }
}