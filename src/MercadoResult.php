<?php

namespace Hanoivip\PaymentMethodMercado;

use Hanoivip\PaymentMethodContract\IPaymentResult;

class MercadoResult implements IPaymentResult
{
    /**
     * 
     * @var MercadoTransaction
     */
    private $record;
    
    public function __construct($record)
    {
        $this->record = $record;
    }
    public function getCurrency()
    {
        return 'BRL';
    }

    public function getDetail()
    {}

    public function toArray()
    {}

    public function isPending()
    {
        return false;
    }

    public function isFailure()
    {
        return !$this->isSuccess();
    }

    public function getTransId()
    {
        return $this->record->trans;
    }

    public function isSuccess()
    {
        return $this->record->state == MercadoMethod::STATUS_SUCCESS;
    }

    public function getAmount()
    {
        if ($this->isSuccess()) {
            return $this->record->total_paid_amount;
        }
        return 0;
    }

}