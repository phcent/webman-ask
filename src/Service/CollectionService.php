<?php
/**
 *-------------------------------------------------------------------------p*
 * 收藏操作
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


use Phcent\WebmanAsk\Model\AskAnswer;
use Phcent\WebmanAsk\Model\AskArticle;
use Phcent\WebmanAsk\Model\AskCollection;
use Phcent\WebmanAsk\Model\AskQuestion;

class CollectionService
{
    /**
     * 新增收藏
     * @param $userId
     * @param $themeId
     * @param $type
     * @throws \Exception
     */
    public static function createCollection($userId,$themeId,$type)
    {
        switch ($type){
            case 1: //问题
                $question = AskQuestion::where('id',$themeId)->first();
                if($question == null){
                    throw new \Exception('问题不存在');
                }
                AskCollection::create([
                    'user_id' => $userId,
                    'theme_id' => $themeId,
                    'type' => $type,
                    'title' => $question->title,
                 //   'content' => $question->content,
                ]);
                $question->increment('collection_num');
                AskUserService::optionsNum($userId,'collection');
                //增加会员动态
                DynamicService::create([
                    'user_id' => $userId,
                    'type' => 1,
                    'item_id' => $question->id,
                    'operation_stage' => 'collection',
                    'title' => $question->title,
                    'content' => ''
                ]);
                break;
            case 2: //文章
                $article = AskArticle::where('id',$themeId)->first();
                if($article == null){
                    throw new \Exception('文章不存在');
                }
                AskCollection::create([
                    'user_id' => $userId,
                    'theme_id' => $themeId,
                    'type' => $type,
                    'title' => $article->title,
              //      'content' => $article->content,
                ]);
                $article->increment('collection_num');
                AskUserService::optionsNum($userId,'collection');
                //增加会员动态
                DynamicService::create([
                    'user_id' => $userId,
                    'type' => 2,
                    'item_id' => $article->id,
                    'operation_stage' => 'collection',
                    'title' => $article->title,
                    'content' => ''
                ]);
                break;
            case 3: //回答
                $answer = AskAnswer::where('id',$themeId)->first();
                if($answer == null){
                    throw new \Exception('回答不存在');
                }
                $question = AskQuestion::where('id',$answer->question_id)->first();
                if($question == null){
                    throw new \Exception('问题不存在');
                }
                AskCollection::create([
                    'user_id' => $userId,
                    'theme_id' => $themeId,
                    'type' => $type,
                    'title' =>$question->title,
                     'content' => $answer->content,
                ]);
                $answer->increment('collection_num');
                AskUserService::optionsNum($userId,'collection');
                //增加会员动态
                DynamicService::create([
                    'user_id' => $userId,
                    'type' => 3,
                    'item_id' => $answer->id,
                    'operation_stage' => 'collection',
                    'title' => $answer->content,
                    'content' => $answer->content
                ]);
                break;
            default:
                throw new \Exception('数据异常');
                break;
        }
    }

    /**
     * 取消收藏
     * @param $userId
     * @param $themeId
     * @param $type
     * @throws \Exception
     */
    public static function deleteCollection($userId,$themeId,$type)
    {

        $collection = AskCollection::where('user_id',$userId)->where('theme_id',$themeId)->where('type',$type)->first();
        if($collection == null){
            throw new \Exception('收藏数据异常');
        }
        $collection->delete();

        switch ($type){
            case 1: //问题
                $question = AskQuestion::where('id',$themeId)->first();
                if($question == null){
                    throw new \Exception('问题不存在');
                }
                $question->decrement('collection_num');
                AskUserService::optionsNum($userId,'collection','del');
                break;
            case 2: //文章
                $article = AskArticle::where('id',$themeId)->first();
                if($article == null){
                    throw new \Exception('文章不存在');
                }
                $article->decrement('collection_num');
                AskUserService::optionsNum($userId,'collection','del');
                break;
            case 3: //回答
                $answer = AskAnswer::where('id',$themeId)->first();
                if($answer == null){
                    throw new \Exception('回答不存在');
                }
                $answer->decrement('collection_num');
                AskUserService::optionsNum($userId,'collection','del');
                break;
            default:
                throw new \Exception('数据异常');
                break;
        }
    }
}