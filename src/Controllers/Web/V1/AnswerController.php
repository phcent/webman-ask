<?php
/**
 *-------------------------------------------------------------------------p*
 * 回答
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
use Phcent\WebmanAsk\Model\AskAnswer;
use Phcent\WebmanAsk\Model\AskDigg;
use Phcent\WebmanAsk\Model\AskQuestion;
use Phcent\WebmanAsk\Model\AskReply;
use Phcent\WebmanAsk\Service\AnswerService;
use Phcent\WebmanAsk\Service\IndexService;
use Respect\Validation\Validator;
use support\Db;
use support\Request;

class AnswerController
{
    /**
     * 回答列表
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
            $question = AskQuestion::where('id',$id)->first();
            if($question == null){
                throw new \Exception('问题不存在');
            }
            $userId = AuthLogic::getInstance()->userId();
            $adminRole = IndexService::isHaveAdminRole($userId,0);
            $askAnswer = new AskAnswer();
            $bestAnswer = AskAnswer::where('question_id',$id)->with(['user',
                'digg'=>function($query) use ($userId) {
                    $query->where('user_id',$userId);
                },
                'collection'=>function($query) use ($userId) {
                    $query->where('user_id',$userId);
                }
            ])->whereNotNull('reward_time')->first();

            //排除最佳答案
            if($bestAnswer != null){
                $bestAnswer = AnswerService::calcItem($bestAnswer,$userId,$adminRole,$question);
                $bestAnswer->setHidden(['user','digg','collection']);
                $askAnswer = $askAnswer->where('id','<>',$bestAnswer->id);
            }
            $type = $request->input('order','new');
            switch ($type){
                case 'date':
                    $askAnswer = $askAnswer->orderBy('created_at','desc');
                    break;
                default:
                    $askAnswer = $askAnswer->orderBy('id','asc')->orderBy('digg_num','desc');
                    break;
            }
            $list  = $askAnswer->where('question_id',$id)
                ->with(['user',
                    'digg'=>function($query) use ($userId) {
                        $query->where('user_id',$userId);
                    },
                    'collection'=>function($query) use ($userId) {
                        $query->where('user_id',$userId);
                    }
                ])
                ->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $list->map(function ($item) use ($question, $userId,$adminRole){
                $item = AnswerService::calcItem($item,$userId,$adminRole,$question);
                $item->setHidden(['user','digg','collection']);
            });
            $data['best_answer'] = $bestAnswer;
            $data['list'] = $list->items();
            return phcentSuccess($data,'回答列表',[ 'page' => $list->currentPage(),'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 获取问答评论
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function show(Request $request,$id)
    {
        try {
            phcentMethod(['GET']);
//            $askAnswer = AskAnswer::where('id',$id)->first();
//            if($askAnswer == null){
//                throw new \Exception('回答不存在');
//            }
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
                    $askReply = $askReply->orderBy('id','asc')->orderBy('digg_num','desc');
                    break;
            }
            $userId = AuthLogic::getInstance()->userId();
            $adminRole = IndexService::isHaveAdminRole($userId,0);
            $list  = $askReply->where('status',1)->where('type',3)->where('theme_id',$id)->with(['user',
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
//            $data['answer'] = $askAnswer;
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
                'content' => Validator::length(3,10000)->setName('回答内容'),
                'question_id' => Validator::digit()->min(1)->setName('问题编号'),
            ]);
            $params = phcentParams(['question_id','content','reward_points' => 0,'reward_balance' => 0]);
            $userId = AuthLogic::getInstance()->userId();
            if(empty($userId)){
                throw new \Exception('请先登入');
            }
            Db::connection()->beginTransaction();
            AnswerService::createAnswer($params,$userId);
            Db::connection()->commit();
            return phcentSuccess();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }

    /**
     * 采纳答案
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function adopt(Request $request)
    {
        try {
            phcentMethod(['POST']);
            Validator::input($request->post(), [
                'question_id' => Validator::digit()->min(1)->setName('问题编号'),
                'answer_id' => Validator::digit()->min(1)->setName('回答编号'),
            ]);
            $params = phcentParams(['question_id','answer_id']);
            $userId = AuthLogic::getInstance()->userId();
            if(empty($userId)){
                throw new \Exception('请先登入');
            }
            Db::connection()->beginTransaction();
            AnswerService::adoptAnswer($params,$userId);
            Db::connection()->commit();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }

    /**
     * 修改答案
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
                'content' => Validator::length(3,10000)->setName('回答内容'),
            ]);
            $params = phcentParams(['content','reward_points' => 0,'reward_balance' => 0]);
            $userId = AuthLogic::getInstance()->userId();
            if(empty($userId)){
                throw new \Exception('请先登入');
            }
            Db::connection()->beginTransaction();
            $answer = AnswerService::updateAnswer($id,$params,$userId);
            Db::connection()->commit();
            return phcentSuccess($answer);
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }

    /**
     * 删除回答
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
            AnswerService::destroyAnswer($id,$userId);
            Db::connection()->commit();
            return phcentSuccess();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }
}