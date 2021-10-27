<?php
/**
 *-------------------------------------------------------------------------p*
 * 资金变动流水
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


use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\SysBalanceLog;
use Phcent\WebmanAsk\Model\SysUser;

class BalanceService
{
    /**
     * 发布悬赏问题
     * @param $question
     * @param $user
     */
    public static function postReward($question,$user)
    {
        if($question->reward_balance > 0 && $question->reward_balance <= $user->available_balance){
            SysBalanceLog::create([
                'user_id' => $user->id,
                'user_name' => $user->nick_name,
                'available_balance' => -$question->reward_balance,
                'freeze_balance' => $question->reward_balance,
                'old_available_balance' => $user->available_balance,
                'old_freeze_balance' => $user->freeze_balance,
                'operation_stage' => 'postReward',
                'description' => '发布悬赏问题,编号：'.$question->id.'冻结资金：'.$question->reward_balance
            ]);
            //减少可用余额 增加冻结余额
            $user->increment('freeze_balance',$question->reward_balance); //增加
            $user->decrement('available_balance',$question->reward_balance);//减少
        }
    }

    /**
     * 申请提现
     * @param $cash
     * @param $user
     */
    public static function cashApply($cash,$user)
    {
        SysBalanceLog::create([
            'user_id' => $user->id,
            'user_name' => $user->nick_name,
            'available_balance' => -$cash->amount,
            'freeze_balance' => $cash->amount,
            'old_available_balance' => $user->available_balance,
            'old_freeze_balance' => $user->freeze_balance,
            'operation_stage' => 'applyCash',
            'description' => '申请提现,编号：'.$cash->cash_sn.'冻结资金：'.$cash->amount
        ]);
        //减少可用余额 增加冻结余额
        $user->increment('freeze_balance',$cash->amount); //增加
        $user->decrement('available_balance',$cash->amount);//减少
    }

    /**
     * 提现成功
     * @param $cash
     * @throws \Exception
     */
    public static function agreeCash($cash)
    {
        $user = SysUser::where('id',$cash->user_id)->first();
        if($user == null){
            throw new \Exception('会员信息异常');
        }
        SysBalanceLog::create([
            'user_id' => $user->id,
            'user_name' => $user->nick_name,
            'available_balance' => 0,
            'freeze_balance' => -$cash->amount,
            'old_available_balance' => $user->available_balance,
            'old_freeze_balance' => $user->freeze_balance,
            'operation_stage' => 'agreeCash',
            'description' => '提现成功,编号：'.$cash->cash_sn.'减少冻结资金：'.$cash->amount
        ]);
        //减少冻结余额
        $user->decrement('freeze_balance',$cash->amount); //减少
    }

    /***
     * 拒绝提现
     * @param $cash
     * @throws \Exception
     */
    public static function refuseCash($cash)
    {
        $user = SysUser::where('id',$cash->user_id)->first();
        if($user == null){
            throw new \Exception('会员信息异常');
        }
        SysBalanceLog::create([
            'user_id' => $user->id,
            'user_name' => $user->nick_name,
            'available_balance' => $cash->amount,
            'freeze_balance' => -$cash->amount,
            'old_available_balance' => $user->available_balance,
            'old_freeze_balance' => $user->freeze_balance,
            'operation_stage' => 'refuseCash',
            'description' => '拒绝提现,编号：'.$cash->cash_sn.'解冻资金：'.$cash->amount
        ]);
        //减少可用余额 增加冻结余额
        $user->decrement('freeze_balance',$cash->amount); //减少冻结
        $user->increment('available_balance',$cash->amount);//增加可用
    }

    /**
     * 采纳最佳答案
     * @param $question
     * @param $userId
     * @throws \Exception
     */
    public static function bestAnswer($question,$userId)
    {
        $user = SysUser::where('id',$question->user_id)->first();
        if($user == null){
            throw new \Exception('问题作者异常');
        }
        SysBalanceLog::create([
            'user_id' => $user->id,
            'user_name' => $user->nick_name,
            'available_balance' => 0,
            'freeze_balance' => -$question->reward_balance,
            'old_available_balance' => $user->available_balance,
            'old_freeze_balance' => $user->freeze_balance,
            'operation_stage' => 'bestAnswer',
            'description' => '悬赏问题,编号：'.$question->id.',采纳最佳答案,扣除冻结资金：'.$question->reward_balance
        ]);
        //减少冻结余额
        $user->decrement('freeze_balance',$question->reward_balance); //直接减少冻结资金

        $bestUser = SysUser::where('id',$userId)->first();
        if($bestUser != null){
            $commission = config('phcentask.balanceCommission',0);
            $reward_balance = $question->reward_balance;
            if($commission > 0){
                $reward_balance = bcdiv(bcmul($question->reward_balance,$commission,0),100,2);
            }
            if($reward_balance){
                SysBalanceLog::create([
                    'user_id' => $bestUser->id,
                    'user_name' => $bestUser->nick_name,
                    'available_balance' => $reward_balance,
                    'freeze_balance' => 0,
                    'old_available_balance' => $bestUser->available_balance,
                    'old_freeze_balance' => $bestUser->freeze_balance,
                    'operation_stage' => 'bestAnswer',
                    'description' => '悬赏问题,编号：'.$question->id.',采纳最佳答案获得分成：'.$reward_balance
                ]);
            }
        }

    }

    /**
     * 'increase','decrease','freeze','unfreeze'
     * 操作资金变化
     * @param $operation
     * @param $amount
     * @param $userId
     * @throws \Exception
     */
    public static function changeBalance($operation,$amount,$userId)
    {
        $adminId = AuthLogic::getInstance()->userId();
        if(empty($adminId)){
            throw new \Exception('数据异常');
        }
        $user = SysUser::where('id',$userId)->first();
        if($user == null){
            throw new \Exception('会员异常');
        }
        $available_balance = 0 ;
        $freeze_balance = 0;
        $operation_stage = '';
        $description = '';
        switch ($operation){
            case 'increase':
                $available_balance = $amount;
                $operation_stage = 'increaseBalance';
                $description = "管理员调整预存款，增加余额 {$amount}，操作者编号{$adminId}";
                $user->increment('available_balance',$amount);//增加可用
                break;
            case 'decrease':
                $operation_stage = 'decreaseBalance';
                $available_balance = -$amount;
                $description = "管理员调整预存款，减少余额 {$amount}，操作者编号{$adminId}";
                $user->decrement('available_balance',$amount);//减少可用
                break;
            case 'freeze':
                $operation_stage = 'freezeBalance';
                $available_balance = -$amount;
                $freeze_balance = $amount;
                $description = "管理员调整预存款，冻结余额 {$amount}，操作者编号{$adminId}";
                $user->increment('freeze_balance',$amount); //增加冻结
                $user->decrement('available_balance',$amount);//减少可用
                break;
            case 'unfreeze':
                $operation_stage = 'unfreezeBalance';
                $available_balance = $amount;
                $freeze_balance = -$amount;
                $description = "管理员调整预存款，解冻余额 {$amount}，操作者编号{$adminId}";
                $user->decrement('freeze_balance',$amount); //减少冻结
                $user->increment('available_balance',$amount);//增加可用
                break;
        }
        SysBalanceLog::create([
            'user_id' => $user->id,
            'user_name' => $user->nick_name,
            'available_balance' => $available_balance,
            'freeze_balance' => $freeze_balance,
            'old_available_balance' => $user->available_balance,
            'old_freeze_balance' => $user->freeze_balance,
            'operation_stage' => $operation_stage,
            'description' => $description
        ]);
    }

}