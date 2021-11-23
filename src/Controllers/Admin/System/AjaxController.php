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


namespace Phcent\WebmanAsk\Controllers\Admin\System;
use Illuminate\Support\Facades\Date;
use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Logic\CodeLogic;
use Phcent\WebmanAsk\Model\SysOrders;
use Phcent\WebmanAsk\Model\SysRechargeLog;
use Phcent\WebmanAsk\Model\SysUser;
use Phcent\WebmanAsk\Service\AdminService;
use support\Redis;
use support\Request;

class AjaxController
{
    /**
     * 清除缓存
     * @param Request $request
     * @return \support\Response
     */
    public function cache(Request $request)
    {
        try {
            phcentMethod(['POST']);
            Redis::del('siteAdminMenu','siteAdminTeamMenu');
            return phcentSuccess();
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 获取后台菜单
     * @param Request $request
     * @return \support\Response
     */
    public function menu(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $userData = AuthLogic::getInstance()->getUserData();
            $teamMenu = AdminService::getAdminTeamMenuCache(); //权限与菜单
            $menu = AdminService::getAdminMenuCache(); // 菜单缓存
           if($userData['current_team_id'] != 1){
               $menuIds = collect($teamMenu)->where('team_id',$userData['current_team_id'])->pluck('menu_id');
               $menu = collect($menu)->whereIn('id',$menuIds)->values()->all();
           }
           return phcentSuccess($menu);
        }catch (\Exception $e){
            return  phcentError($e->getMessage());
        }
    }

    /**
     * 获取卡片数据
     * @param Request $request
     * @return \support\Response
     */
    public function card(Request $request)
    {
        try {
            phcentMethod(['GET']);
            //会员数量
            $data['userNum'] = SysUser::count();
            $data['userNumToday'] = SysUser::whereBetween('created_at',[Date::now()->startOfDay(),Date::now()->endOfDay()])->count();
            //订单数量
            $data['orderNum'] = SysOrders::where('status',1)->count();
            $data['orderNumToday'] = SysOrders::where('status',1)->whereBetween('created_at',[Date::now()->startOfDay(),Date::now()->endOfDay()])->count();
            //充值单量
            $data['rechargeNum'] = SysRechargeLog::where('status',1)->count();
            $data['rechargeMoney'] = SysRechargeLog::where('status',1)->count('amount');
            $data['rechargeGiveMoney'] = SysRechargeLog::where('status',1)->count('give_amount');

            //

            return phcentSuccess($data);
        }catch (\Exception $e){
            return  phcentError($e->getMessage());
        }
    }
}