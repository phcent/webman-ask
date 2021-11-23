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


namespace Phcent\WebmanAsk\Controllers\Web\V1;


use Illuminate\Support\Facades\Date;
use Phcent\WebmanAsk\Model\AskArticle;
use Phcent\WebmanAsk\Model\AskQuestion;
use Phcent\WebmanAsk\Service\CategoryService;
use Phcent\WebmanAsk\Service\IndexService;
use support\Db;
use support\Request;

class IndexController
{
    /**
     * 获取首页
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $topQuestion = AskQuestion::where('status','<>',0)->where('top_sort','>',0)->with(['tags','user'])->select(['id','title','answer_num','view_num','user_id','top_sort','hot_sort','style','reward_balance','reward_points','created_at',Db::connection()->raw('any_value(1) as type')]);
            $topArticle = AskArticle::where('status','<>',0)->where('top_sort','>',0)->with(['tags','user'])->select(['id','title','reply_num as answer_num','view_num','user_id','top_sort','hot_sort','style','reward_balance','reward_points','created_at',Db::connection()->raw('any_value(2) as type')]);
            $topList = $topArticle->union($topQuestion)->orderBy('top_sort','desc')->get();
            $topList->map(function ($item){
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
            $data['top_list'] = $topList;
            $question = AskQuestion::where('status','<>',0)->where(function ($query) use ($request) {
                if($request->input('type') == 'hot'){
                    $query->where('hot_sort' ,'>', 0);
                }
                if($request->input('type') == 'unreply'){
                    $query->where('answer_num', 0);
                }
            })->with(['tags','user'])->select(['id','title','answer_num','view_num','user_id','top_sort','hot_sort','style','reward_balance','reward_points','created_at',Db::connection()->raw('any_value(1) as type')]);
            $article = AskArticle::where('status','<>',0)->where(function ($query) use ($request) {
                if($request->input('type') == 'hot'){
                    $query->where('hot_sort','>', 0);
                }
                if($request->input('type') == 'unreply'){
                    $query->where('reply_num', 0);
                }
            })->with(['tags','user'])->select(['id','title','reply_num as answer_num','view_num','user_id','top_sort','hot_sort','style','reward_balance','reward_points','created_at',Db::connection()->raw('any_value(2) as type')]);
            $list  = $article->union($question)->orderBy('created_at','desc')->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
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
            $data['hotExpert'] = IndexService::getExpertOnline(10);
            return phcentSuccess($data,'问题文章列表',[ 'page' => $list->currentPage(),'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    public function expert()
    {
        
    }
}