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


use Illuminate\Support\Facades\Date;
use Phcent\WebmanAsk\Model\AskAnswer;
use Phcent\WebmanAsk\Model\AskArticle;
use Phcent\WebmanAsk\Model\AskQuestion;
use Phcent\WebmanAsk\Model\AskThanks;
use Phcent\WebmanAsk\Model\SysOrders;
use support\Db;

class AskThanksService
{

    public static function unique_rand($min, $max, $num,$radix = 0) {
        $count = 0;
        $return = array();
        while ($count < $num) {
            $number = $min + mt_rand() / mt_getrandmax() * ( $max - $min);
            $return[] = sprintf("%.{$radix}f",$number);
            $return = array_flip(array_flip($return));
            $count = count($return);
        }
        //打乱数组，重新赋予数组新的下标
        shuffle($return);
        return $return;
    }

    /**
     * 创建感谢订单
     * @param $params
     * @param $user
     * @return
     * @throws \Throwable
     */
    public static function createThanks($params,$user)
    {
        try {
            switch (intval($params->type)){
                case 1:
                    $info = AskQuestion::where('id',$params->theme_id)->first();
                    break;
                case 2:
                    $info = AskArticle::where('id',$params->theme_id)->first();
                    break;
                case 3:
                    $info = AskAnswer::where('id',$params->theme_id)->first();
                    break;
                default:
                    throw new \Exception('类型错误');
            }
            if($info == null){
                throw new \Exception('数据不存在');
            }
            Db::connection()->beginTransaction();
            $thanksInfo = AskThanks::create([
                'content' => $params->content,
                'user_id' => $user->id,
                'to_user_id' => $info->user_id,
                'amount' =>$params->amount,
                'theme_id' => $params->theme_id,
                'type' => $params->type,
                'order_id' => 0
            ]);
            if(intval($params->pay_type) === 1){
                $order = SysOrders::create([
                    'theme_id' => $thanksInfo->id,
                    'operation_stage' => 'thank',
                    'user_id' => $user->id,
                    'order_sn' => OrdersService::getOrderSn($user->id),
                    'amount' => $params->amount
                ]);
                $thanksInfo->order_id = $order->id;
                $thanksInfo->save();
            }else{
                if($user->available_balance >= $params->amount){
                    BalanceService::thanksFromUser($thanksInfo);
                    $info->increment('thank_num');
                    $thanksInfo->pay_time = Date::now();
                    $thanksInfo->status = 1;
                    $thanksInfo->save();
                }else{
                    throw new \Exception('可用资金不足');
                }
            }
            Db::connection()->commit();
            return $thanksInfo;
        }catch (\Exception $e){
            Db::connection()->rollBack();
            throw new \Exception($e->getMessage());
        }


    }
}