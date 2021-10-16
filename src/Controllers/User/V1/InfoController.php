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


namespace Phcent\WebmanAsk\Controllers\User\V1;

use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\User;
use Respect\Validation\Validator;
use support\Request;

class InfoController
{
    /**
     * 修改密码
     * @param Request $request
     * @return \support\Response
     */
    public function pwd(Request $request)
    {
        try {
            phcentMethod(['POST']);
            $user = AuthLogic::getInstance()->user();
            if($user == null){
                throw new \Exception('会员未登入');
            }
            Validator::input($request->post(), [
                'old_password' => Validator::length(6,50)->setName('原密码'),
                'password' => Validator::length(6,50)->setName('新密码'),
                'password_confirmation' => Validator::equals($request->post('password') ?? null)->length(6,50)->setName('确认密码'),
            ]);
            $params = phcentParams('old_password','password');

            if(!password_verify($params['old_password'],$user->password)){
                throw new \Exception('原密码错误');
            }
            $user->password = password_hash($params['password'],PASSWORD_DEFAULT);
            $user->save();

            return phcentSuccess();
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 修改个人资料
     * @param Request $request
     * @return \support\Response
     */
    public function profile(Request $request)
    {
        try {
            phcentMethod(['POST','GET']);
            $user = AuthLogic::getInstance()->user();
            if($user == null){
                throw new \Exception('会员未登入');
            }
            if($request->method() == 'GET'){
                return phcentSuccess($user);
            }else{
                Validator::input($request->post(), [
                    'name' => Validator::Alnum()->length(4,15)->noWhitespace()->setName('用户名'),
                    'nick_name' => Validator::stringType()->length(2,20)->noWhitespace()->setName('昵称'),
                ]);
                $params = phcentParams('name','nick_name','description');
                if($params['name'] != $user->name){
                    $user = User::where('name',$params['name'])->where('id','<>',$user->id)->first();
                    if($user !=null){
                        throw new \Exception('用户名已存在');
                    }
                }
                if($params['nick_name'] != $user->nick_name){
                    $user = User::where('nick_name',$params['nick_name'])->where('id','<>',$user->id)->first();
                    if($user !=null){
                        throw new \Exception('昵称已存在');
                    }
                }
                foreach ($params as $k=>$v){
                    $user->$k = $v;
                }
                $user->save();
                return phcentSuccess();
            }
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 修改邮箱
     * @param Request $request
     * @return \support\Response
     */
    public function email(Request $request)
    {
        try {
            phcentMethod(['POST','GET']);
            $user = AuthLogic::getInstance()->user();
            if($user == null){
                throw new \Exception('会员未登入');
            }
            if($request->method() == 'GET'){
                Validator::input($request->all(), [
                    'email' => Validator::Alnum()->length(4,15)->noWhitespace()->setName('用户名'),
                    'nick_name' => Validator::Alnum()->length(2,20)->noWhitespace()->setName('昵称'),
                ]);

            }else{
                Validator::input($request->post(), [
                    'name' => Validator::Alnum()->length(4,15)->noWhitespace()->setName('用户名'),
                    'nick_name' => Validator::Alnum()->length(2,20)->noWhitespace()->setName('昵称'),
                ]);
                $params = phcentParams('name','nick_name','description');
                if($params['name'] != $user->name){
                    $user = User::where('name',$params['name'])->where('id','<>',$user->id)->first();
                    if($user !=null){
                        throw new \Exception('用户名已存在');
                    }
                }
                if($params['nick_name'] != $user->nick_name){
                    $user = User::where('nick_name',$params['nick_name'])->where('id','<>',$user->id)->first();
                    if($user !=null){
                        throw new \Exception('昵称已存在');
                    }
                }
                foreach ($params as $k=>$v){
                    $user->$k = $v;
                }
                $user->save();
                return phcentSuccess();
            }
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }
}