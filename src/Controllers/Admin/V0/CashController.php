<?php
/**
 *-------------------------------------------------------------------------p*
 * 提现记录
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


namespace Phcent\WebmanAsk\Controllers\Admin\V0;


use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\CashLog;
use Phcent\WebmanAsk\Service\CashService;
use support\Request;

class CashController
{
    /**
     * 提现记录
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $cashLog = new CashLog();
            $cashLog = phcentWhereParams($cashLog, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $cashLog = $cashLog->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $cashLog = $cashLog->orderBy('id', 'desc')->orderBy('id', 'desc');
            }
            $list = $cashLog->paginate($request->limit ?? 10);
            $data['list'] = $list->items();
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total()]);
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 变更状态
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public static function update(Request $request,$id)
    {
        try {
            phcentMethod(['PUT']);
            $params = phcentParams(['status','refuse_reason'=>'']);
            if(!in_array($params['status'],[1,2])){
                throw new \Exception('参数错误');
            }
            $cashInfo = CashLog::where('id',$id)->where('status',10)->first();
            if($cashInfo == null){
                throw new \Exception('该提现已被操作');
            }
            $adminId =  AuthLogic::getInstance()->userId();
            if($params['status'] == 1){
                CashService::adminAgreeCash($cashInfo,$adminId);
            }else{
                if(empty($params['refuse_reason'])){
                    throw new \Exception('拒绝提现理由不能为空');
                }
                CashService::adminRefuseCash($cashInfo,$adminId,$params['refuse_reason']);
            }
            return phcentSuccess();
        }catch (\Exception $e){
            return phcentError( $e->getMessage());
        }
    }
}