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
 * @since      象讯·PHP知识付费问答系统
 *-------------------------------------------------------------------------t*
 */

namespace Phcent\WebmanAsk\Controllers\Web\Cms;

use Phcent\WebmanAsk\Model\CmsArticle;
use Phcent\WebmanAsk\Model\CmsCategory;
use Phcent\WebmanAsk\Service\CmsArticleService;
use support\Redis;
use support\Request;

class ArticleController
{
    /**
     * 获取文章列表
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $article = new CmsArticle();
            $article = phcentWhereParams($article, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $article = $article->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $article = $article->orderBy('id', 'desc');
            }
            $list = $article->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $data['categoryList'] = CmsCategory::where('code',$request->input('resultType'))->where('pid','>',0)->get();
            $data['list'] = $list->items();
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }

    }
    /**
     * 获取新闻详情
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function show(Request $request,$id)
    {
        try {
            phcentMethod(['GET']);
            $info = CmsArticle::where('id',$id)->first();
            if($info == null){
                throw new \Exception('文章不存在');
            }
            $data['categoryList'] = CmsCategory::where('code',$request->input('resultType'))->where('pid','>',0)->get();
            $data['info'] = $info;
            return phcentSuccess($data);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 根据编号获取数据
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function byId(Request $request,$id)
    {
        $ids = is_array($id) ? $id : (is_string($id) ? explode(',', $id) : func_get_args());
        try {
            $limit = $request->input('limit',4);
            $data['list'] = CmsArticleService::getArticleByCateIds($ids,$limit);
            return phcentSuccess($data);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

}