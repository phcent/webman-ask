<?php
/**
 *-------------------------------------------------------------------------p*
 * 问答文章模块
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
use Phcent\WebmanAsk\Model\AskCategory;
use Phcent\WebmanAsk\Model\AskReply;

use Phcent\WebmanAsk\Service\CategoryService;
use support\Request;

class ArticleController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\AskArticle::class;
    public  $name = '文章';
    public  $projectName = '问答管理-文章管理-';
    /**
     * 获取数据之前
     * @param $model
     * @return mixed
     */
    function beforeAdminIndex($model){
        $model = $model->with(['user']);
        return $model;
    }

    public function afterAdminIndex($list)
    {
        $list->map(function ($item){
            $item->user_name = $item->user != null ? $item->user->nick_name:'';
            $item->setHidden(['user']);
        });
        $data['list'] = $list->items();
        $data['categoryList'] = CategoryService::getCategoryList(2);
        return $data;
    }

    /**
     * 新增数据 支持get,post类型
     * @param Request $request
     * @return \support\Response
     */
    public function create(Request $request)
    {
        return phcentError( );
    }
   public function insertGetAdminUpdate($info, $id)
   {
       $data['category'] = CategoryService::getCategoryList(2);
       $data['info'] = $info;
       return $data;
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
           'report_num',
           'collection_num',
           'thank_num',
           'reply_num',
           'pay_num',
           'share_num',
           'reward_balance',
           'reward_points',
           'sort',
           'seo_title',
           'seo_keyword',
           'seo_description',
           'status'
       ]); //允许修改字段
       return $params;
   }
   public function adminRecoveryDelete($user, $ids, $id)
   {
       foreach ($ids as $value) {
           AskCommentLogic::deleteArticle($value);
       }
   }
   public function adminDestroy($user, $ids, $id)
   {
       (new $this->model)->destroy($ids);
       //删除评论
       $askCommentReply = AskReply::whereIn('theme_id',$ids)->where('type',2)->get();
       AskReply::destroy($askCommentReply->pluck('id'));
   }
}