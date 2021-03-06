<?php
/**
 *-------------------------------------------------------------------------p*
 * 关注管理
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


use Phcent\WebmanAsk\Model\AskFollower;
use Phcent\WebmanAsk\Model\AskQuestion;

class FollowerService
{
    /**
     * 关注
     * @param $userId
     * @param $themeId
     * @param $type
     * @throws \Exception
     */
    public static function createFollower($userId,$themeId,$type)
    {
        if($type == 'user'){
            $follower = AskFollower::where('user_id',$userId)->where('theme_id',$themeId)->where('type',7)->first();
            if($follower != null){
                throw new \Exception('你已经关注过了');
            }
            AskFollower::create([
                'user_id' => $userId,
                'theme_id' => $themeId,
                'type'=>7
            ]);
            //增加会员关注数量
            AskUserService::optionsNum($userId,'follow');
            AskUserService::optionsNum($themeId,'fans');
        }else{
            $follower = AskFollower::where('user_id',$userId)->where('type',1)->where('theme_id',$themeId)->first();
            if($follower != null){
                throw new \Exception('你已经关注过了');
            }
            $question = AskQuestion::where('id',$themeId)->first();
            if($question == null){
                throw new \Exception('问题信息异常');
            }
            AskFollower::create([
                'user_id' => $userId,
                'theme_id' => $themeId,
                'type' => 1
            ]);
            $question->increment('follow_num');
             //增加会员关注数量
            AskUserService::optionsNum($userId,'follow');

            //增加会员动态
            DynamicService::create([
                'user_id' => $userId,
                'type' => 1,
                'item_id' => $themeId,
                'operation_stage' => 'follow',
                'title' => $question->title,
                'content' => ''
            ]);
        }

    }

    /**
     * 取消关注
     * @param $userId
     * @param $themeId
     * @param $type
     * @throws \Exception
     */
    public static function deleteFollower($userId,$themeId,$type)
    {
        if($type == 'user'){
            $follower = AskFollower::where('user_id',$userId)->where('type',7)->where('theme_id',$themeId)->first();
            if($follower == null){
                throw new \Exception('你已经取消关注了');
            }
            AskFollower::destroy($follower->id);
            //增加会员关注数量
            AskUserService::optionsNum($userId,'follow','del');
            AskUserService::optionsNum($themeId,'fans','del');

        }else{
            $follower = AskFollower::where('user_id',$userId)->where('type',1)->where('theme_id',$themeId)->first();
            if($follower == null){
                throw new \Exception('你已经取消关注了');
            }
            $question = AskQuestion::where('id',$themeId)->first();
            if($question == null){
                throw new \Exception('问题信息异常');
            }
            AskFollower::destroy($follower->id);
            $question->decrement('follow_num');
            //增加会员关注数量
            AskUserService::optionsNum($userId,'follow','del');
        }
    }
}