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


namespace Phcent\WebmanAsk\Service;


use Illuminate\Support\Facades\Date;
use Phcent\WebmanAsk\Model\SysMenu;
use Phcent\WebmanAsk\Model\SysSite;
use Phcent\WebmanAsk\Model\SysTeamMenu;
use support\Redis;

class AdminService
{
    public static function checkHaveRole($userData)
    {
        try {
            $userId = $userData['id'];
            $teamId = $userData['current_team_id'];
            if($teamId != 1){
                if(empty($teamId)){
                    throw new \Exception('没有权限');
                }
                self::getAdminRoleMenu($teamId);
            }
            return true;
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * 获取管理员缓存
     * @param $teamId
     * @return bool
     * @throws \Exception
     */
    public static function getAdminRoleMenu($teamId)
    {
        $teamMenu = self::getAdminTeamMenuCache();
        $menu = self::getAdminMenuCache();
        $control = request()->controller;
        $menuIds = collect($teamMenu)->where('team_id',$teamId)->pluck('menu_id');
        //判断控制器是否存在
        $haveMenu = collect($menu)->whereIn('id',$menuIds)->where('controller',$control)->first();
        if($haveMenu != null){
            //查询
            $teamMenuItem = collect($teamMenu)->where('menu_id',$haveMenu->id)->first();
            if($teamMenuItem != null){
                $perms = explode(',', $teamMenuItem->perm);
                $method = request()->method();
                if (in_array($method, (array) $perms)) {
                    return true;
                }
            }
        }
         throw new \Exception('无权操作');
    }

    /**
     * 获取管理员菜单缓存
     * @return mixed
     */
    public static function getAdminMenuCache()
    {
        $list = Redis::get('siteAdminMenu');
        if($list == null){
            $list = SysMenu::where('status',1)->get()->toJson();
            Redis::set('siteAdminMenu',$list);
        }
        return json_decode($list);
    }

    /**
     * 获取菜单与权限缓存记录
     * @return mixed
     */
    public static function getAdminTeamMenuCache()
    {
        $list = Redis::get('siteAdminTeamMenu');
        if($list == null){
            $list = SysTeamMenu::get()->toJson();
            Redis::set('siteAdminTeamMenu',$list);
        }
        return json_decode($list);
    }
}