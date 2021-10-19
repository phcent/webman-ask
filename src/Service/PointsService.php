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


use Phcent\WebmanAsk\Model\AskUser;
use Phcent\WebmanAsk\Model\SysPointsLog;
use Phcent\WebmanAsk\Model\SysUser;

class PointsService
{
    /**
     * 发布积分悬赏问题
     * @param $question
     * @param $user
     */
    public static function postReward($question,$user){
        if($question->reward_points > 0 && $question->reward_points <= $user->available_balance){
            SysPointsLog::create([
                'user_id' => $user->id,
                'user_name' => $user->nick_name,
                'available_points' => -$question->reward_points,
                'freeze_points' => $question->reward_points,
                'old_available_points' => $user->available_points,
                'old_freeze_points' => $user->freeze_points,
                'operation_stage' => 'postReward',
                'description' => '发布悬赏问题,编号：'.$question->id.'冻结积分：'.$question->reward_points
            ]);
            //减少可用余额 增加冻结余额
            $user->increment('freeze_points'); //增加
            $user->decrement('available_points');//减少
        }
    }

    /**
     * 删除回答
     * @param $askAnswer
     */
    public static function deleteAnswer($askAnswer)
    {
        $user = SysUser::where('id',$askAnswer->user_id)->first();
        $points = config('phcentask.points.publishAnswer',3);
        if($user != null){
            SysPointsLog::create([
                'user_id' => $user->id,
                'user_name' => $user->nick_name,
                'available_points' => -$points,
                'freeze_points' => 0,
                'old_available_points' => $user->available_points,
                'old_freeze_points' => $user->freeze_points,
                'operation_stage' => 'deleteAnswer',
                'description' => '删除问题回答,编号：'.$askAnswer->id.'减少积分：'. $points
            ]);
            //减少可用积分
            $user->decrement('available_points',$points);//减少
            $user->decrement('points',$points);//减少

        }
    }

    /**
     * 发布回答
     * @param $askAnswer
     */
    public static function publishAnswer($askAnswer)
    {
        $user = SysUser::where('id',$askAnswer->user_id)->first();
        $points = config('phcentask.points.publishAnswer',1);
        if($user != null){
            SysPointsLog::create([
                'user_id' => $user->id,
                'user_name' => $user->nick_name,
                'available_points' => $points,
                'freeze_points' => 0,
                'old_available_points' => $user->available_points,
                'old_freeze_points' => $user->freeze_points,
                'operation_stage' => 'publishAnswer',
                'description' => '发布问题回答,编号：'.$askAnswer->question_id.'增加积分：'. $points
            ]);
            //增加可用积分
            $user->increment('available_points',$points);//增加
            $user->increment('points',$points);//增加
        }
    }

    /**
     * 删除回复/评论
     * @param $askReply
     */
    public static function deleteReply($askReply)
    {
        $user = SysUser::where('id',$askReply->user_id)->first();
        $points = config('phcentask.points.publishReply',1);
        if($user != null){
            if($askReply->type == 1){
                $description = '删除问题回复,编号：'.$askReply->theme_id.'减少积分：'. $points;
            }else{
                $description = '删除文章评论,编号：'.$askReply->theme_id.'减少积分：'. $points;
            }

            SysPointsLog::create([
                'user_id' => $user->id,
                'user_name' => $user->nick_name,
                'available_points' => -$points,
                'freeze_points' => 0,
                'old_available_points' => $user->available_points,
                'old_freeze_points' => $user->freeze_points,
                'operation_stage' => 'deleteReply',
                'description' => $description
            ]);
            //减少可用积分
            $user->decrement('available_points',$points);//减少
            $user->decrement('points',$points);//减少
        }
    }

