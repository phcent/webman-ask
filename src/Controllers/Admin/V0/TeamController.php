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
 * @since      象讯·PHP 知识付费问答系统
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Controllers\Admin\V0;


use Phcent\WebmanAsk\Logic\AdminControllerLogic;

use Phcent\WebmanAsk\Model\AdminTeamMenu;
use Respect\Validation\Validator;

class TeamController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\AdminTeam::class;
    public  $name = '权限组';
    public  $projectName = '系统管理-权限组管理-';
    public function getAdminCreate()
    {
        $data['menuList'] = AdminTeamMenu::get();
        return $data; // TODO: Change the autogenerated stub
    }
    public function beforeAdminCreate($user)
    {
        Validator::input(\request()->all(), [
            'name' => Validator::length(1, 32)->noWhitespace()->setName('权限组名称'),
        ]);
        $params = phcentParams([
            'name',
            'role',
        ]);
        return $params;
    }
    public function insertGetAdminUpdate($info, $id)
    {
        $data['menuList'] = AdminTeamMenu::get();
        $data['info'] = $info;
        return $data;
    }
    public function beforeAdminUpdate($user, $id)
    {
        Validator::input(\request()->all(), [
            'name' => Validator::length(1, 32)->noWhitespace()->setName('权限组名称'),
        ]);
        $params = phcentParams([
            'name',
            'role',
        ]);
        return $params;
    }

}