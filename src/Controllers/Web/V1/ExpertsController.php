<?php
/**
 *-------------------------------------------------------------------------p*
 * 专家管理
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
use Phcent\WebmanAsk\Model\AskCategory;
use Phcent\WebmanAsk\Model\AskUser;
use Phcent\WebmanAsk\Service\CategoryService;
use Phcent\WebmanAsk\Service\IndexService;
use support\Redis;
use support\Request;

class ExpertsController
{
    /**
     * 获取专家列表
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $userId = AuthLogic::getInstance()->userId();
            $experts = new AskUser();
            $experts = phcentWhereParams($experts,$request->all());
            $list = $experts->with('user')->whereHas('user')
                ->where('expert_status',1)
                ->orderBy('hot_sort','desc')
                ->orderBy('answer_best_num','desc')
                ->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            foreach ($list as $item){
                $item->user_name = $item->user->nick_name;
                $item->avatar_url = $item->user->avatar_url;
                $item->is_online = phcentIsUserOnline($item->id);
                $item->setHidden(['user']);
            }
            $data['list'] = $list->items();
            $data['categoryList'] = CategoryService::getCategoryList(6);
            return phcentSuccess($data,'专家列表',[ 'page' => $list->currentPage(),'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 申请专家认证
     * @param Request $request
     */
    public function create(Request $request)
    {
    }
}