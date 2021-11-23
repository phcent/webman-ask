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
            $user->increment('freeze_balance',bcmul($question->reward_balance,100,0)); //增加
            $user->decrement('available_balance',bcmul($question->reward_balance,100,0));//减少
        }
    }

    /**
     * 追加悬赏
     * @param $amount
     * @param $id
     * @param $user
     */
    public static function appendReward($amount,$id,$user)
    {
        if($amount > 0 && $amount <= $user->available_balance){
            SysBalanceLog::create([
                'user_id' => $user->id,
                'user_name' => $user->nick_name,
                'available_balance' => -$amount,
                'freeze_balance' => $amount,
                'old_available_balance' => $user->available_balance,
                'old_freeze_balance' => $user->freeze_balance,
                'operation_stage' => 'appendReward',
                'description' => '问题追加悬赏,编号：'.$id.'冻结资金：'.$amount
            ]);
            //减少可用余额 增加冻结余额
            $user->increment('freeze_balance',bcmul($amount,100,0)); //增加
            $user->decrement('available_balance',bcmul($amount,100,0));//减少
        }
    }

    /**
     * 退回悬赏金额
     * @param $question
     */
    public static function backReward($question)
    {
        $user = SysUser::where('id',$question->user_id)->withTrashed()->first();

        SysBalanceLog::create([
            'user_id' => $user->id,
            'user_name' => $user->nick_name,
            'available_balance' => $question->reward_balance,
            'freeze_balance' => -$question->reward_balance,
            'old_available_balance' => $user->available_balance,
            'old_freeze_balance' => $user->freeze_balance,
            'operation_stage' => 'backReward',
            'description' => '关闭悬赏问题,编号：'.$question->id.'解冻资金：'.$question->reward_balance
        ]);
        //减少可用余额 增加冻结余额
        $user->decrement('freeze_balance',bcmul($question->reward_balance,100,0)); //减少
        $user->increment('available_balance',bcmul($question->reward_balance,100,0));//增加
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
        $user->increment('freeze_balance',bcmul($cash->amount,100,0)); //增加
        $user->decrement('available_balance',bcmul($cash->amount,100,0));//减少
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
        $user->decrement('freeze_balance',bcmul($cash->amount,100,0)); //减少
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
        $user->decrement('freeze_balance',bcmul($cash->amount,100,0)); //减少冻结
        $user->increment('available_balance',bcmul($cash->amount,100,0));//增加可用
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
        $user->decrement('freeze_balance',bcmul($question->reward_balance,100,0)); //直接减少冻结资金

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
        $old_available_balance = $user->available_balance;
        $old_freeze_balance = $user->freeze_balance;
        $available_balance = 0 ;
        $freeze_balance = 0;
        $operation_stage = '';
        $description = '';
        switch ($operation){
            case 'increase':
                $available_balance = $amount;
                $operation_stage = 'increaseBalance';
                $description = "管理员调整预存款，增加余额 {$amount}，操作者编号{$adminId}";
                $user->increment('available_balance',bcmul($amount,100,0));//增加可用
                break;
            case 'decrease':
                $operation_stage = 'decreaseBalance';
                $available_balance = -$amount;
                $description = "管理员调整预存款，减少余额 {$amount}，操作者编号{$adminId}";
                $user->decrement('available_balance',bcmul($amount,100,0));//减少可用
                break;
            case 'freeze':
                $operation_stage = 'freezeBalance';
                $available_balance = -$amount;
                $freeze_balance = $amount;
                $description = "管理员调整预存款，冻结余额 {$amount}，操作者编号{$adminId}";
                $user->increment('freeze_balance',bcmul($amount,100,0)); //增加冻结
                $user->decrement('available_balance',bcmul($amount,100,0));//减少可用
                break;
            case 'unfreeze':
                $operation_stage = 'unfreezeBalance';
                $available_balance = $amount;
                $freeze_balance = -$amount;
                $description = "管理员调整预存款，解冻余额 {$amount}，操作者编号{$adminId}";
                $user->decrement('freeze_balance',bcmul($amount,100,0)); //减少冻结
                $user->increment('available_balance',bcmul($amount,100,0));//增加可用
                break;
        }
        SysBalanceLog::create([
            'user_id' => $user->id,
            'user_name' => $user->nick_name,
            'available_balance' => $available_balance,
            'freeze_balance' => $freeze_balance,
            'old_available_balance' => $old_available_balance,
            'old_freeze_balance' => $old_freeze_balance,
            'operation_stage' => $operation_stage,
            'description' => $description
        ]);
    }

    /**
     * 充值
     * @param $info
     * @throws \Exception
     */
    public static function recharge($info)
    {
        $user = SysUser::where('id',$info->user_id)->first();
        if($user == null){
            throw new \Exception('会员异常');
        }
        $old_available_balance = $user->available_balance;
        $old_freeze_balance = $user->freeze_balance;
        $amount = bcadd($info->amount,$info->give_amount,2);
        $user->increment('available_balance',bcmul($amount,100,0));//增加可用余额
        //写入资金记录
        SysBalanceLog::create([
            'user_id' => $user->id,
            'user_name' => $user->nick_name,
            'available_balance' => $amount,
            'freeze_balance' => 0,
            'old_available_balance' => $old_available_balance,
            'old_freeze_balance' => $old_freeze_balance,
            'operation_stage' => 'recharge',
            'description' => "充值成功，充值编号：{$info->id}，充值金额：{$info->amount}，赠送金额：{$info->give_amount}"
        ]);
    }

    /**
     * 余额发布感谢
     * @param $info
     * @throws \Exception
     */
    public static function thanksFromUser($info)
    {
        $user = SysUser::where('id',$info->user_id)->first();
        if($user == null){
            throw new \Exception('会员异常');
        }
        $amount = $info->amount;
        $old_available_balance = $user->available_balance;
        $old_freeze_balance = $user->freeze_balance;
        $user->decrement('available_balance',bcmul($amount,100,0));//减少可用余额
        $allType = config('phcentask.allType');
        $type = isset($allType[$info->type])?$allType[$info->type]:'未知';
        //写入资金记录
        SysBalanceLog::create([
            'user_id' => $user->id,
            'user_name' => $user->nick_name,
            'available_balance' => -$amount,
            'freeze_balance' => 0,
            'old_available_balance' => $old_available_balance,
            'old_freeze_balance' => $old_freeze_balance,
            'operation_stage' => 'postThank',
            'description' => "发布感谢，金额：{$amount},{$type}编号：{$info->theme_id}"
        ]);
        self::thanksToUser($info,$user);
    }

    /**
     * 被感谢者收入
     * @param $info
     * @param $u
     * @throws \Exception
     */
    public static function thanksToUser($info,$u)
    {
        $user = SysUser::where('id',$info->to_user_id)->first();
        if($user == null){
            throw new \Exception('会员异常');
        }
        $amount = $info->amount;
        $old_available_balance = $user->available_balance;
        $old_freeze_balance = $user->freeze_balance;
        $balanceCommission = config('phcentask.balanceCommission',0); //资金扣点
        if($balanceCommission > 0 && $info->amount > 0.1){ //大于1毛才扣点
            $amount = bcmul($info->amount,bcdiv($balanceCommission,100,5),2);
        }
        $user->increment('available_balance',bcmul($amount,100,0));//增加可用余额
        $type = config('phcentask.allType')[$info->type];
        //写入资金记录
        SysBalanceLog::create([
            'user_id' => $user->id,
            'user_name' => $user->nick_name,
            'available_balance' => $amount,
            'freeze_balance' => 0,
            'old_available_balance' => $old_available_balance,
            'old_freeze_balance' => $old_freeze_balance,
            'operation_stage' => 'receivedThank',
            'description' => "收到来自（{$u->nick_name}）感谢，金额：{$amount},{$type}编号：{$info->theme_id}"
        ]);
    }

    /**
     * 付费阅读
     * @param $info
     * @param $u
     * @throws \Exception
     */
    public static function paidToUser($info,$u)
    {
        $user = SysUser::where('id',$info->to_user_id)->first();
        if($user == null){
            throw new \Exception('会员异常');
        }
        $amount = $info->amount;
        $old_available_balance = $user->available_balance;
        $old_freeze_balance = $user->freeze_balance;
        $balanceCommission = config('phcentask.balanceCommission',0); //资金扣点
        if($balanceCommission > 0 && $info->amount > 0.1){ //大于1毛才扣点
            $amount = bcmul($info->amount,bcdiv($balanceCommission,100,5),2);
        }
        $user->increment('available_balance',bcmul($amount,100,0));//增加可用余额
        $type = config('phcentask.allType')[$info->type];
        //写入资金记录
        SysBalanceLog::create([
            'user_id' => $user->id,
            'user_name' => $user->nick_name,
            'available_balance' => $amount,
            'freeze_balance' => 0,
            'old_available_balance' => $old_available_balance,
            'old_freeze_balance' => $old_freeze_balance,
            'operation_stage' => $info->type ===  2? 'payArticle':'payAnswer',
            'description' => "收到来自（{$u->nick_name}）付费阅读，金额：{$amount},{$type}编号：{$info->theme_id}"
        ]);
    }

}