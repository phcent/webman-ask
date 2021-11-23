<?php
/**
 *-------------------------------------------------------------------------p*
 * 支付回调处理
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


namespace Phcent\WebmanAsk\Logic;


use Illuminate\Support\Facades\Date;
use Phcent\WebmanAsk\Model\AskOrders;
use Phcent\WebmanAsk\Model\AskThanks;
use Phcent\WebmanAsk\Model\SysRechargeLog;
use Phcent\WebmanAsk\Model\SysUser;
use Phcent\WebmanAsk\Service\BalanceService;

class PayReturnLogic
{
    /**
     * 充值订单支付完成处理
     * @param $id
     * @param $orderId
     * @throws \Exception
     */
    public static function recharge($id,$orderId)
    {
        $info = SysRechargeLog::where('id',$id)->first();
        if($info == null){
            throw new \Exception('数据不存在');
        }
        BalanceService::recharge($info);
        //发放资金成功
        $info->status = 1;
        $info->save();
    }

    /**
     * 感谢订单
     * @param $id
     * @param $orderId
     * @throws \Exception
     */
    public static function thank($id,$orderId)
    {
        $info = AskThanks::where('id',$id)->first();
        if($info == null){
            throw new \Exception('数据不存在');
        }
        $user = SysUser::where('id',$info->user_id)->first();
        if($user == null){
            throw new \Exception('会员异常');
        }
        BalanceService::thanksToUser($info,$user);
        $info->pay_time = Date::now();
        $info->status = 1;
        $info->save();
    }

    /**
     * 付费阅读订单
     * @param $id
     * @param $orderId
     * @throws \Exception
     */
    public static function paid($id,$orderId)
    {
        $info = AskOrders::where('id',$id)->first();
        if($info == null){
            throw new \Exception('数据不存在');
        }
        $user = SysUser::where('id',$info->user_id)->first();
        if($user == null){
            throw new \Exception('会员异常');
        }
        BalanceService::thanksToUser($info,$user);
        $info->pay_time = Date::now();
        $info->status = 1;
        $info->save();
    }
}