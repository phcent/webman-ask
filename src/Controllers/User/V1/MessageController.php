<?php
/**
 *-------------------------------------------------------------------------p*
 * 我的私信
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
use Phcent\WebmanAsk\Model\AskMessage;
use Phcent\WebmanAsk\Model\User;
use Respect\Validation\Validator;
use support\Request;

class MessageController
{
    /**
     * 获得私信列表
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $userId = AuthLogic::getInstance()->userId();
            $askMessage = new AskMessage();
            $params = phcentParams(['page' => 1,'limit' =>10]);
            $askMessage = phcentWhereParams($askMessage,$params);
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askMessage = $askMessage->orderBy(request()->input('sortName'),request()->input('sortOrder'));
            }else{
                $askMessage = $askMessage->orderBy('id','desc');
            }
            $list  = $askMessage->where('to_user_id',$userId)->where('to_user_del','<>',1)->with('toUser')->paginate($params['limit']);
            $list->map(function ($item){
                if($item->toUser != null){
                    $item->to_user_name = $item->toUser->nick_name;
                    $item->to_user_avatar = $item->toUser->avatar_url;
                }else{
                    $item->to_user_avatar = '';
                }
                $item->setHidden(['toUser']);
            });
            $data['list'] = $list->items();
            return phcentSuccess($data,'私信列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 变更为已读状态
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
                $askMessage = AskMessage::where('id',$id)->first();
                if($askMessage == null){
                    throw new \Exception('私信不存在');
                }
                if($askMessage->to_user_id != $userId){
                    throw new \Exception('不能变更非自己的私信状态');
                }
                if($askMessage->is_read == 1){
                    throw new \Exception('信息已经是已读状态了');
                }
                $askMessage->is_read = 1;
                $askMessage->save();
            }else{
                AskMessage::where('to_user_id',$userId)->where('is_read','<>',1)->update(['is_read'=>1]);
            }
            return phcentSuccess();
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 发送私信
     * @param Request $request
     * @return \support\Response
     */
    public function create(Request $request)
    {
        try {
            phcentMethod(['POST']);
            $user = AuthLogic::getInstance()->user();
            if($user == null){
                throw new \Exception('会员未登入');
            }
            Validator::input($request->post(), [
                'to_user_id' => Validator::digit()->min(1)->setName('私信人'),
                'content' => Validator::length(3,5000)->setName('私信内容'),
            ]);
            $params = phcentParams(['to_user_id','content']);
            $toUser = User::where('id',$params['to_user_id'])->first();
            if($toUser == null){
                throw new \Exception('会员异常');
            }
            AskMessage::create([
                'user_id' => $user->id,
                'user_name' => $user->nick_name,
                'to_user_id' => $toUser->id,
                'to_user_name' => $toUser->nick_name,
                'content' => $params['content']
            ]);
           return phcentSuccess();
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 删除
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
            $askMessage = AskMessage::where('id',$id)->first();
            if($askMessage == null){
                throw new \Exception('信息不存在');
            }
            if($askMessage->user_id != $userId){
                throw new \Exception('不能变更非自己的信息状态');
            }
            if($askMessage->to_user_del == 1){
                throw new \Exception('信息已经被删除了');
            }
            $askMessage->to_user_del = 1;
            $askMessage->save();

            return phcentSuccess();
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }
}