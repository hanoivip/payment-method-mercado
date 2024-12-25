<?php

namespace Hanoivip\PaymentMethodMercado;

use MercadoPago\Resources\Preference;
use Hanoivip\PaymentMethodContract\IPaymentSession;
use Hanoivip\Payment\Models\Transaction;

class MercadoSession implements IPaymentSession
{
    private $trans;
    
    private $config;
    
    private $preference;
    /**
     * @param Transaction $trans
     * @param Preference $preference
     */
    public function __construct($trans, $config, $preference)
    {
        $this->trans = $trans;
        $this->config = $config;
        $this->preference = $preference;
    }
    
    public function getSecureData()
    {
        return [];
    }

    public function getGuide()
    {
        return __('hanoivip.mercado::mercado.guide');
    }

    public function getTransId()
    {
        return $this->trans->trans_id;
    }

    public function getData()
    {
        $isTest = $this->config['is_test'];
        if ($isTest) {
            return ['checkoutUrl' => $this->preference->sandbox_init_point];
        } else {
            return ['checkoutUrl' => $this->preference->init_point];
        }
    }

}