<?php

namespace Hanoivip\PaymentMethodMercado;

use Hanoivip\PaymentMethodContract\IPaymentResult;

class MercadoPending implements IPaymentResult
{
    private $trans;
    /**
     * 
     * @param MercadoTransaction $trans
     */
    public function __construct($trans)
    {
        $this->trans = $trans;
    }
    
    public function getCurrency()
    {
        return 'BRL';
    }

    public function getDetail()
    {
    {
		$isTest = $this->config['is_test'];
		if ($isTest) {
		    return ['checkoutUrl' =>  'https://mercadopago.com.br/checkout/v1/redirect?pref_id=' . $this->trans->pref_id];
		}
		else {
		    return ['checkoutUrl' =>  'https://sandbox.mercadopago.com.br/checkout/v1/redirect?pref_id=' . $this->trans->pref_id];
		}
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
        $arr['currency'] = $this->getCurrency();
        return $arr;
    }

    public function isPending()
    {
        return true;
    }

    public function isFailure()
    {
        return false;
    }

    public function getTransId()
    {
        return $this->trans->trans_id;
    }

    public function isSuccess()
    {
        return false;
    }

    public function getAmount()
    {
        return 0;
    }

}