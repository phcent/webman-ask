<?php
/**
 *-------------------------------------------------------------------------p*
 * 问答会员管理
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
use support\Request;

class UserController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\AskUser::class;
    public  $name = '会员';
    public  $projectName = '问答管理-会员管理-';
    public function beforeAdminUpdate($user, $id)
    {
        $params = phcentParams([
            'question_num',
            'answer_num',
            'article_num',
            'answer_num',
            'collection_num',
            'follow_num',
            'view_num',
            'fans_num',
            'expert_status',
            'grade_id',
            'answer_best_num',
            'description',
            'hot_sort',
            'is_admin',
            'user_name',
        ]); //允许修改字段
        return $params; // TODO: Change the autogenerated stub
    }
    public function create(Request $request)
    {
        return []; // TODO: Change the autogenerated stub
    }
    public function destroy(Request $request, $id)
    {
        return []; // TODO: Change the autogenerated stub
    }
}