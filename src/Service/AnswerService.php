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


namespace Phcent\WebmanAsk\Service;


use Illuminate\Support\Facades\Date;
use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\AskAnswer;
use Phcent\WebmanAsk\Model\AskArticle;
use Phcent\WebmanAsk\Model\AskQuestion;
use Phcent\WebmanAsk\Model\AskReply;
use support\Db;

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
     * 补充回答
     * @param $id
     * @param $params
     * @param $userId
     * @throws \Exception
     */
    public static function updateAnswer($id,$params,$userId)
    {
        $answer = AskAnswer::where('id',$id)->with('question')->has('question')->first();
        if($answer == null || $answer->status != 1){
            throw new \Exception('数据异常或状态不允许修改');
        }
        $admin = IndexService::isHaveAdminRole($userId,$answer->question->cate_id);
        if(!$admin){
            if(!in_array($answer->question->status,[2,3])){
                throw new \Exception('该问题状态不允许回答');
            }
            if($answer->reward_time != null){
                throw new \Exception('悬赏答案不允许修改');
            }
            if($answer->user_id != $userId){
                throw new \Exception('不允许修改非自己的评论');
            }
        }
        $time = \date('Y-m-d H:i:s');
        $params['content'] = $answer->content."\n > 补充内容时间于{$time} \n\n {$params['content']}";
        foreach ($params  as $key=>$value){
            $answer->$key = $value;
        }
        $answer->save();
        return $answer;
    }

    /**
     * 删除答案
     * @param $id
     * @param $userId
     * @throws \Exception
     */
    public static function destroyAnswer($id,$userId)
    {
        $answer = AskAnswer::where('id',$id)->with('question')->has('question')->first();
        if($answer == null){
            throw new \Exception('数据异常');
        }
        $admin = IndexService::isHaveAdminRole($userId,$answer->question->cate_id);
        if(!$admin){
            if($answer->reward_time != null){
                throw new \Exception('悬赏答案不允许删除');
            }
            if($answer->user_id != $userId){
                throw new \Exception('不允许删除非自己的回答');
            }
        }
        //删除回答
        $answer->delete();
        //删除回答下的评论
        AskReply::where('type',3)->where('theme_id',$id)->delete();
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
           case 3:
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
     * 更新评论
     * @param $id
     * @param $params
     * @param $userId
     * @throws \Exception
     */
    public static function updateReply($id,$params,$userId)
    {
        $reply = AskReply::where('id',$id)->first();
        if($reply == null){
            throw new \Exception('数据异常');
        }
        $admin = IndexService::isHaveRole($reply,$userId,false);
        if(!$admin){
            throw new \Exception('无权限操作');
        }
        $time = \date('Y-m-d H:i:s');
        $params['content'] = $reply->content."\n > 补充内容时间于{$time} \n\n {$params['content']}";
        foreach ($params  as $key=>$value){
            $reply->$key = $value;
        }
        $reply->save();
    }
    /**
     * 删除评论
     * @param $id
     * @param $userId
     * @throws \Exception
     */
    public static function destroyReply($id,$userId)
    {
        $reply = AskReply::where('id',$id)->first();
        if($reply == null){
            throw new \Exception('数据异常');
        }
        $admin = IndexService::isHaveRole($reply,$userId,false);
        if(!$admin){
            throw new \Exception('无权限操作');
        }
        //删除评论
        $reply->delete();
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

    /**
     * 整理回答列表数据
     * @param $item
     * @param $userId
     * @param $adminRole
     * @param $question
     * @return mixed
     */
    public static function calcItem($item,$userId,$adminRole,$question)
    {
        $item->is_collection = $item->collection->count() > 0 ?1:0;
        $item->is_digg = (isset($item->digg) && $item->digg != null && $item->digg->where('conduct','up')->first() != null ) ? 1 : 0;
        $item->is_step = (isset($item->digg) && $item->digg != null && $item->digg->where('conduct','down')->first() != null ) ? 1 : 0;
        $item->show_edit = 0;
        $item->show_delete = 0;
        $item->show_reward = 0;
        $content = $item->content;
        if($question->is_private === 1){
            $content = '此回答仅提问者可以查看';
        }
        if($item->reward_balance > 0 || $item->reward_points > 0){
            $content = "需要支付<b>{$item->reward_balance}</b>元及<b>{$item->reward_points}</b>积分才可阅读";
        }

        if($item->user != null){
            $item->user_name = $item->user->nick_name;
            $item->user_avatar = $item->user->avatar_url;
            $item->user_description = $item->user->description;
        }else{
            $item->user_name = '会员不存在';
            $item->user_avatar = '';
            $item->user_description = '';
        }
        if(!empty($userId)){
            if($item->user_id == $userId){
                $item->show_edit = 1;
                $content = $item->content;
            }
            if($adminRole){
                $item->show_edit = 1;
                $item->show_delete = 1;
                $item->show_reward = 1;
                $content = $item->content;
            }
        }
        $item->content = $content;
        return $item;
    }
}