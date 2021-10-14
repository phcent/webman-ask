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


namespace Phcent\WebmanAsk\Logic;



use Phcent\WebmanAsk\Model\AskArticle;
use Phcent\WebmanAsk\Model\AskAnswer;
use Phcent\WebmanAsk\Model\AskReply;
use Phcent\WebmanAsk\Model\AskQuestion;
use Phcent\WebmanAsk\Service\AskUserService;
use Phcent\WebmanAsk\Service\PointsService;

class AskCommentLogic
{
    /**
     * 删除回答
     * @param $id
     * @throws \Exception
     */
    public static function deleteAnswer($id)
    {
        $askComment = AskAnswer::where('id',$id)->withTrashed()->first();
        if($askComment == null){
            throw new \Exception('回答不存在');
        }
        PointsService::deleteAnswer($askComment);
        //删除回复
        $askCommentReply = AskReply::where('theme_id',$askComment->id)->where('type',1)->withTrashed()->get();
        foreach ($askCommentReply as $item){
            PointsService::deleteReply($item);
            //减少评论数量
            AskUserService::optionsNum($item->user_id,'reply','del');
            $item->forceDelete();
        }
        $askComment->forceDelete();
    }

    /**
     * 删除评论
     * @param $id
     * @throws \Exception
     */
    public static function deleteReply($id)
    {
        $askReply = AskReply::where('id',$id)->withTrashed()->first();
        PointsService::deleteReply($askReply);
        //减少评论数量
        AskUserService::optionsNum($askReply->user_id,'reply','del');
        $askReply->forceDelete();
    }

    /**
     * 删除问题
     * @param $id
     * @throws \Exception
     */
    public static function deleteQuestion($id)
    {
        $askQuestion = AskQuestion::where('id',$id)->withTrashed()->first();
        if($askQuestion == null){
            throw new \Exception('回答不存在');
        }
        //减少问题数量
        AskUserService::optionsNum($askQuestion->user_id,'question','del');

        //删除问答
        $askAnswer = AskAnswer::where('question_id',$id)->withTrashed()->get();
        if($askAnswer->count() > 0){
            $themeId = $askAnswer->pluck('id');
            foreach ($askAnswer as $item){
                PointsService::deleteAnswer($item);
                //减少回答数量
                AskUserService::optionsNum($askAnswer->user_id,'answer','del');
                $item->forceDelete();
            }
            //删除回复
            $askReply = AskReply::whereIn('theme_id',$themeId)->where('type',1)->withTrashed()->get();
            foreach ($askReply as $item){
                PointsService::deleteReply($item);
                //减少评论数量
                AskUserService::optionsNum($askReply->user_id,'reply','del');
                $item->forceDelete();
            }
        }
        $askQuestion->forceDelete();

    }

    /**
     * 删除文章
     * @param $id
     * @throws \Exception
     */
    public static function deleteArticle($id)
    {
        $askArticle  = AskArticle::where('id',$id)->withTrashed()->first();
        if($askArticle == null){
            throw new \Exception('回答不存在');
        }
        //减少文章数量
        AskUserService::optionsNum($askArticle->user_id,'article','del');

        //删除回复
        $askReply = AskReply::where('theme_id',$id)->where('type',1)->withTrashed()->get();
        foreach ($askReply as $item){
            PointsService::deleteReply($item);
            //减少评论数量
            AskUserService::optionsNum($askReply->user_id,'reply','del');
            $item->forceDelete();
        }
        $askArticle->forceDelete();
    }
}