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


namespace Phcent\WebmanAsk\Controllers\Admin\System;


use Phcent\WebmanAsk\Logic\AdminControllerLogic;
use Phcent\WebmanAsk\Model\SysTeam;

use Phcent\WebmanAsk\Service\BalanceService;
use Phcent\WebmanAsk\Service\PointsService;
use Respect\Validation\Validator;
use support\Request;

class UserController extends AdminControllerLogic
{
    public  $model = \Phcent\WebmanAsk\Model\SysUser::class;
    public  $name = '会员';
    public  $projectName = '系统管理-会员管理-';

    public function getAdminCreate()
    {
        $data['teamList'] = SysTeam::get();
        return $data; // TODO: Change the autogenerated stub
    }

    public function insertGetAdminUpdate($info, $id)
    {
        $data['info'] = $info;
        $data['teamList'] = SysTeam::get();
        return $data;
    }

    public function beforeAdminCreate($user)
    {
        Validator::input(\request()->all(), [
            'name' => Validator::length(1, 32)->noWhitespace()->setName('用户名'),
            'nick_name' => Validator::length(1, 32)->noWhitespace()->setName('昵称'),
            'password' => Validator::length(6, 64)->noWhitespace()->setName('密码'),
            'current_team_id' => Validator::digit()->in([1,2])->setName('组别'),
        ]);
        $params = phcentParams([
            'name',
            'email',
            'mobile',
            'password',
            'current_team_id',
            'avatar',
            'status',
            'nick_name',
        ]);
        return $params; // TODO: Change the autogenerated stub
    }

    /**
     * @param $user
     * @param $id
     * @return array|mixed
     */
    public function beforeAdminUpdate($user, $id)
    {
        Validator::input(\request()->all(), [
            'name' => Validator::length(1, 32)->noWhitespace()->setName('用户名'),
            'nick_name' => Validator::length(1, 32)->noWhitespace()->setName('昵称'),
         //   'password' => Validator::length(6, 64)->noWhitespace()->setName('密码')
        ]);
        $params = phcentParams([
            'name',
            'email',
            'mobile',
            'password',
            'current_team_id',
            'avatar',
            'status',
            'nick_name'
        ]);
        if(isset($params['password']) && strlen($params['password']) >= 6){
            $params['password'] = password_hash($params['password'],PASSWORD_DEFAULT);
        }else{
            unset($params['password']);
        }
        return $params;
    }

    /**
     * 操作会员金额
     * @param Request $request
     * @return \support\Response
     */
    public function money(Request $request)
    {
        try {
            phcentMethod(['POST']);
            Validator::input($request->all(), [
                'operation' => Validator::stringType()->in(['increase','decrease','freeze','unfreeze'])->noWhitespace()->setName('操作方式'),
                'amount' => Validator::length(1, 32)->noWhitespace()->setName('金额'),
                'user_id' => Validator::digit()->min(1)->noWhitespace()->setName('会员编号'),
            ]);
            $params = phcentParams(['operation','amount','user_id']);
            BalanceService::changeBalance($params['operation'],$params['amount'],$params['user_id']);
            return  phcentSuccess();
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 操作积分
     * @param Request $request
     * @return \support\Response
     */
    public function points(Request $request)
    {
        try {
            phcentMethod(['POST']);
            Validator::input($request->all(), [
                'operation' => Validator::stringType()->in(['increase','decrease','freeze','unfreeze'])->noWhitespace()->setName('操作方式'),
                'amount' => Validator::digit()->min(1)->noWhitespace()->setName('金额'),
                'user_id' => Validator::digit()->min(1)->noWhitespace()->setName('会员编号'),
            ]);
            $params = phcentParams(['operation','amount','user_id']);
            PointsService::changePoints($params['operation'],$params['amount'],$params['user_id']);
            return  phcentSuccess();
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

}