<?php

namespace Hanoivip\PaymentMethodMercado;

use Hanoivip\PaymentMethodContract\IPaymentMethod;
use Hanoivip\Shop\Facades\OrderFacade;
use Illuminate\Support\Facades\Log;
use Exception;

class MercadoMethod implements IPaymentMethod
{
    private $config;
    
    private $helper;
    
    const STATUS_INIT = 0;
    const STATUS_PENDING = 1;
    const STATUS_CANCEL = 2;
    const STATUS_SUCCESS = 3;
    const STATUS_FAILURE = 4;
    
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
        $exists = MercadoTransaction::where('trans', $trans->trans_id)->get();
        if ($exists->isNotEmpty())
            throw new Exception('Mercado transaction already exists');
        $log = new MercadoTransaction();
        $log->trans = $trans->trans_id;
        $log->state = self::STATUS_INIT;
        // init...
        $order = $trans->order;
        $orderDetail = OrderFacade::detail($order);
        $pref = $this->helper->payment($orderDetail);
        $log->state = self::STATUS_PENDING;
        $log->pref_id = $pref->id;
        $log->save();
        return new MercadoSession($trans, $this->config, $pref);
    }

    public function request($trans, $params)
    {
        return $this->query($trans);
    }
    
    /**
     * Still no way to actively query??
     * {@inheritDoc}
     * @see \Hanoivip\PaymentMethodContract\IPaymentMethod::query()
     */
    public function query($trans, $force = false)
    {
        $log = MercadoTransaction::where('trans', $trans->trans_id)->first();
        if (empty($log))
        {
            return new MercadoFailure($trans, __('hanoivip.mercado::mercado.error'));
        }
        try
        {
            if ($log->state == self::STATUS_PENDING) {
                return new MercadoPending($log);
            }
            else {
                return new MercadoResult($log);
            }
        } catch (Exception $ex)
        {
            Log::error('Mercado query payment detail exception:' . $ex->getMessage());
            return new MercadoFailure($trans, __('hanoivip.mercado::mercado.error'));
        }
    }

    public function config($cfg)
    {
        $this->config = $cfg;
        $this->helper->config($cfg);
    }

    public function validate($params)
    {
        return [];
    }
    
    public function openPaymentPage($transId, $guide, $session)
    {
        return view('hanoivip.mercado::payment-page', ['trans' => $transId, 'guide' => $guide, 'data' => $session]);
    }

    public function openPendingPage($trans)
    {
        return view('hanoivip.mercado::pending-page', ['trans' => $trans]);
    }


    
}