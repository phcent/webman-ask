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


namespace Phcent\WebmanAsk\Controllers\Web\V1;


use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\CodeLog;

use Phcent\WebmanAsk\Service\CodeLogService;
use Phcent\WebmanAsk\Service\UserService;
use support\Request;
use Respect\Validation\Validator;

class AuthController
{
    /**
     * 登入
     * @param Request $request
     * @return \support\Response
     */
    public function login(Request $request)
    {
        try {
            phcentMethod('POST');
            $params = phcentParams(['name'=>'','password'=>'','code'=>'','type'=>1],$request,true);
            if(!in_array($params->type,[1,2])){
                throw new \Exception('参数错误');
            }
            if($params->type == 1){ //账户或手机号登入
                //登录获得token
                $token = UserService::login($params->name,$params->password);
            }else{
                $token = UserService::smsLogin($params->name,$params->code);
            }

            return phcentSuccess([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('phcentask.jwt.key'),
            ]);
        }catch (\Exception $e){
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 刷新token
     * @return mixed
     */
    public function refresh()
    {
        //$user_info = Auth::guard('user')->user();
        try {
            $token ='';
            return phcentSuccess([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('phcentask.jwt.key'),
            ]);
        }catch (\Exception $e){
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 获取会员信息
     * @return mixed
     */
    public function me()
    {
        try {
            $userInfo = AuthLogic::getInstance()->user();
            return phcentSuccess($userInfo);
        }catch (\Exception $e){
            return phcentError( $e->getMessage());
        }

    }

    /**
     * 获取
     * @param Request $request
     * @return \support\Response
     */
    public function code(Request $request)
    {
        Validator::input($request->post(), [
            'name' => Validator::length(1, 64)->setName('手机号/邮箱'),
            'type' => Validator::digit()->min(1)->setName('请求类型'),
        ]);
        $params = phcentParams(['name'=>'','type'=>1],$request,true);
        //获取验证码
        try {
            phcentMethod('POST');
            if(!in_array($params->type,[CodeLogService::REGISTER,CodeLogService::LOGIN,CodeLogService::FIND_PASSWORD])){
                throw new \Exception('请求类型错误');
            }
            UserService::getCode($params->name,$params->type);
            $codeList = CodeLog::where('receiver',$params->name)->where('status',0)->get();
            return phcentSuccess($codeList,'发送成功');
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 注册
     * @param Request $request
     * @return \support\Response
     */
    public function reg(Request $request)
    {
        Validator::input($request->post(), [
            'name' => Validator::length(1, 64)->setName('手机号/邮箱'),
            'code' => Validator::length(4, 6)->setName('验证码'),
        ]);
        try {
            phcentMethod('POST');
            $params = phcentParams(['name'=>'','code'=>''],$request,true);
            $info = UserService::reg($params);
            return phcentSuccess($info,'注册成功');
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 找回密码
     * @param Request $request
     * @return \support\Response
     */
    public function forget(Request $request)
    {

        Validator::input($request->post(), [
            'name' => Validator::length(1, 64)->noWhitespace()->setName('手机号/邮箱'),
            'code' => Validator::length(4, 6)->noWhitespace()->setName('验证码'),
            'password' => Validator::length(6, 60)->noWhitespace()->setName('密码'),
        ]);
        try {
            phcentMethod('POST');
            $params = phcentParams(['name'=>'','password'=>'','code'=>''],$request,true);
            UserService::forget($params);
            return phcentSuccess(null,'找回密码成功');
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 退出登入
     * @return mixed
     */
    public function logout()
    {
        try {
            AuthLogic::getInstance()->logout();
            return phcentSuccess(null,'退出成功');
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }
}