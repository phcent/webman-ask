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


use Phcent\WebmanAsk\Model\AskArticle;
use Phcent\WebmanAsk\Model\AskAnswer;
use Phcent\WebmanAsk\Model\AskReply;
use Phcent\WebmanAsk\Model\AskDigg;
use Phcent\WebmanAsk\Model\AskQuestion;

class AskDiggService
{
    /**
     * 操作
     * @param $userId
     * @param $themeId
     * @param int $type
     * @param string $conduct
     * @throws \Exception
     */
    public static function create($userId,$themeId,$type = 1,$conduct = 'up')
    {
        switch ($type){
            default: //文章
                $info = AskQuestion::where('id',$themeId)->first();
                if($info == null){
                    throw new \Exception('问题不存在');
                }
                if($info->user_id == $userId){
                    throw new \Exception('不能点评自己的提问');
                }
                break;
            case 2: //文章
                $info = AskArticle::where('id',$themeId)->first();
                if($info == null){
                    throw new \Exception('文章不存在');
                }
                if($info->user_id == $userId){
                    throw new \Exception('不能点评自己的文章');
                }
                break;
            case 3: //回答
                $info = AskAnswer::where('id',$themeId)->first();
                if($info == null){
                    throw new \Exception('回答不存在');
                }
                if($info->user_id == $userId){
                    throw new \Exception('不能点评自己的回答');
                }
                break;
            case 4: //评论
                $info = AskReply::where('id',$themeId)->first();
                if($info == null){
                    throw new \Exception('评论不存在');
                }
                if($info->user_id == $userId){
                    throw new \Exception('不能点评自己的评论');
                }
                break;
        }
        $digg = AskDigg::where('user_id',$userId)->where('theme_id',$themeId)->where('type',$type)->where('conduct',$conduct)->first();
        if($digg == null){
            AskDigg::Create([
                'user_id' => $userId,
                'type' => $type,
                'conduct' => $conduct,
                'theme_id' => $themeId
            ]);
            if($conduct == 'up'){
                $info->increment('digg_num');
            }else{
                $info->increment('step_num');
            }
        }else{
            throw new \Exception('已经操作过了');
        }

    }
    /**
     * 取消操作
     * @param $userId
     * @param $themeId
     * @param int $type
     * @param string $conduct
     * @throws \Exception
     */
    public static function delete($userId,$themeId,$type = 1,$conduct = 'up')
    {
        switch ($type){
            default: //文章
                $info = AskQuestion::where('id',$themeId)->first();
                if($info == null){
                    throw new \Exception('问题不存在');
                }
                if($info->user_id == $userId){
                    throw new \Exception('不能点评自己的提问');
                }
                break;
            case 2: //文章
                $info = AskArticle::where('id',$themeId)->first();
                if($info == null){
                    throw new \Exception('文章不存在');
                }
                if($info->user_id == $userId){
                    throw new \Exception('不能点评自己的文章');
                }
                break;
            case 3: //回答
                $info = AskAnswer::where('id',$themeId)->first();
                if($info == null){
                    throw new \Exception('回答不存在');
                }
                if($info->user_id == $userId){
                    throw new \Exception('不能点评自己的回答');
                }
                break;
            case 4: //评论
                $info = AskReply::where('id',$themeId)->first();
                if($info == null){
                    throw new \Exception('评论不存在');
                }
                if($info->user_id == $userId){
                    throw new \Exception('不能点评自己的评论');
                }
                break;
        }
        $digg = AskDigg::where('user_id',$userId)->where('theme_id',$themeId)->where('type',$type)->where('conduct',$conduct)->first();
        if($digg == null){
            throw new \Exception('已经操作过了');
        }else{
            AskDigg::destroy($digg->id);
            if($conduct == 'up'){
                $info->decrement('digg_num');
            }else{
                $info->decrement('step_num');
            }
        }

    }
}