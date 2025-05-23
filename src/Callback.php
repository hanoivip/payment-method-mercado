<?php

namespace Hanoivip\PaymentMethodMercado;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller as BaseController;
use Exception;
use Hanoivip\PaymentContract\Facades\PaymentFacade;
use Hanoivip\Payment\Facades\BalanceFacade;
use Hanoivip\Shop\Facades\OrderFacade;
use Hanoivip\Events\Payment\TransactionUpdated;

class Callback extends BaseController
{   
    private $helper;
    
    public function __construct(IHelper $helper) {
        $this->helper = $helper;
    }
    
    ///success?collection_id=1329076091,1329078013&collection_status=approved,approved&payment_id=1329076091,1329078013&status=approved,approved&external_reference=1234567890&payment_type=credit_card&merchant_order_id=26425376449&preference_id=574432392-6e28d36e-0b4c-41a3-a084-529893f818d3&site_id=MLB&processing_mode=aggregator&merchant_account_id=null
    public function success(Request $request, $pid)
    {
        Log::error('Mercado got success redirect ' . print_r($request->all(), true));
        $config = PaymentFacade::getConfig($pid);
        if (empty($config))
        {
            Log::error("Mercado instance error");
            return view('hanoivip.mercado::failure-page');
        }
        $this->helper->config($config);
        
        $collection = explode(',', $request->input('collection_id'));
        $collectionStatus = explode(',', $request->input('collection_status'));
        $payment = explode(',', $request->input('payment_id'));
        $status = explode(',', $request->input('status'));
        $exPreference = $request->input('external_reference');// order number
        $preference = $request->input('preference_id');
        
        $total = 0;
        $net = 0;
        foreach ($payment as $pid) {
            // validate payment status
            $paymentObj = $this->helper->query($pid);
            if ($paymentObj == null || $paymentObj->status !== 'approved') {
                Log::error("Mercado validation failed");
                return view('hanoivip.mercado::failure-page');
            }
            $total += $paymentObj->transaction_details->total_paid_amount;
            $net += $paymentObj->transaction_details->net_received_amount;
        }
        
        $log = MercadoTransaction::where('pref_id', $preference)->first();
        if (!empty($log)) {
            // validate status
            if ($log->state == MercadoMethod::STATUS_SUCCESS) {
                Log::error("Mercado transaction was done. No need to repeat: $preference");
                return view('hanoivip.mercado::failure-page');
            }
            // validate paid amount
            $orderDetail = OrderFacade::detail($exPreference);//$log->transaction->order);
            if (empty($orderDetail)) {
                Log::error("Mercado order not found");
                return view('hanoivip.mercado::failure-page');
            }
            $price = $orderDetail->price;
            if ($orderDetail->currency !== 'BRL') {
                $price = BalanceFacade::convert($price, $orderDetail->currency, 'BRL');
            }
            if ($total < $price) {
                Log::error("Mercado paid amount not enough $total $price BRL");
                return view('hanoivip.mercado::failure-page');
            }
            // ok
            $log->state = MercadoMethod::STATUS_SUCCESS;
            $log->payment_id = $payment;
            $log->total_paid_amount = $total;
            $log->net_received_amount = $net;
            $log->save();
            
            event(new TransactionUpdated($log->trans));
        }
        else {
            Log::error("Mercado preference not found? " . $preference);
        }
        return view('hanoivip.mercado::success-page');
    }
    
    public function success_single_payment(Request $request, $pid)
    {
        Log::error('Mercado got success redirect ' . print_r($request->all(), true));
        $config = PaymentFacade::getConfig($pid);
        if (empty($config))
        {
            Log::error("Mercado instance error");
            return view('hanoivip.mercado::failure-page');
        }
        $this->helper->config($config);
        
        $collection = explode(',', $request->input('collection_id'));
        $collectionStatus = explode(',', $request->input('collection_status'));
        $payment = explode(',', $request->input('payment_id'));
        $status = explode(',', $request->input('status'));
        $exPreference = $request->input('external_reference');// order number
        $preference = $request->input('preference_id');
        
        // validate submitted data
        if (count($collection) != 1 || count($collectionStatus) != 1 || count($payment) != 1 || count($status) != 1) {
            Log::error("Mercado multiple payment is not supported");
            return view('hanoivip.mercado::failure-page');
        }
        if ($status[0] != 'approved') {
            Log::error("Mercado callback not approved status");
            return view('hanoivip.mercado::failure-page');
        }
        
        // validate data
        $paymentObj = $this->helper->query($payment[0]);
        if ($paymentObj == null || $paymentObj->status !== 'approved') {
            Log::error("Mercado validation failed");
            return view('hanoivip.mercado::failure-page');
        }
        
        
        $log = MercadoTransaction::where('pref_id', $preference)->first();
        if (!empty($log)) {
            $log->state = MercadoMethod::STATUS_SUCCESS;
            $log->payment_id = $payment[0];
            $log->total_paid_amount = $paymentObj->transaction_details->total_paid_amount;
            $log->net_received_amount = $paymentObj->transaction_details->net_received_amount;
            $log->save();
            
            event(new TransactionUpdated($log->trans));
        }
        else {
            Log::error("Mercado preference not found? " . $preference);
        }
        return view('hanoivip.mercado::success-page');
    }
    
    public function failure(Request $request, $pid)
    {
        Log::error('Mercado got failure redirect ' . print_r($request->all(), true));
        $preference = $request->input('preference_id');
        $log = MercadoTransaction::where('pref_id', $preference)->first();
        if (!empty($log)) {
            $log->state = MercadoMethod::STATUS_FAILURE;
            $log->save();
            
            event(new TransactionUpdated($log->trans));
        }
        else {
            Log::error("Mercado preference not found? " . $preference);
        }
        return view('hanoivip.mercado::failure-page');
    }
    
    public function pending(Request $request, $pid)
    {
        Log::error('Mercado got pending redirect ' . print_r($request->all(), true));
        $preference = $request->input('preference_id');
        $log = MercadoTransaction::where('pref_id', $preference)->first();
        if (!empty($log)) {
            $log->state = MercadoMethod::STATUS_PENDING;
            $log->save();
        }
        else {
            Log::error("Mercado preference not found? " . $preference);
        }
        return view('hanoivip.mercado::pending-page');
    }
    
}