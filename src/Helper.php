<?php

namespace Hanoivip\PaymentMethodMercado;

use Illuminate\Support\Facades\Log;
use Exception;
use Hanoivip\Payment\Facades\BalanceFacade;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;

/**
 * Checkout pro helper
 * @author GameOH
 *
 */
class Helper implements IHelper
{
    private $cfg;

    public function config($cfg)
    {
        $this->cfg = $cfg;
        $isTest = $cfg['is_test'];
        MercadoPagoConfig::setAccessToken($cfg['access_token']);
        MercadoPagoConfig::setRuntimeEnviroment($isTest ? MercadoPagoConfig::LOCAL : MercadoPagoConfig::SERVER);
    }
    
    public function query($paymentId)
    {
        $client = new PaymentClient();
        return $client->get($paymentId);
    }

    // TODO: need a way to get CartVO
    public function payment($orderDetail)
    {
        $amount = $orderDetail->price;
        if (!empty($orderDetail->currency) && $orderDetail->currency !== 'BRL') {
            $amount = BalanceFacade::convert($amount, $orderDetail->currency, 'BRL');
        }
        $product = array(
            "id" => $orderDetail->serial,
            "title" => "Order Number: " . $orderDetail->serial,
            "description" => "Order " . $orderDetail->serial,
            "currency_id" => "BRL",
            "quantity" => 1,
            "unit_price" => $amount,
        );
        $payer = array(
            "name" => config('mercado.proxy_name'),
            "surname" => config('mercado.proxy_name'),
            "email" => config('mercado.proxy_email'),
        );
        $request = $this->createPreferenceRequest($orderDetail->serial, array($product), $payer);
        
        $client = new PreferenceClient();
        return $client->create($request);
    }
    
    private function createPreferenceRequest($serial, $items, $payer): array
    {
        $paymentMethods = [
            "excluded_payment_methods" => [],
            "installments" => 12,
            "default_installments" => 1
        ];
        
        $backUrls = array(
            'success' => route('mercado.success', ['pid' => $this->cfg['id']]),
            'failure' => route('mercado.failure', ['pid' => $this->cfg['id']]),
        );
        
        $request = [
            "items" => $items,
            "payer" => $payer,
            "payment_methods" => $paymentMethods,
            "back_urls" => $backUrls,
            "statement_descriptor" => "Extreme Game Studio",
            "external_reference" => $serial,
            "expires" => false,
            "auto_return" => 'approved',
        ];
        
        return $request;
    }

    
}