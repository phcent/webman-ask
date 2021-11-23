<?php
/**
 *-------------------------------------------------------------------------p*
 *
 *-------------------------------------------------------------------------h*
 * @copyright  Copyright (c) 2015-2021 Phcent Inc. (http://www.phcent.com)
 *-------------------------------------------------------------------------c*
 * @license    http://www.phcent.com        p h c e n t . c o m
 *-------------------------------------------------------------------------e*
 * @link       http://www.phcent.com
 *-------------------------------------------------------------------------n*
 * @since      象讯·PHP知识付费问答系统
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Controllers\Web\V1;


use Phcent\WebmanAsk\Service\OrdersService;
use support\Log;
use support\Request;
use Yansongda\Pay\Pay;

class AlipayController
{
    /**
     * 接收回调
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function return(Request $request)
    {
        try {
            Pay::config(config('phcentask.pay'));
            $result = Pay::alipay()->callback($request->all());
            OrdersService::payOrdersUpdate(['out_trade_no'=>$result->out_trade_no,'trade_no'=>$result->trade_no,'timestamp'=>$result->timestamp,'amount'=>$result->total_amount,'payment_code'=>'alipay']);
            Pay::alipay()->success();
            return phcentSuccess();
        }catch (\Exception $e){
            Log::info($e->getMessage());
        }
    }

    /**
     * 接收回调
     * @param Request $request
     * @throws \Throwable
     */
    public function notify(Request $request)
    {
        try {
            Pay::config(config('phcentask.pay'));
            $result = Pay::alipay()->callback($request->all());
            OrdersService::payOrdersUpdate(['out_trade_no'=>$result->out_trade_no,'trade_no'=>$result->trade_no,'timestamp'=>$result->timestamp,'amount'=>$result->total_amount,'payment_code'=>'alipay']);
            Pay::alipay()->success();
        }catch (\Exception $e){
            Log::info($e->getMessage());
        }
    }
}