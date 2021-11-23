<?php
/**
 *-------------------------------------------------------------------------p*
 * 问答问题模块
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


namespace Phcent\WebmanAsk\Controllers\Admin\Ask;

use Phcent\WebmanAsk\Logic\AdminControllerLogic;
use Phcent\WebmanAsk\Logic\AskCommentLogic;
use Phcent\WebmanAsk\Model\AskAnswer;
use Phcent\WebmanAsk\Model\AskCategory;
use Phcent\WebmanAsk\Model\AskReply;

use Phcent\WebmanAsk\Service\CategoryService;
use support\Request;

class QuestionController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\AskQuestion::class;
    public  $name = '问题';
    public  $projectName = '问答管理-问题管理-';

    public function afterAdminIndex($list)
    {
        $data['list'] = $list->items();
        $data['categoryList'] = CategoryService::getCategoryList(1);
        return $data;
    }

    /**
     * 新增数据 支持get,post类型
     * @param Request $request
     * @return \support\Response
     */
    public function create(Request $request)
    {
        return phcentError();

    }
    public function insertGetAdminUpdate($info, $id)
    {
        $data['info'] = $info;
        $data['category'] = AskCategory::where('type',1)->get();
        return $data; // TODO: Change the autogenerated stub
    }
    public function beforeAdminUpdate($user, $id)
    {
        $params = phcentParams([
            'title',
            'content',
            'user_id',
            'cate_id',
            'hot_sort',
            'top_sort',
            'style',
            'digg_num',
            'step_num',
            'view_num',
            'follow_num',
            'report_num',
            'collection_num',
            'thank_num',
            'answer_num',
            'reply_num',
            'best_answer',
            'reward_balance',
            'reward_points',
            'reward_time',
            'keyword',
            'description',
            'status'
        ]); //允许修改字段
        return $params;
    }

    /**
     * 删除问题
     * @param $user
     * @param $ids
     * @param $id
     * @return array|void
     */
    public function adminDestroy($user, $ids, $id)
    {
        (new $this->model)->destroy($ids);
        //删除回答
        $askComment = AskAnswer::whereIn('question_id', $ids)->get();
        AskAnswer::destroy($askComment->pluck('id'));

        //删除评论
        $askCommentReply = AskReply::whereIn('theme_id', $askComment->pluck('id'))->where('type', 1)->get();
        AskReply::destroy($askCommentReply->pluck('id'));
    }

    /**
     * 彻底删除
     * @param $user
     * @param $ids
     * @param $id
     * @throws \Exception
     */
    public function adminRecoveryDelete($user, $ids, $id)
    {
        foreach ($ids as $value) {
            AskCommentLogic::deleteQuestion($value);
        }
    }
}