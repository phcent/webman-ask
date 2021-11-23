<?php
/**
 *-------------------------------------------------------------------------p*
 * 订单关联
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


namespace Phcent\WebmanAsk\Controllers\User\V1;


use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\SysBalanceLog;
use Phcent\WebmanAsk\Model\SysOrders;
use Phcent\WebmanAsk\Service\OrdersService;
use Respect\Validation\Validator;
use support\Redis;
use support\Request;

class OrdersController
{
    /**
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
            $sysOrders = new SysOrders();
            $sysOrders = phcentWhereParams($sysOrders, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $sysOrders = $sysOrders->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $sysOrders = $sysOrders->orderBy('id', 'desc');
            }
            $list = $sysOrders->where('user_id',$user->id)->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $data['list'] = $list->items();
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 订单支付
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function pay(Request $request,$id)
    {
        try {
            phcentMethod(['GET','POST']);
            if($request->method()=='GET'){
                $userId = AuthLogic::getInstance()->userId();
                if(empty($userId)){
                    throw new \Exception('会员未登入');
                }
                $data['info'] = SysOrders::where('user_id',$userId)->where('id',$id)->first();
                $data['paymentList'] = [
                    ['paymentCode'=>'alipay','paymentName'=>'支付宝','status'=>!empty(config('phcentask.pay.alipay.default.app_id',''))],
                    ['paymentCode'=>'wxpay','paymentName'=>'微信支付','status'=>!empty(config('phcentask.pay.wechat.default.mch_id',''))]
                ];
                return phcentSuccess( $data);
            }else{
                Validator::input($request->all(), [
                    'payment_code' => Validator::stringType()->noWhitespace()->notEmpty()->in(['alipay','wxpay'])->setName('支付方式'),
                ]);
                $params = phcentParams(['payment_code','client_type' => 'web']);
                $payInfo = OrdersService::payOrders($params,$id);
                return phcentSuccess($payInfo);
            }

        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 查询订单支付状态
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function status(Request $request,$id)
    {
        try {
            phcentMethod(['POST']);
//            $userId = AuthLogic::getInstance()->userId();
//            if(empty($userId)){
//                throw new \Exception('会员未登入');
//            }
//            $info = SysOrders::where('user_id',$userId)->where('id',$id)->first();
//            if($info == null){
//                throw new \Exception('订单不存在');
//            }
            return phcentSuccess(Redis::get("phcentOrderStatus{$id}"));
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }
}