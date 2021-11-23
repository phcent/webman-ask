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
use Phcent\WebmanAsk\Model\SysCashLog;
use Phcent\WebmanAsk\Service\CashService;
use Respect\Validation\Validator;
use support\Db;
use support\Request;

class CashController
{
    /**
     * 获取提现日志
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
            $cashLog = new SysCashLog();
            $cashLog = phcentWhereParams($cashLog, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $cashLog = $cashLog->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $cashLog = $cashLog->orderBy('id', 'desc')->orderBy('id', 'desc');
            }
            $list = $cashLog->where('user_id',$user->id)->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $data['list'] = $list->items();
            $data['available_balance'] = $user->available_balance;
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 申请提现
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
                $data['minCash'] = config('phcentask.minCash');
                $data['cashCommission'] = config('phcentask.cashCommission');
                return phcentSuccess( $data);
            }else{
                $user = AuthLogic::getInstance()->lockUser();
                if($user == null){
                    throw new \Exception('会员未登入');
                }
                $minCash = config('phcentask.minCash');
                Validator::input($request->post(), [
                    'amount' => Validator::digit()->min($minCash)->noWhitespace()->setName('提现金额'),
                    'bank_name' => Validator::stringType()->noWhitespace()->notEmpty()->setName('银行名称'),
                    'bank_user' => Validator::stringType()->noWhitespace()->notEmpty()->setName('真实姓名'),
                    'bank_account' => Validator::stringType()->length(6,22)->noWhitespace()->setName('账户'),
                ]);
                $params = phcentParams([
                    'amount',
                    'bank_name',
                    'bank_user',
                    'bank_account'
                ]);
                Db::connection()->beginTransaction();
                 CashService::applyCash($params,$user);
                Db::connection()->commit();
                return phcentSuccess();
            }
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError( $e->getMessage());
        }
    }
}