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
use Phcent\WebmanAsk\Model\AskQuestion;
use Phcent\WebmanAsk\Model\AskReply;
use Phcent\WebmanAsk\Service\AnswerService;
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
            $askAnswer = new AskAnswer();
            $bestAnswer = AskAnswer::where('question_id',$id)->whereNotNull('reward_time')->first();
            //排除最佳答案
            if($bestAnswer != null){
                $askAnswer = $askAnswer->where('id','<>',$bestAnswer->id);
            }
            $params = phcentParams(['page' => 1,'limit' =>10]);
            $type = $request->input('order','new');
            switch ($type){
                case 'date':
                    $askAnswer = $askAnswer->orderBy('created_at','desc');
                    break;
                default:
                    $askAnswer = $askAnswer->orderBy('id','asc')->orderBy('digg_num','desc');
                    break;
            }
            $list  = $askAnswer->where('question_id',$id)->with('user')->paginate($params['limit']);
            $list->map(function ($item){
                $item->is_collection = 0;
                $item->is_digg = 0;
                $item->is_step = 0;
                $item->show_edit = 0;
                $item->show_delete = 0;
                if($item->user != null){
                    $item->user_name = $item->user->nick_name;
                    $item->user_avatar = $item->user->avatar_url;
                    $item->user_description = $item->user->description;
                }else{
                    $item->user_name = '会员不存在';
                    $item->user_avatar = '';
                    $item->user_description = '';
                }
                $item->setHidden(['user']);
            });
            $data['best_answer'] = $bestAnswer;
            $data['list'] = $list->items();
            return phcentSuccess($data,'回答列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
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
            $list  = $askReply->where('status',1)->where('type',1)->where('theme_id',$id)->with('user')->paginate($params['limit']);
            $list->map(function ($item){
                if($item->user != null){
                    $item->user_name = $item->user->nick_name;
                    $item->user_avatar = $item->user->avatar_url;
                    $item->user_description = $item->user->description;
                }else{
                    $item->user_name = '会员不存在';
                    $item->user_avatar = '';
                    $item->user_description = '';
                }
                $item->setHidden(['user']);
            });
            $data['list'] = $list->items();
//            $data['answer'] = $askAnswer;
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
                'content' => Validator::length(10,10000)->setName('回答内容'),
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
}