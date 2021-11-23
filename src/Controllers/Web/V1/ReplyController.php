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


namespace Phcent\WebmanAsk\Controllers\Web\V1;


use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\AskReply;
use Phcent\WebmanAsk\Service\AnswerService;
use Phcent\WebmanAsk\Service\IndexService;
use Respect\Validation\Validator;
use support\Db;
use support\Request;

class ReplyController
{
    /**
     * 查询评论列表
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function index(Request $request,$id)
    {
        try {
            phcentMethod(['GET']);
            if(!is_numeric($id) && empty($id)){
                throw new \Exception('编号不正确');
            }
            $askReply = new AskReply();
            $type = $request->input('order','default');
            switch ($type){
                case 'date':
                    $askReply = $askReply->orderBy('created_at','desc');
                    break;
                default:
                    $askReply = $askReply->orderBy('id','asc');
                    break;
            }
            $userId = AuthLogic::getInstance()->userId();
            $adminRole = IndexService::isHaveAdminRole($userId,0);
            $list  = $askReply->where('status',1)->where('type',2)->where('theme_id',$id)->with(['user',
                'digg' => function($query) use ($userId) {
                    $query->where('user_id',$userId);
                }
            ])->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $list->map(function ($item) use ($adminRole,$userId) {
                if($item->user != null){
                    $item->user_name = $item->user->nick_name;
                    $item->user_avatar = $item->user->avatar_url;
                    $item->user_description = $item->user->description;
                }else{
                    $item->user_name = '会员不存在';
                    $item->user_avatar = '';
                    $item->user_description = '';
                }
                $item->is_digg = $item->digg->where('conduct','up')->first() != null ?1:0;
                $item->is_step = $item->digg->where('conduct','down')->first() != null ?1:0;
                if(!empty($userId)) {
                    if ($item->user_id == $userId) {
                        $item->show_edit = 1;
                        $item->show_delete = 1;
                    }
                    if ($adminRole) {
                        $item->show_edit = 1;
                        $item->show_delete = 1;
                    }
                }
                $item->setHidden(['user']);
            });
            $data['list'] = $list->items();
            return phcentSuccess($data,'评论列表',[ 'page' => $list->currentPage(),'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 发布回答
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function create(Request $request)
    {
        try {
            phcentMethod(['POST']);
            Validator::input($request->post(), [
                'content' => Validator::length(3,10000)->setName('评论内容'),
                'theme_id' => Validator::digit()->min(1)->setName('来源编号'),
                'type' => Validator::digit()->in([3,2])->setName('来源类型'),
            ]);
            $params = phcentParams(['type','content','theme_id' => 0,'reply_user_id'=>0]);
            $userId = AuthLogic::getInstance()->userId();
            if(empty($userId)){
                throw new \Exception('请先登入');
            }
            Db::connection()->beginTransaction();
            AnswerService::createReply($params,$userId);
            Db::connection()->commit();
            return phcentSuccess();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }

    /**
     * 修改评论
     * @param Request $request
     * @param $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function update(Request $request,$id)
    {
        try {
            phcentMethod(['POST']);
            if(!is_numeric($id) || empty($id)){
                throw new \Exception('编号不正确');
            }
            Validator::input($request->post(), [
                'content' => Validator::length(3,10000)->setName('评论内容'),
            ]);
            $params = phcentParams(['content']);
            $userId = AuthLogic::getInstance()->userId();
            if(empty($userId)){
                throw new \Exception('请先登入');
            }
            Db::connection()->beginTransaction();
            AnswerService::updateReply($id,$params,$userId);
            Db::connection()->commit();
            return phcentSuccess();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }

    /**
     * 删除评论
     * @param Request $request
     * @param $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function destroy(Request $request,$id)
    {
        try {
            phcentMethod(['Delete']);
            if(!is_numeric($id) || empty($id)){
                throw new \Exception('编号不正确');
            }
            $userId = AuthLogic::getInstance()->userId();
            if(empty($userId)){
                throw new \Exception('请先登入');
            }
            Db::connection()->beginTransaction();
            AnswerService::destroyReply($id,$userId);
            Db::connection()->commit();
            return phcentSuccess();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }
}