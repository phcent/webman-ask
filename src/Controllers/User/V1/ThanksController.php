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


namespace Phcent\WebmanAsk\Controllers\User\V1;


use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\AskThanks;
use Phcent\WebmanAsk\Service\AskThanksService;
use Respect\Validation\Validator;
use support\Request;

class ThanksController
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
            $askThanks = new AskThanks();
            $askThanks = phcentWhereParams($askThanks, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askThanks = $askThanks->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $askThanks = $askThanks->orderBy('id', 'desc');
            }
            $list = $askThanks->where('user_id',$user->id)->with(['toUser'])->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $list->map(function ($item){
                if($item->order_id > 0){
                    $item->load('order');
                }
                if($item->toUser != null){
                    $item->to_user_name = $item->toUser->nick_name;
                }else{
                    $item->to_user_name = '异常';
                }
                $item->setHidden(['toUser']);
            });
            $data['list'] = $list->items();
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }
    /**
     * 创建感谢订单
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function create(Request $request)
    {
        try {
            phcentMethod(['GET','POST']);
            $user = AuthLogic::getInstance()->user();
            if($user == null){
                throw new \Exception('请先登入');
            }

            if($request->method() == 'GET'){
                $data['thanksMoneyList'] = AskThanksService::unique_rand(1,100,6,1);
                $data['thanksType'] = config('phcentask.thanksType');
                $data['userBalance'] = $user->available_balance;
                return phcentSuccess($data);
            }else{
                Validator::input($request->all(), [
                    'amount' => Validator::number()->min(0.01)->setName('感谢金额'),
                    'type' => Validator::digit()->in([1,2,3])->setName('来源'),
                    'theme_id' => Validator::digit()->min(1)->setName('项目编号'),
                    'pay_type' => Validator::digit()->in([1,2])->setName('支付方式'),
                ]);
                $params = phcentParams(['amount','type','theme_id','pay_type','content'=>''],null,true);
                $info = AskThanksService::createThanks($params,$user);
                return phcentSuccess($info);
            }
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }
}