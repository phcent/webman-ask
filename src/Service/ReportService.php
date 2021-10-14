<?php
/**
 *-------------------------------------------------------------------------p*
 * 举报管理
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


use Phcent\WebmanAsk\Model\AskArticle;
use Phcent\WebmanAsk\Model\AskAnswer;
use Phcent\WebmanAsk\Model\AskReply;
use Phcent\WebmanAsk\Model\AskQuestion;
use Phcent\WebmanAsk\Model\AskReport;
use Phcent\WebmanAsk\Model\AskTags;

class ReportService
{
    /**
     * 新增举报
     * @param $params
     * @param $userId
     * @throws \Exception
     */
    public static function create($params,$userId)
    {
        $content = '';
        switch ($params['type']){
            case 1:
                $info = AskQuestion::where('id',$params['theme_id'])->first();
                if($info == null){
                    throw new \Exception('问题不存在');
                }
                $info->increment('report_num');
                $content = $info->title;
                break;
            case 2:
                $info = AskArticle::where('id',$params['theme_id'])->first();
                if($info == null){
                    throw new \Exception('文章不存在');
                }
                $info->increment('report_num');
                $content = $info->title;
                break;
            case 3:
                $info = AskAnswer::where('id',$params['theme_id'])->first();
                if($info == null){
                    throw new \Exception('回答不存在');
                }
                $info->increment('report_num');
                $content = $info->content;
                break;
            case 4:
                $info = AskReply::where('id',$params['theme_id'])->first();
                if($info == null){
                    throw new \Exception('评论不存在');
                }
                $info->increment('report_num');
                $content = $info->content;
                break;
            case 5:
                $info = AskTags::where('id',$params['theme_id'])->first();
                if($info == null){
                    throw new \Exception('话题不存在');
                }
                $info->increment('report_num');
                $content = $info->title;
                break;
        }
        AskReport::create([
            'reason' => $params['reason'],
            'theme_id' => $params['theme_id'],
            'type' => $params['type'],
            'content' =>$content,
            'ip' => request()->getRealIp(),
            'user_agent' => request()->header('user-agent',''),
            'user_id' => $userId,
        ]);
    }
}