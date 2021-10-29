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


namespace Phcent\WebmanAsk\Controllers\Admin\V0;


use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Logic\CodeLogic;
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
}