<?php

namespace Hanoivip\PaymentMethodMercado;

use Hanoivip\IapContract\Facades\IapFacade;
use Hanoivip\PaymentMethodContract\IPaymentMethod;
use Illuminate\Support\Facades\Log;
use Exception;

class MercadoMethod implements IPaymentMethod
{
    private $config;
    
    private $helper;
    
    public function __construct(IHelper $helper)
    {
        $this->helper = $helper;
    }
    
    public function endTrans($trans)
    {}

    public function cancel($trans)
    {}

    public function beginTrans($trans)
    {
        $methods = $this->helper->listMethods();
        // load player cards?
        $cards = [];
        $log = new MercadoTransaction();
        $log->trans = $trans->trans_id;
        $log->save();
        session(['methods' => $methods]);
        return new MercadoSession($trans, $methods, $cards);
    }

    public function request($trans, $params)
    {
        $methods = session('methods');
        if (empty($methods))
        {
            return new MercadoFailure($trans, __('hanoivip.mercado::mercado.timeout'));
        }
        $log = MercadoTransaction::where('trans', $trans->trans_id)->first();
        if (empty($log))
        {
            return new MercadoFailure($trans, __('hanoivip.mercado::mercado.error'));
        }
        $method = $params['method'];
        try 
        {
            $order = $trans->order;
            $orderDetail = IapFacade::detail($order);
            $payment = $this->helper->payment($method, $orderDetail['item'], $orderDetail['item_price']);
            return new MercadoResult($payment);
        } 
        catch (Exception $ex) 
        {
            Log::error('Mercado request payment exception: ' . $ex->getMessage());
            return new MercadoFailure($trans, __('hanoivip.mercado::mercado.retry'));
        }
    }

    public function query($trans, $force = false)
    {
        $log = MercadoTransaction::where('trans', $trans->trans_id)->first();
        if (empty($log))
        {
            return new MercadoFailure($trans, __('hanoivip.mercado::mercado.error'));
        }
        try 
        {
            $payment = $this->helper->query($log->payment_id);
            return new MercadoResult($payment);
        } catch (Exception $ex) 
        {
            Log::error('Mercado query payment detail exception:' . $ex->getMessage());
            return new MercadoFailure($trans, __('hanoivip.mercado::mercado.retry'));
        }
    }

    public function config($cfg)
    {
        $this->config = $cfg;
        $this->helper->config($cfg);
    }

    public function validate($params)
    {
        $errors = [];
        if (!isset($params['method']))
        {
            $errors['method'] = "You must choose one method to pay";    
        }
        return $errors;
    }

    
}