    /**
     * 回复/评论
     * @param $askCommentReply
     * @param $userId
     */
    public static function publishReply($askCommentReply)
    {
        $user = SysUser::where('id',$askCommentReply->user_id)->first();
        $points = config('phcentask.points.publishReply',1);
        if($user != null){
            if($askCommentReply->type == 1){
                $description = '发表问题回复,编号：'.$askCommentReply->theme_id.'增加积分：'. $points;
            }else{
                $description = '发表文章评论,编号：'.$askCommentReply->theme_id.'增加积分：'. $points;
            }
           SysPointsLog::create([
                'user_id' => $user->id,
                'user_name' => $user->nick_name,
                'available_points' => $points,
                'freeze_points' => 0,
                'old_available_points' => $user->available_points,
                'old_freeze_points' => $user->freeze_points,
                'operation_stage' => 'publishReply',
                'description' => $description
            ]);
            //增加可用积分
            $user->increment('available_points',$points);//增加
            $user->increment('points',$points);//增加
        }
    }

    /**
     * 发布问题
     * @param $askQuestion
     */
    public static function publishQuestion($askQuestion)
    {
        $user = SysUser::where('id',$askQuestion->user_id)->first();
        $points = config('phcentask.points.publishQuestion',3);
        if($user != null){
            SysPointsLog::create([
                'user_id' => $user->id,
                'user_name' => $user->nick_name,
                'available_points' => $points,
                'freeze_points' => 0,
                'old_available_points' => $user->available_points,
                'old_freeze_points' => $user->freeze_points,
                'operation_stage' => 'publishAnswer',
                'description' => '发布问题,编号：'.$askQuestion->id.'增加积分：'. $points
            ]);
            //增加可用积分
            $user->increment('available_points',$points);//增加
            $user->increment('points',$points);//增加
        }
    }

    /**
     * 发布文章
     * @param $askArticle
     */
    public static function publishArticle($askArticle)
    {
        $user = SysUser::where('id',$askArticle->user_id)->first();
        $points = config('phcentask.points.publishArticle',5);
        if($user != null){
            SysPointsLog::create([
                'user_id' => $user->id,
                'user_name' => $user->nick_name,
                'available_points' => $points,
                'freeze_points' => 0,
                'old_available_points' => $user->available_points,
                'old_freeze_points' => $user->freeze_points,
                'operation_stage' => 'publishAnswer',
                'description' => '发布文章,编号：'.$askArticle->id.'增加积分：'. $points
            ]);
            //增加可用积分
            $user->increment('available_points',$points);//增加
            $user->increment('points',$points);//增加
        }
    }


    /**
     * 采纳最佳答案
     * @param $question
     * @param $userId
     * @throws \Exception
     */
    public static function bestAnswer($question,$userId)
    {
        $user = SysUser::where('id',$question->user_id)->first();
        if($user == null){
            throw new \Exception('问题作者异常');
        }
        SysPointsLog::create([
            'user_id' => $user->id,
            'user_name' => $user->nick_name,
            'available_points' => 0,
            'freeze_points' => $question->reward_points,
            'old_available_points' => $user->available_points,
            'old_freeze_points' => $user->freeze_points,
            'operation_stage' => 'bestAnswer',
            'description' => '悬赏问题,编号：'.$question->id.',采纳最佳答案,扣除冻结积分：'.$question->reward_points
        ]);
        //减少冻结积分
        $user->decrement('freeze_points',$question->freeze_points); //直接减少冻结积分

        $bestUser = SysUser::where('id',$userId)->first();
        if($bestUser != null){
            $commission = config('phcentask.pointsCommission',0);
            $reward_points = $question->reward_points;
            if($commission > 0){
                $reward_points = bcdiv(bcmul($question->reward_points,$commission,0),100,2);
            }
            if($reward_points > 0){
                SysPointsLog::create([
                    'user_id' => $bestUser->id,
                    'user_name' => $bestUser->nick_name,
                    'available_points' => 0,
                    'freeze_points' => $reward_points,
                    'old_available_points' => $bestUser->available_points,
                    'old_freeze_points' => $bestUser->freeze_points,
                    'operation_stage' => 'bestAnswer',
                    'description' => '悬赏问题,编号：'.$question->id.',采纳最佳答案获得积分分成：'.$reward_points
                ]);
            }
        }

    }
}