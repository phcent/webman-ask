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
 * @since      象讯·PHP商城系统Pro
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Controllers\Web\V1;


use Illuminate\Support\Facades\Date;
use Phcent\WebmanAsk\Model\AskQuestion;
use Phcent\WebmanAsk\Service\CategoryService;
use Phcent\WebmanAsk\Service\IndexService;
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
            $askQuestion = new AskQuestion();
            $params = phcentParams(['page' => 1,'limit' =>10,'cate_id']);
            $askQuestion = phcentWhereParams($askQuestion,$params);
            $type = $request->input('type','new');
            switch ($type){
                case 'hot':
                    $askQuestion = $askQuestion->orderBy('hot_sort','desc')->orderBy('id','desc')->orderBy('view_num','desc');
                    break;
                case 'price':
                    $askQuestion = $askQuestion->where(function ($query){
                        return $query->where('reward_balance','>','0')->orWhere('reward_points','>','0');
                    })->orderBy('id','desc');
                    break;
                case 'unsolved':
                    $askQuestion = $askQuestion->where('status',1)->orderBy('id','desc');
                    break;
                case 'unanswer':
                    $askQuestion = $askQuestion->where('answer_num',0)->where('status','<>',0)->orderBy('id','desc');
                    break;
                case 'solved':
                    $askQuestion = $askQuestion->where('status',2)->orderBy('id','desc');
                    break;
                case 'unsettled':
                    $askQuestion = $askQuestion->where('reward_time','<',Date::now()->subDays(config('phcentask.rewardTime',7)))->where('status',1)->orderBy('id','desc');
                    break;
                default:
                    $askQuestion = $askQuestion->where('status','<>',0)->orderBy('id','desc');
                    break;
            }
            $list  = $askQuestion->with(['tags','user'])->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
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
            $data['type'] = $type;
            $data['list'] = $list->items();
            $data['categoryList'] = CategoryService::getCategoryList(1);
            $data['hotQuestion'] = IndexService::getHotQuestion();
            $data['hotArticle'] = IndexService::getHotArticle();
            $data['hotTags'] = IndexService::getHotTags();
            return phcentSuccess($data,'问题列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    public function expert()
    {
        
    }
}