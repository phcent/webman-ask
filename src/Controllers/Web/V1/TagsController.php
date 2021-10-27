<?php
/**
 *-------------------------------------------------------------------------p*
 * 话题接口
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

use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\AskFollower;
use Phcent\WebmanAsk\Model\AskQuestion;
use Phcent\WebmanAsk\Model\AskTags;
use Phcent\WebmanAsk\Model\AskTagsQa;
use Phcent\WebmanAsk\Service\CategoryService;
use Phcent\WebmanAsk\Service\IndexService;
use Phcent\WebmanAsk\Service\TagsService;
use Respect\Validation\Validator;
use support\Db;
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
            $params = phcentParams(['name_like','cate_id']);
            $askTags = phcentWhereParams($askTags,$params);
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askTags = $askTags->orderBy(request()->input('sortName'),request()->input('sortOrder'));
            }else{
                $askTags = $askTags->orderBy('id','desc');
            }
            $list  = $askTags->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));

            $data['list'] = $list->items();
            $data['categoryList'] = CategoryService::getCategoryList(3);
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
            phcentMethod(['GET']);
            $siteId = $request->siteId;
            $info = AskTags::where('id',$id)->where('site_id',$siteId)->first();
            if($info == null){
                throw new \Exception('话题不存在,或已被删除');
            }
            $data['info'] = $info;
            $data['is_follower'] = 0; //是否收藏
            $data['show_delete'] = 0; //是否显示删除
            $data['show_edit'] = 0; //是否显示补充问题
            $userId = AuthLogic::getInstance()->userId();
            if(!empty($userId)){
                //判断是否关注
                $isFollower = AskFollower::where('user_id',$userId)->where('tags_id',$info->id)->first();
                if($isFollower != null){
                    $data['is_follower'] = 1; //是否关注
                }
                $adminRole = IndexService::isHaveAdminRole($userId,$info->cate_id);
                if($adminRole){
                    $data['show_delete'] = 1; //是否显示删除
                    $data['show_edit'] = 1; //是否显示补充问题
                }
            }
            $data['hotQuestion'] = IndexService::getHotQuestion();
            $data['hotArticle'] = IndexService::getHotArticle();
            $data['hotTags'] = IndexService::getHotTags();
//            $data['hotExpert'] = IndexService::getExpertOnline();
//            $data['newQuestion'] = IndexService::getNewQuestion();
            return phcentSuccess($data);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 问题列表
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function list(Request $request,$id)
    {
        try {
            phcentMethod(['GET']);
            if(!is_numeric($id) || empty($id)){
                throw new \Exception('编号错误');
            }
            $siteId = $request->siteId;
            $askTagsQa = new AskTagsQa();
            if($request->input('type','question') == 'question'){
                $list = $askTagsQa->where('question_id','>',0)->where('site_id',$siteId)->where('tag_id',$id)->where('article_id','<',1)->with(['question.tags','question.user'])->has('question')->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
                $list->map(function ($item){
                    if($item->question->tags != null){
                        $item->question->tags->map(function ($item2){
                            $item2->setVisible(['id','name']);
                        });
                    }
                    if($item->question->user == null){
                        $item->question->user_name = '异常';
                    }else{
                        $item->question->user_name = $item->question->user->nick_name;
                    }
                    $item->question->setHidden(['user']);
                    $item->setHidden(['article_id','updated_at','site_id']);
                });
                $data['list'] = $list->items();
            }else{
                $list  = $askTagsQa->where('article_id','>',0)->where('site_id',$siteId)->where('tag_id',$id)->where('question_id','<',1)->with(['article.tags','article.user'])->has('article')->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
                $list->map(function ($item){
                    if($item->article->tags != null){
                        $item->article->tags->map(function ($item2){
                            $item2->setVisible(['id','name']);
                        });
                    }
                    if($item->article->user == null){
                        $item->user_name = '异常';
                    }else{
                        $item->user_name = $item->article->user->nick_name;
                    }
                    $item->article->setHidden(['user']);
                    $item->setHidden(['question_id','updated_at','site_id']);
                });
                $data['list'] = $list->items();
            }
            return phcentSuccess($data,'问题列表',[ 'page' => $list->currentPage(),'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 修改话题
     * @param Request $request
     * @param $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function update(Request $request,$id)
    {
        try {
            phcentMethod(['GET','PUT']);
            if(!is_numeric($id) || empty($id)){
                throw new \Exception('编号错误');
            }
            $userId = AuthLogic::getInstance()->userId();
            if(empty($userId)){
                throw new \Exception('请先登入');
            }
            if($request->method() == 'GET'){

                $data['cateList'] = CategoryService::getCategoryList(3);
                $info = AskTags::where('id',$id)->first();

                if($info == null){
                    throw new \Exception('话题不存在');
                }
                $data['info'] = $info;
                return phcentSuccess($data);
            }else{
                Validator::input($request->all(), [
                    'name' => Validator::length(1, 32)->noWhitespace()->setName('话题名称'),
                ]);
                $params = phcentParams(['name','icon','image','summary','seo_title','seo_keyword','seo_description']);
                TagsService::updateTags($params,$id,$userId);
                return phcentSuccess();
            }
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }
}