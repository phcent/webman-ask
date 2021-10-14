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


use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\AskArticle;
use Phcent\WebmanAsk\Model\AskCategory;
use Phcent\WebmanAsk\Model\AskCollection;
use Phcent\WebmanAsk\Model\AskDigg;
use Phcent\WebmanAsk\Model\AskTags;
use Phcent\WebmanAsk\Model\AskTagsQa;
use Phcent\WebmanAsk\Service\ArticleService;
use Phcent\WebmanAsk\Service\CategoryService;
use Phcent\WebmanAsk\Service\IndexService;
use Respect\Validation\Validator;
use support\Db;
use support\Request;

class ArticleController
{
    /**
     * 查询文章列表
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $askArticle = new AskArticle();
            $params = phcentParams(['page' => 1,'limit' =>10,'cate_id']);
            $askArticle = phcentWhereParams($askArticle,$params);
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askArticle = $askArticle->orderBy(request()->input('sortName'),request()->input('sortOrder'));
            }else{
                $askArticle = $askArticle->orderBy('id','desc');
            }
            $list  = $askArticle->with(['user','tags'])->paginate($params['limit']);
            $list->map(function ($item){
                if($item->tags != null){
                    $item->tags->map(function ($item2){
                        $item2->setVisible(['id','name']);
                    });
                }
                if($item->user == null){
                    $item->user_name = '异常';
                }else{
                    $item->user_name = $item->user->nick_name;
                }
                $item->setHidden(['user']);
            });
            $data['list'] = $list->items();
            $data['categoryList'] = CategoryService::getCategoryList(2);
            $data['hotQuestion'] = IndexService::getHotQuestion();
            $data['hotArticle'] = IndexService::getHotArticle();
            $data['hotTags'] = IndexService::getHotTags();
            $data['hotExpert'] = IndexService::getExpertOnline();
            $data['newQuestion'] = IndexService::getNewQuestion();

            return phcentSuccess($data,'文章列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }
    /**
     * 获取文章详情
     * @param $id
     * @param Request $request
     * @return
     */
    public function show(Request $request,$id)
    {
        try {
            phcentMethod(['GET']);
            $info = AskArticle::where('id',$id)->with('tags')->first();
            if($info == null){
                throw new \Exception('文章不存在,或已被删除');
            }
            $info->increment('view_num');

            if($info->tags != null){
                $info->tags->map(function ($item){
                    $item->setVisible(['id','name']);
                });
            }

            $data['info'] = $info;
            $data['is_collection'] = 0; //是否收藏
            $data['show_delete'] = 0; //是否显示删除
            $data['show_edit'] = 0; //是否显示补充问题
            $data['show_set'] = 0; //是否显示设置
            $data['is_digg'] = 0; //是否顶过
            $data['is_step'] = 0; //是否踩过
            $userId = AuthLogic::getInstance()->userId();
            if(!empty($userId)){
                //判断是否收藏
                $isCollection = AskCollection::where('user_id',$userId)->where('type',2)->where('theme_id',$info->id)->first();
                if($isCollection != null){
                    $data['is_collection'] = 1; //是否收藏
                }
                $digg = AskDigg::where('user_id',$userId)->where('type',2)->where('theme_id',$id)->get();
                $isDigg = $digg->where('conduct','up')->first();
                $isStep = $digg->where('conduct','down')->first();
                if($isDigg != null){
                    $data['is_digg'] = 1;
                }
                if($isStep != null){
                    $data['is_step'] = 1;
                }


                if($info->user_id == $userId){
                    $data['show_edit'] = 1; //是否显示补充文章
                }
                $adminRole = IndexService::isHaveAdminRole($userId,$info->cate_id);
                if($adminRole){
                    $data['show_delete'] = 1; //是否显示删除
                    $data['show_set'] = 1; //是否显示设置
                    $data['show_edit'] = 1; //是否显示补充问题
                }
            }
            $data['userCard'] = IndexService::getUserCard($info->user_id,$userId);

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
     * 新增问题
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function create(Request $request)
    {
        try {
            phcentMethod(['GET','POST']);
            if($request->method() == 'GET'){
                $userId = AuthLogic::getInstance()->userId();
                if(empty($userId)){
                    throw new \Exception('请先登入');
                }
                $data['cateList'] = AskCategory::where('type',2)->get();
                $data['recommendBalance'] =config('phcentask.recommendBalance');
                $data['recommendPoints'] = config('phcentask.recommendPoints');
                return phcentSuccess($data);
            }else{
                Validator::input($request->post(), [
                    'title' => Validator::length(1, 32)->noWhitespace()->setName('文章标题'),
                    'content' => Validator::length(10,10000)->setName('文章内容'),
                    'cate_id' => Validator::digit()->min(1)->setName('文章分类'),
                ]);
                $params = phcentParams(['title','content','cate_id'=>0,'reward_balance'=>0,'reward_points'=>0,'keyword'=>'','description'=>'','tags']);
                $userId = AuthLogic::getInstance()->userId();
                if(empty($userId)){
                    throw new \Exception('请先登入');
                }
                Db::connection()->beginTransaction();
                $question = ArticleService::createArticle($params,$userId);
                Db::connection()->commit();
                return phcentSuccess($question);
            }

        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }

    }

    /**
     * 修改问题
     * @param Request $request
     * @param $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function update(Request $request,$id)
    {
        try {
            phcentMethod(['GET','PUT']);
            if($request->method() == 'GET'){
                $userId = AuthLogic::getInstance()->userId();
                if(empty($userId)){
                    throw new \Exception('请先登入');
                }
                $data['cateList'] = AskCategory::where('type',2)->get();
                $info = AskArticle::where('id',$id)->with('tags')->first();
                if($info == null){
                    throw new \Exception('文章不存在');
                }
                if($info->tags != null){
                    $info->tags->map(function ($item){
                        $item->setVisible(['id','name']);
                    });
                }
                $data['info'] = $info;
                return phcentSuccess($data);
            }else{
                Validator::input($request->all(), [
                    'title' => Validator::length(1, 32)->noWhitespace()->setName('提问标题'),
                    'content' => Validator::length(10,10000)->setName('提问内容'),
                    'cate_id' => Validator::digit()->min(1)->setName('问题分类'),
                ]);
                $params = phcentParams(['title','content','cate_id'=>0,'keyword'=>'','description'=>'','reward_balance','reward_points','is_private'=>2,'tags']);
                $userId = AuthLogic::getInstance()->userId();
                if(empty($userId)){
                    throw new \Exception('请先登入');
                }
                Db::connection()->beginTransaction();
                ArticleService::updateArticle($params,$id,$userId);
                Db::connection()->commit();
                return phcentSuccess();
            }

        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }

    /**
     * 删除文章
     * @param Request $request
     * @param $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function destroy(Request $request,$id)
    {
        try {
            phcentMethod(['DELETE']);
            if(!is_numeric($id) && empty($id)){
                throw new \Exception('参数错误');
            }
            $userId = AuthLogic::getInstance()->userId();
            if(empty($userId)){
                throw new \Exception('请先登入');
            }
            Db::connection()->beginTransaction();
            ArticleService::destroyArticle($id,$userId);
            Db::connection()->commit();
            return phcentSuccess([],'删除成功');
        } catch (\Exception $e) {
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }


    /**
     * 问题样式设置
     * @param Request $request
     * @param $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function config(Request $request,$id)
    {
        try {
            phcentMethod(['GET','POST']);
            if(!is_numeric($id) && empty($id)){
                throw new \Exception('参数错误');
            }
            $userId = AuthLogic::getInstance()->userId();
            if(empty($userId)){
                throw new \Exception('请先登入');
            }
            if($request->method() == 'GET'){
                $info = AskArticle::where('id',$id)->first();
                if($info == null){
                    throw new \Exception('文章不存在');
                }
                $data['hot_sort'] = $info->hot_sort;
                $data['top_sort'] = $info->top_sort;
                $data['style'] = $info->style;
                return phcentSuccess($data);
            }else{
                Validator::input($request->post(), [
                    'style' => Validator::json()->setName('样式'),
                    'top_sort' => Validator::digit()->in([0,1,2,3])->setName('置顶'),
                    'hot_sort' => Validator::digit()->in([0,1,2,3])->setName('热门'),
                ]);
                $params = phcentParams(['style','top_sort','hot_sort'=>0]);
                $info = AskArticle::where('id',$id)->first();
                if($info == null){
                    throw new \Exception('问题不存在');
                }
                //判断是否有修改权限
                $haveRole = IndexService::isHaveAdminRole($info,$userId);
                if(!$haveRole){
                    throw new \Exception('无权限修改');
                }
                Db::connection()->beginTransaction();
                foreach ($params as $k=>$v){
                    $info->$k = $v;
                }
                $info->save();
                Db::connection()->commit();
                return phcentSuccess();
            }
        } catch (\Exception $e) {
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }
}