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
use support\bootstrap\Redis;
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
            $askCollection = new AskCollection();
            $params = phcentParams(['page' => 1,'limit' =>10,'type']);
            $askCollection = phcentWhereParams($askCollection,$params);
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askCollection = $askCollection->orderBy(request()->input('sortName'),request()->input('sortOrder'));
            }else{
                $askCollection = $askCollection->orderBy('id','desc');
            }
            $list  = $askCollection->where('user_id',$userId)->paginate($params['limit']);
            $data['list'] = $list->items();
            $data['categoryList'] = CategoryService::getCategoryList(4);
            return phcentSuccess($data,'收藏列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
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