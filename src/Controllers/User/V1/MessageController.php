<?php
/**
 *-------------------------------------------------------------------------p*
 * 我的私信
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


namespace Phcent\WebmanAsk\Controllers\User\V1;


use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\AskMessage;
use support\Request;

class MessageController
{
    /**
     * 获得私信列表
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $userId = AuthLogic::getInstance()->userId();
            $askMessage = new AskMessage();
            $params = phcentParams(['page' => 1,'limit' =>10]);
            $askMessage = phcentWhereParams($askMessage,$params);
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askMessage = $askMessage->orderBy(request()->input('sortName'),request()->input('sortOrder'));
            }else{
                $askMessage = $askMessage->orderBy('id','desc');
            }
            $list  = $askMessage->where('to_user_id',$userId)->paginate($params['limit']);
            $data['list'] = $list->items();
            return phcentSuccess($data,'收藏列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    public function create(Request $request)
    {
        try {
            phcentMethod(['POST']);
            $userId = AuthLogic::getInstance()->userId();
            $askMessage = new AskMessage();


           // return phcentSuccess($data,'收藏列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }
}