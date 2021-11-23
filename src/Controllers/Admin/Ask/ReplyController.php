<?php
/**
 *-------------------------------------------------------------------------p*
 * 问答评论
 *-------------------------------------------------------------------------h*
 * @copyright  Copyright (c) 2015-2021 Phcent Inc. (http://www.phcent.com)
 *-------------------------------------------------------------------------c*
 * @license    http://www.phcent.com        p h c e n t . c o m
 *-------------------------------------------------------------------------e*
 * @link       http://www.phcent.com
 *-------------------------------------------------------------------------n*
 * @since      象讯·PHP知识付费问答系统
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Controllers\Admin\Ask;

use Phcent\WebmanAsk\Logic\AdminControllerLogic;
use Phcent\WebmanAsk\Logic\AskCommentLogic;
use Phcent\WebmanAsk\Service\ConfigService;
use Respect\Validation\Validator;
use support\Request;

class ReplyController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\AskReply::class;
    public  $name = '评论';
    public  $projectName = '问答管理-评论管理-';

    public function create(Request $request)
    {
        return []; // TODO: Change the autogenerated stub
    }
    public function afterAdminIndex($list)
    {
        $data['list'] = $list->items();
        $data['categoryType'] = ConfigService::getKeyName([2,3],config('phcentask.allType'));
        return $data;
    }
    public function insertGetAdminUpdate($info, $id)
    {
        $data['categoryType'] = ConfigService::getKeyName([2,3],config('phcentask.allType'));
        $data['info'] = $info;
        return $data; // TODO: Change the autogenerated stub
    }
    public function beforeAdminUpdate($user, $id)
    {
        Validator::input(\request()->all(), [
            'content' => Validator::length(3, 10000)->noWhitespace()->setName('内容'),
            'theme_id' => Validator::digit()->min(1)->setName('来源编号'),
            'type' => Validator::digit()->in([2,3])->setName('类型'),
            'status' => Validator::digit()->in([1,0])->setName('状态'),
        ]);
        $params = phcentParams([
            'theme_id',
            'user_id',
            'content',
            'reply_user_id',
            'type',
            'digg_num',
            'step_num',
            'share_num',
            'report_num',
            'collection_num',
            'thank_num',
            'reply_num',
            'status'
        ]);
        return $params;
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
        if(is_numeric($id) && empty($id)){
            $list = (new $this->model)->onlyTrashed()->get();
        }else{
            $list =(new $this->model)->whereIn($this->key,$ids)->onlyTrashed()->get();
        }
        foreach ($list as $item) {
            AskCommentLogic::deleteReply($item->id);
        }
    }
}