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
 * @since      象讯·PHP商城系统Pro
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Service;


use Illuminate\Support\Facades\Date;
use Phcent\WebmanAsk\Model\AskAnswer;
use Phcent\WebmanAsk\Model\AskArticle;
use Phcent\WebmanAsk\Model\AskQuestion;
use Phcent\WebmanAsk\Model\AskReply;
use Phcent\WebmanAsk\Model\AskUser;

class AnswerService
{
    /**
     * 新增回答
     * @param $params
     * @param $userId
     * @throws \Exception
     */
    public static function createAnswer($params,$userId)
    {
        $question = AskQuestion::where('id',$params['question_id'])->first();
        if($question == null){
            throw new \Exception('问题不存在');
        }
        $admin = IndexService::isHaveAdminRole($userId,$question->cate_id);
        if(!$admin){
            if(!in_array($question->status,[1,2])){
                 throw new \Exception('该问题状态不允许回答');
            }
        }
        $askAnswer = AskAnswer::create([
            'question_id' => $params['question_id'],
            'reward_balance' => $params['reward_balance'],
            'reward_points' => $params['reward_points'],
            'content' => $params['content'],
            'user_id' => $userId,
        ]);
        //增加问题回答数量
        $question->increment('answer_num');
        //增加会员回答数量
        AskUserService::optionsNum($userId,'answer');
        //增加会员回答奖励积分
        PointsService::publishAnswer($askAnswer);
    }

    /**
     * 新增评论
     * @param $params
     * @param $userId
     * @throws \Exception
     */
    public static function createReply($params,$userId)
    {
       switch ($params['type']){
           //回答
           case 1:
               $answer = AskAnswer::where('id',$params['theme_id'])->with('question')->whereHas('question')->first();
               if($answer == null){
                   throw new \Exception('参数错误');
               }
               $admin = IndexService::isHaveAdminRole($userId,$answer->question->cate_id);
               if(!$admin){
                   if($answer->status !== 1 || !in_array($answer->question->status,[1,2])){
                       throw new \Exception('该问答状态不允许评论');
                   }
               }
               $reply = AskReply::create([
                   'theme_id' => $params['theme_id'],
                    'user_id' => $userId,
                    'content' => $params['content'],
                    'reply_user_id' => $params['reply_user_id'],
                    'type' => $params['type'],
               ]);
               $answer->increment('reply_num');
               $answer->question->increment('reply_num');

               //增加评论积分
                PointsService::publishReply($reply);
               //增加会员评论数量
               AskUserService::optionsNum($userId,'reply');

               break;
           //文章评论
           case 2:
               $article = AskArticle::where('id',$params['theme_id'])->first();
               if($article == null){
                   throw new \Exception('文章不存在');
               }
               $admin = IndexService::isHaveAdminRole($userId,$article->cate_id);
               if(!$admin){
                   if(!in_array($article->status,[1,2])){
                       throw new \Exception('该问题状态不允许回答');
                   }
               }
               $reply = AskReply::create([
                   'theme_id' => $params['theme_id'],
                   'user_id' => $userId,
                   'content' => $params['content'],
                   'reply_user_id' => $params['reply_user_id'],
                   'type' => $params['type'],
               ]);
               $article->increment('reply_num');

               //增加评论积分
               PointsService::publishReply($reply);
               //增加会员评论数量
               AskUserService::optionsNum($userId,'reply');

               break;
           default:
               throw new \Exception('异常');
               break;
       }
    }

    /**
     * 采纳答案
     * @param $params
     * @param $userId
     * @throws \Exception
     */
    public static function adoptAnswer($params,$userId)
    {
        $question = AskQuestion::where('id',$params['question_id'])->first();
        if($question == null){
            throw new \Exception('问题不存在');
        }
        if($question->status == 2){
            throw new \Exception('该问题已经解决了');
        }
        $admin = IndexService::isHaveAdminRole($userId,$question->cate_id);
        if(!$admin){
            if($question->user_id == $userId){
                $expire = config('phcentask.rewardTime',7);
                if(Date::parse($question->reward_time)->diffInDays(Date::now()) > $expire){
                    throw new \Exception('问题悬赏已过期，请等待管理员操作');
                }
            }else{
                throw new \Exception('无权限操作');
            }
        }

        $answer = AskAnswer::where('id',$params['answer_id'])->first();
        if($answer == null){
            throw new \Exception('答案不存在');
        }
        if($answer->question_id != $question->id){
            throw new \Exception('数据异常');
        }
        //存储最佳答案
        $question->best_answer = $params['answer_id'];
        $question->status = 2;
        $question->save();
        //最佳答案时间
        $answer->reward_time = Date::now();
        $answer->save();
        //更新被采纳者最佳答案数量
        AskUserService::optionsNum($answer->user_id,'answer_best');
        //悬赏金额分配
        if($question->reward_balance > 0){
            BalanceService::bestAnswer($question,$answer->user_id);
        }
        if($question->reward_points > 0){
            PointsService::bestAnswer($question,$answer->user_id);
        }
    }
}