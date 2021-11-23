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


namespace Phcent\WebmanAsk\Controllers\User\V1;


use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\SysBalanceLog;
use Phcent\WebmanAsk\Model\SysRechargeLog;
use Phcent\WebmanAsk\Service\OrdersService;
use Respect\Validation\Validator;
use support\Request;

class RechargeController
{
    /**
     * 获取充值日志
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $user = AuthLogic::getInstance()->user();
            if($user == null){
                throw new \Exception('会员未登入');
            }
            $rechargeLog = new SysRechargeLog();
            $rechargeLog = phcentWhereParams($rechargeLog, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $rechargeLog = $rechargeLog->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $rechargeLog = $rechargeLog->orderBy('id', 'desc');
            }
            $list = $rechargeLog->where('user_id',$user->id)->with('orders')->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $data['list'] = $list->items();
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 余额日志
     * @param Request $request
     * @return \support\Response
     */
    public function balance(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $user = AuthLogic::getInstance()->user();
            if($user == null){
                throw new \Exception('会员未登入');
            }
            $sysBalanceLog = new SysBalanceLog();
            $sysBalanceLog = phcentWhereParams($sysBalanceLog, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $sysBalanceLog = $sysBalanceLog->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $sysBalanceLog = $sysBalanceLog->orderBy('id', 'desc');
            }
            $list = $sysBalanceLog->where('user_id',$user->id)->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $data['list'] = $list->items();
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 创建充值
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function create(Request $request)
    {
        try {
            phcentMethod(['GET','POST']);
            if($request->method() == 'GET'){
                $user = AuthLogic::getInstance()->user();
                if($user == null){
                    throw new \Exception('会员未登入');
                }
                $data['available_balance'] = $user->available_balance;
                $data['rechargeRule'] = config('phcentask.rechargeRule',[]);
                return phcentSuccess( $data);
            }else{
                Validator::input($request->post(), [
                    'amount' => Validator::number()->min(0.01)->noWhitespace()->setName('充值金额'),
                   // 'payment_code' => Validator::stringType()->noWhitespace()->notEmpty()->in(['alipay','wxpay'])->setName('支付方式'),
                ]);
                $params = phcentParams(['amount','payment_code','client_type' => 'web']);
                $order = OrdersService::createRecharge($params);
                return phcentSuccess($order);
            }
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }
}