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
 * @since      象讯·PHP 知识付费问答系统
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Controllers\Admin\System;


use Phcent\WebmanAsk\Logic\AdminControllerLogic;
use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\SysCashLog;
use Phcent\WebmanAsk\Service\CashService;
use support\Request;

class CashController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\SysCashLog::class;
    public  $name = '提现';
    public  $projectName = '系统管理-提现管理-';


    /**
     * 变更状态
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function update(Request $request,$id)
    {
        try {
            phcentMethod(['PUT']);
            $params = phcentParams(['status','refuse_reason'=>'']);
            if(!in_array($params['status'],[1,2])){
                throw new \Exception('参数错误');
            }
            $cashInfo = SysCashLog::where('id',$id)->where('status',10)->first();
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

    public function create(Request $request)
    {
        return []; // TODO: Change the autogenerated stub
    }

    public function destroy(Request $request, $id)
    {
        return []; // TODO: Change the autogenerated stub
    }
}