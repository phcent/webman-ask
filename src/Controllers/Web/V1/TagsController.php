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


namespace Phcent\WebmanAsk\Controllers\Web\V1;


use Phcent\WebmanAsk\Model\AskCollection;
use Phcent\WebmanAsk\Model\AskTags;
use Phcent\WebmanAsk\Service\IndexService;
use support\Request;

class TagsController
{
    /**
     * 获取tag列表
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        try {
            $askTags = new AskTags();
            $params = phcentParams(['page' => 1,'limit' =>10,'name_like','cate_id']);
            $askTags = phcentWhereParams($askTags,$params);
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askTags = $askTags->orderBy(request()->input('sortName'),request()->input('sortOrder'));
            }else{
                $askTags = $askTags->orderBy('id','desc');
            }
            $list  = $askTags->paginate($params['limit']);

            $data['list'] = $list->items();
            return phcentSuccess($data,'话题列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 获取话题详情
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function show(Request $request,$id)
    {
        try {
            $info = AskQuestion::where('id',$id)->first();
            if($info == null){
                throw new \Exception('问题不存在,或已被删除');
            }
            $info->increment('view_num');
            $info->save();
            $data['info'] = $info;
            $data['is_collection'] = 0; //是否收藏
            $data['show_close'] = 0; //是否显示关闭
            $data['show_delete'] = 0; //是否显示删除
            $data['show_edit'] = 0; //是否显示补充问题
            $data['show_reward'] = 0; //是否显示追加悬赏
            $data['show_set'] = 0; //是否显示设置

            $user = AuthLogic::getInstance()->user();
            $userId = 0;
            if($user != null){
                $userId = $user->id;
                //判断是否收藏
                $isCollection = AskCollection::where('user_id',$user->id)->where('type',1)->where('theme_id',$info->id)->first();
                if($isCollection != null){
                    $data['is_collection'] = 1; //是否收藏
                }

                if($info->user_id == $user->id){
                    $data['show_edit'] = 1; //是否显示补充问题
                    $data['show_reward'] = 1; //是否显示追加悬赏
                }
                $adminRole = IndexService::isHaveAdminRole($user->id,$info->cate_id);
                if($adminRole){
                    $data['show_close'] = 1; //是否显示关闭
                    $data['show_delete'] = 1; //是否显示删除
                    $data['show_set'] = 1; //是否显示设置
                    $data['show_edit'] = 1; //是否显示补充问题
                    $data['show_reward'] = 1; //是否显示追加悬赏
                }
            }
            $data['userCard'] = IndexService::getUserCard($id,$userId);

            $data['hotQuestion'] = IndexService::getHotQuestion();
            $data['hotArticle'] = IndexService::getHotArticle();
            $data['hotTags'] = IndexService::getHotTags();
            $data['hotExpert'] = IndexService::getExpertOnline();
            $data['newQuestion'] = IndexService::getNewQuestion();

            $data['reasonList'] = config('phcentask.reasonList');
            $data['addPoints'] =  config('phcentask.addPoints');
            $data['addBalance'] =  config('phcentask.addBalance');

            return phcentSuccess($data);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 问题列表
     * @param Request $request
     * @param $id
     */
    public function question(Request $request,$id)
    {

    }

    /**
     * 文章列表
     * @param Request $request
     * @param $id
     */
    public function article(Request $request,$id)
    {

    }
}