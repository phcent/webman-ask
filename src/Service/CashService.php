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
 * @since      象讯·PHP 知识付费问答系统
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Service;


use Illuminate\Support\Facades\Date;
use Phcent\WebmanAsk\Model\UserCashLog;
use Phcent\WebmanAsk\Model\User;

class CashService
{

    /**
     * 申请提现
     * @param $params
     * @param $userId
     * @throws \Exception
     */
    public static function applyCash($params,$user)
    {
        $minCash = config('phcentask.minCash',50);
        if($params['amount'] < $minCash){
            throw new \Exception('提现金额不得少于$minCash');
        }
        $cashCommission = config('phcentask.cashCommission',0);
        $amount = $params['amount'];
        if(!empty($cashCommission)){
            $amount = bcdiv(bcmul($params['amount'],$cashCommission,2),100,2);
        }
        if($params['amount'] > $user->available_balance){
            throw new \Exception('可用余额不足');
        }
       $cash =  UserCashLog::create([
            'amount' => $params['amount'],
            'cash_sn' => self::getCashSn($user->id),
            'user_id' => $user->id,
            'real_amount' => $amount,
            'bank_name' => $params['bank_name'],
            'bank_user' => $params['bank_user'],
            'bank_account' => $params['bank_account']
        ]);
        BalanceService::cashApply($cash,$user);
    }

    /**
     * 生成提现编号
     * @param $userId
     * @return string
     */
    public static function getCashSn($userId)
    {
        return date('ymdHis').mt_rand(1000,9999).substr(100+$userId,-3);
    }

    /**
     * 管理员同意提现
     * @param $cashInfo
     * @param $adminId
     * @throws \Exception
     */
    public static function adminAgreeCash($cashInfo,$adminId)
    {
        $cashInfo->status = 1;
        $cashInfo->admin_id = $adminId;
        $cashInfo->pay_time = Date::now();
        $cashInfo->save();
        BalanceService::agreeCash($cashInfo);
    }

    /**
     * 管理员拒绝提现
     * @param $cashInfo
     * @param $adminId
     * @param $refuseReason
     * @throws \Exception
     */
    public static function adminRefuseCash($cashInfo,$adminId,$refuseReason)
    {
        $cashInfo->status = 2;
        $cashInfo->admin_id = $adminId;
        $cashInfo->refuse_reason = $refuseReason;
        $cashInfo->save();
        BalanceService::refuseCash($cashInfo);
    }
}