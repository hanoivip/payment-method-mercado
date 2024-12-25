<?php

namespace Hanoivip\PaymentMethodMercado;

use Hanoivip\PaymentMethodContract\IPaymentResult;

class MercadoResult implements IPaymentResult
{
	private $config;
    /**
     * 
     * @var MercadoTransaction
     */
    private $record;
    
    public function __construct($record, $config)
    {
        $this->record = $record;
		$this->config = $config;
    }
    public function getCurrency()
    {
        return 'BRL';
    }

    public function getDetail()
    {
		$isTest = $this->config['is_test'];
		if ($isTest) {
		    return ['checkoutUrl' =>  'https://mercadopago.com.br/checkout/v1/redirect?pref_id=' . $this->record->pref_id];
		}
		else {
		    return ['checkoutUrl' =>  'https://sandbox.mercadopago.com.br/checkout/v1/redirect?pref_id=' . $this->record->pref_id];
		}
	}

    public function toArray()
    {
        $arr = [];
        $arr['detail'] = $this->getDetail();
        $arr['amount'] = $this->getAmount();
        $arr['isPending'] = $this->isPending();
        $arr['isFailure'] = $this->isFailure();
        $arr['isSuccess'] = $this->isSuccess();
        $arr['trans'] = $this->getTransId();
        return $arr;
    }

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