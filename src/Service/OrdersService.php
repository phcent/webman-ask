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


namespace Phcent\WebmanAsk\Service;


use Carbon\Traits\Date;
use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Logic\PayReturnLogic;
use Phcent\WebmanAsk\Model\SysOrders;
use Phcent\WebmanAsk\Model\SysRechargeLog;
use support\Db;
use support\Redis;
use Yansongda\Pay\Pay;

class OrdersService
{
    public function adminPay()
    {
        
    }

    /**
     * 创建充值
     * @param $params
     * @return
     * @throws \Throwable
     */
    public static function createRecharge($params)
    {
        try {
            $userId = AuthLogic::getInstance()->userId();
            if(empty($userId)){
                throw new \Exception('未登入');
            }
            $giveAmount = 0; //赠送金额
            $rechargeRule = config('phcentask.rechargeRule',[]);
            if(!empty($rechargeRule)){
                $rechargeRule = collect($rechargeRule)->sortDesc();
                foreach ($rechargeRule as $key=>$val){
                    if($params['amount'] >= $key){
                        $giveAmount = $val;
                        break;
                    }
                }
            }
            Db::connection()->beginTransaction();
            $recharge = SysRechargeLog::create([
                'amount' => $params['amount'],
                'give_amount' => $giveAmount,
                'user_id' => $userId
            ]);
            $order = SysOrders::create([
                'theme_id' => $recharge->id,
                'operation_stage' => 'recharge',
                'user_id' => $userId,
                'order_sn' => self::getOrderSn($userId),
                'amount' => $params['amount']
            ]);
            $recharge->orders_id = $order->id;
            $recharge->save();
            Db::connection()->commit();
            return $order;
        }catch (\Exception $e){
            Db::connection()->rollBack();
            throw new \Exception($e->getMessage());
        }
    }
    /**
     * 生成提现编号
     * @param $userId
     * @return string
     */
    public static function getOrderSn($userId)
    {
        return date('ymdHis').mt_rand(1000,9999).substr(100+$userId,-3);
    }

    /**
     * 订单提交支付
     * @param $params
     * @param $id
     * @return \Psr\Http\Message\ResponseInterface|\Yansongda\Supports\Collection
     * @throws \Exception
     */
    public static function payOrders($params,$id)
    {
        try {
            $userId = AuthLogic::getInstance()->userId();
            if (empty($userId)) {
                throw new \Exception('未登入');
            }
            $orderInfo = SysOrders::where('user_id',$userId)->where('id',$id)->first();
            if($orderInfo->status == 1){
                throw new \Exception('订单已支付');
            }
            $orderInfo->payment_code = $params['payment_code'];
            $orderInfo->payment_client_type = $params['client_type'];
            $orderInfo->save();

            $payInfo = self::getPay($orderInfo);
            if($payInfo == null){
                throw new \Exception('支付参数配置异常');
            }
            return $payInfo;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 获取支付端口
     * @param $clientType
     * @return string
     */
    public static function getClientType($clientType)
    {
        if(in_array($clientType,['app','android','ios'])){
            return 'app';
        }else if(in_array($clientType,['wap','h5'])){
            return 'wap';
        }else if(in_array($clientType,['wap','h5'])){
            return 'applet';
        }else{
            return 'web';
        }
    }

    /**
     * @param $orderInfo
     * @return mixed|\Psr\Http\Message\ResponseInterface|string|\Yansongda\Supports\Collection|null
     * @throws \Exception
     */
    public static function getPay($orderInfo)
    {
        try {
            Pay::config(config('phcentask.pay'));
            $info = null;
            switch ($orderInfo->payment_code){
                case 'alipay':
                    if(self::getClientType($orderInfo->payment_client_type) =='app'){
                        $info = Pay::alipay()->app([
                            'out_trade_no' => $orderInfo->order_sn,
                            'total_amount' => $orderInfo->amount,
                            'subject' => '订单--'.$orderInfo->order_sn,
                        ]);
                    }else if(self::getClientType($orderInfo->payment_client_type) =='wap') {
                        $res = Pay::alipay()->wap([
                            'out_trade_no' => $orderInfo->order_sn,
                            'total_amount' => $orderInfo->amount,
                            'subject' => '订单--' . $orderInfo->order_sn,
                            '_method' => 'get'
                        ]);
                        $info = $res->getHeaders()['Location'][0];
                    }else if(self::getClientType($orderInfo->payment_client_type) =='applet') {
                        $info = Pay::alipay()->mini([
                            'out_trade_no' => $orderInfo->order_sn,
                            'total_amount' => $orderInfo->amount,
                            'subject' => '订单--' . $orderInfo->order_sn,
                        ]);
                    }else{
                        $res = Pay::alipay()->web([
                            'out_trade_no' => $orderInfo->order_sn,
                            'total_amount' => $orderInfo->amount,
                            'subject' => '订单--'.$orderInfo->order_sn,
                            '_method' => 'get'
                        ]);
                        $info = $res->getHeaders()['Location'][0];
                    }
                    break;
                case 'wxpay':
                    if(self::getClientType($orderInfo->payment_client_type) =='app'){
                        $info = Pay::wechat()->app([
                            'out_trade_no' => $orderInfo->order_sn,
                            'amount' => [
                                'total' => $orderInfo->amount,
                            ],
                            'description' => '订单--'.$orderInfo->order_sn,
                        ]);
                    }else if(self::getClientType($orderInfo->payment_client_type) =='wap') {
                        $info = Pay::alipay()->wap([
                            'out_trade_no' => $orderInfo->order_sn,
                            'description' => '订单--' . $orderInfo->order_sn,
                            'amount' => [
                                'total' => $orderInfo->amount,
                            ],
                            'scene_info' => [
                                'payer_client_ip' => '1.2.4.8',
                                'h5_info' => [
                                    'type' => 'Wap',
                                ]
                            ],
                        ]);
                    }else if(self::getClientType($orderInfo->payment_client_type) =='applet') {
                        $info = Pay::alipay()->mini([
                            'out_trade_no' => $orderInfo->order_sn,
                            'amount' => [
                                'total' => $orderInfo->amount,
                            ],
                            'description' => '订单--'.$orderInfo->order_sn,
                        ]);
                    }else{
                        $res = Pay::wechat()->scan([
                            'out_trade_no' => $orderInfo->order_sn,
                            'amount' => [
                                'total' => $orderInfo->amount,
                            ],
                            'description' => '订单--'.$orderInfo->order_sn,
                        ]);
                        $info = $res->code_url;
                    }
                    break;
            }
            return $info;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 更新订单相关信息
     * @param $result
     * @return string
     * @throws \Throwable
     */
    public static function payOrdersUpdate($result)
    {
        try {
            $orders = SysOrders::where('order_sn',$result['out_trade_no'])->first();
            if($orders == null){
                throw new \Exception('订单不存在');
            }
            if($orders->status == 1){
                throw new \Exception('订单已经支付过了');
            }
            if($orders->amount != $result['amount']){
                throw new \Exception('订单金额异常');
            }

            $orders->trade_sn = $result['trade_no'];
            $orders->pay_time = $result['timestamp'];
            $orders->payment_code = $result['payment_code'];
            $orders->status = 1;
            $orders->save();
            //存储订单状态
            Redis::setEx("phcentOrderStatus{$orders->id}",3600,1);
            //订单后续处理
            PayReturnLogic::{$orders->operation_stage}($orders->theme_id,$orders->id);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
}