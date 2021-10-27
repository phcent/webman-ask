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
use Phcent\WebmanAsk\Model\AskNotice;
use support\Request;

class NoticeController
{
    /**
     * 查询我的通知表
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $userId = AuthLogic::getInstance()->userId();
            $askNotice = new AskNotice();
            $params = phcentParams(['page' => 1,'limit' =>10]);
            $askNotice = phcentWhereParams($askNotice,$params);
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askNotice = $askNotice->orderBy(request()->input('sortName'),request()->input('sortOrder'));
            }else{
                $askNotice = $askNotice->orderBy('id','desc');
            }
            $list  = $askNotice->where('user_id',$userId)->with('fromUser')->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $list->map(function ($item){
                if($item->fromUser != null){
                    $item->from_user_name = $item->fromUser->nick_name;
                    $item->from_user_avatar = $item->fromUser->avatar_url;
                }else{
                    $item->from_user_name = '会员不存在';
                    $item->from_user_avatar = '';
                }
                $item->setHidden(['fromUser']);
            });
            $data['list'] = $list->items();
            return phcentSuccess($data,'私信列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 设为已读
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function read(Request $request,$id)
    {
        try {
            phcentMethod(['POST']);
            $userId = AuthLogic::getInstance()->userId();
            if(is_numeric($id) && !empty($id)){
                $askNotice = AskNotice::where('id',$id)->first();
                if($askNotice == null){
                    throw new \Exception('通知不存在');
                }
                if($askNotice->user_id != $userId){
                    throw new \Exception('不能变更非自己的通知状态');
                }
                if($askNotice->is_read == 1){
                    throw new \Exception('通知已经是已读状态了');
                }
                $askNotice->is_read = 1;
                $askNotice->save();
            }else{
                AskNotice::where('user_id',$userId)->where('is_read','<>',1)->update(['is_read'=>1]);
            }
            return phcentSuccess();
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 删除通知
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function destroy(Request $request,$id)
    {
        try {
            phcentMethod(['DELETE']);
            $userId = AuthLogic::getInstance()->userId();
            if(!is_numeric($id) && empty($id)) {
                throw new \Exception('编号参数异常');
            }
            $askNotice = AskNotice::where('id',$id)->first();
            if($askNotice == null){
                throw new \Exception('通知不存在');
            }
            if($askNotice->user_id != $userId){
                throw new \Exception('不能变更非自己的通知状态');
            }
            AskNotice::destroy($id);

            return phcentSuccess();
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }
}