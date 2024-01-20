<?php

namespace Hanoivip\PaymentMethodMercado;

use Illuminate\Support\Facades\Log;
use MercadoPago\Payer;
use MercadoPago\Payment;
use MercadoPago\SDK;
use Mervick\CurlHelper;
use Exception;
/**
 * Non-cached helper
 * @author GameOH
 *
 */
class Helper implements IHelper
{
    private $cfg;
    
    private function getAccessToken()
    {
        $params = [
            'client_secret' => $this->cfg['client_secret'],
            'client_id' => $this->cfg['client_id'],
            'grant_type' => 'authorization_code',
            'code' => 'TG-abc3c6d371ac53ceece434f33d5c3b93-574432392',//user application id - user id
            'redirect_uri' => 'http://bomarm.test/',
        ];
        $url = sprintf('%s/oauth/token', $this->cfg['endpoint']);
        $response = CurlHelper::factory($url)
        ->setHeaders(['Authorization', 'Bearer ' . $this->cfg['public_key']])
        ->setPostParams($params)
        ->exec();
        print_r($response);
        return $response['data']['access_token'];
    }
    
    public function query($paymentId)
    {
        $payment = Payment::find_by_id($paymentId);
        Log::debug(print_r($payment, true));
        return $payment;
    }

    public function listMethods()
    {
        /*$token = $this->getAccessToken();
        if (empty($token))
        {
            throw new Exception('Mercado token empty. Check client id & scret.');
        }*/
        print_r($this->cfg);
        $url = sprintf('%s/v1/payment_methods', $this->cfg['endpoint']);
        $response = CurlHelper::factory($url)
        ->setHeaders(['Authorization', 'Bearer ' . $this->cfg['access_token']])
        ->exec();
        $methods = [];
        print_r($response);
        if ($response['status'] == 200)
        {
            $methods = $response['data'];
        }
        //Log::debug(print_r($methods, true));
        return $methods;
    }

    public function payment($method, $item, $price)
    {
        $payment = new Payment();
        
        $payment->transaction_amount = $price;
        $payment->token = "CARD_TOKEN";
        $payment->description = $item;
        $payment->installments = 1;
        
        $payer = new Payer();
        $payer->email = "game.us.team@gmail.com";
        
        $payment->payer = $payer;
        $payment->save(); 
        Log::debug(print_r($payment, true));
        return $payment;
    }

    public function config($cfg)
    {
        $this->cfg = $cfg;
        SDK::setAccessToken($cfg['access_token']);
    }
    
}