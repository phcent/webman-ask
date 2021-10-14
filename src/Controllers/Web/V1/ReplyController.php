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
            $params = phcentParams(['page' => 1,'limit' =>10]);
            $type = $request->input('order','default');
            switch ($type){
                case 'date':
                    $askReply = $askReply->orderBy('created_at','desc');
                    break;
                default:
                    $askReply = $askReply->orderBy('id','desc');
                    break;
            }
            $list  = $askReply->where('status',1)->where('type',2)->where('theme_id',$id)->paginate($params['limit']);
            $list->map(function ($item){

            });
            $data['list'] = $list->items();

            return phcentSuccess($data,'评论列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
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
                'content' => Validator::length(10,10000)->setName('评论内容'),
                'theme_id' => Validator::digit()->min(1)->setName('来源编号'),
                'type' => Validator::digit()->in([1,2])->setName('来源类型'),
            ]);
            $params = phcentParams(['type','content','theme_id' => 1,'reply_user_id'=>0]);
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
}