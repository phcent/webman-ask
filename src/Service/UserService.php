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


namespace Phcent\WebmanAsk\Service;

use Illuminate\Support\Facades\Date;
use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\User;
use Phcent\WebmanAsk\Model\UserLog;

use support\Db;

class UserService
{
    /**
     * 登入状态及时间处理
     * @param $token
     * @throws \Throwable
     */
    public static function afterLogin($token)
    {
        if ($token){
            $user = AuthLogic::getInstance()->user();
            if($user != null){
                $ip = request()->getRealIp();
                $login_time = Date::now();
                Db::connection()->beginTransaction();
                try {
                    $user->last_login_time = $user->login_time;
                    $user->last_login_ip = $user->login_ip;
                    $user->login_num += 1;
                    $user->login_time = $login_time;
                    $user->login_ip = $ip;
                    $user->save();
                    Db::connection()->commit();
                }catch (\Exception $exception){
                    Db::connection()->rollBack();
                }
            }

        }
    }

    /**
     * 登入
     * @param $name
     * @param $password
     * @return bool
     * @throws \Exception
     */
    public static function login($name,$password)
    {
        $is_mobile = phcentIsPhoneNumber($name);
        $is_email = phcentIsEmailText($name);
        if ($is_mobile){
            $credentials = [
                'mobile'=>$name,
                'password'=>$password
            ];
        }else if($is_email){
            $credentials = [
                'email'=>$name,
                'password'=>$password
            ];
        }else{
            $credentials = ['name'=>$name,'password'=>$password];
        }
        $token = AuthLogic::getInstance()->attempt($credentials);
        if (!$token){
            throw new \Exception(  '账号或密码不正确');
        }
        self::afterLogin($token);
        return $token;
    }

    /**
     * 短信登入
     * @param $mobile
     * @param $code
     * @return false|mixed
     * @throws \Exception
     */
    public static function smsLogin($mobile,$code)
    {
        $is_mobile = phcentIsPhoneNumber($mobile);
        if(!$is_mobile){
            throw new \Exception('手机号码格式不正确');
        }
        $codeVerify = CodeLogService::verifyCode($mobile,$code,2);
        if(!$codeVerify){
            throw new \Exception('验证码错误');
        }
        $user = User::where('mobile',$mobile)->first();
        if($user == null){
            $user = User::create([
                'mobile'=> $mobile,
                'mobile_verified_at' => Date::now(),
                'password' => password_hash(mt_rand(1000000,9999999),PASSWORD_DEFAULT),
                'name' => 'M'.$mobile,
                'nick_name' => self::getRandName('m_',7,'nick_name')
            ]);
        }
        $token = AuthLogic::getInstance()->login($user);
        if (!$token){
            throw new \Exception(  '登入失败');
        }
        self::afterLogin($token);
        return $token;
    }

    /**
     * 发送信息
     * @param $name
     * @param int $type
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
     */
    public static function getCode($name,$type = 1)
    {
        $is_mobile = phcentIsPhoneNumber($name);
        $is_email = phcentIsEmailText($name);
        if ($is_mobile){
            CodeLogService::sendSms(['mobile'=>$name,'ip'=>request()->getRealIp(),'type'=>$type]);
        }else if($is_email){
            CodeLogService::sendEmail(['email'=>$name,'ip'=>request()->getRealIp(),'type'=>$type]);
        }else{
            throw new \Exception('数据异常');
        }
    }

    /**
     * 注册
     * @param $params
     * @return false|void
     * @throws \Exception
     */
    public static function reg($params)
    {
        $is_mobile = phcentIsPhoneNumber($params->name);
        $is_email = phcentIsEmailText($params->name);
        if ($is_mobile){
            if(CodeLogService::verifyCode($params->name,$params->code,1)){
                $pwd = mt_rand(1000000,9999999);
                $info = User::create([
                    'mobile'=> $params->name,
                    'mobile_verified_at' => Date::now(),
                    'password' => password_hash($pwd,PASSWORD_DEFAULT),
                    'name' => 'M'.$params->name,
                    'nick_name' => self::getRandName('m_',7,'nick_name')
                ]);
                $token = AuthLogic::getInstance()->login($info);
                self::afterLogin($token);
                return $token;
            }else{
                throw new \Exception('验证码错误');
            }

        }else if($is_email){
            if(CodeLogService::verifyCode($params->name,$params->code,1,1)){
                $info = User::create([
                    'email'=> $params->name,
                    'email_verified_at' => Date::now(),
                    'password' => password_hash(mt_rand(1000000,9999999),PASSWORD_DEFAULT),
                    'name' => self::getRandName('PY_',6,'name'),
                    'nick_name' => self::getRandName('e_',7,'nick_name')
                ]);
                $token = AuthLogic::getInstance()->login($info);
                self::afterLogin($token);
                return $token;
            }else{
                throw new \Exception('验证码错误');
            }
        }else{
            throw new \Exception('数据异常');
        }
    }

    /**
     * 随机用户名
     * @param string $prefix
     * @param int $num
     * @param string $name
     * @return string
     */
    public static function getRandName($prefix = 'user_', $num = 6,$name ='name')
    {
        $user_name = '';
        $chars = ['0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
        for ( $i = 0; $i < $num; $i++ )
        {
            $user_name .= $chars[mt_rand(0, count($chars)-1)];
        }
        $user_name = $prefix.strtoupper(base_convert(time() - 1420070400, 10, 36)).$user_name;
        $user = User::where( $name , $user_name)->first();
        if(!empty($user)) {
            for ($i = 1;$i < 3;$i++) {
                $user_name .= $chars[mt_rand(0, count($chars)-1)];
                $user = User::where( $name , $user_name)->first();
                if(empty($user)) {//查询为空表示当前会员名可用
                    break;
                }
            }
        }
        return $user_name;
    }

    /**
     * 找回密码
     * @param $params
     * @throws \Exception
     */
    public static function forget($params)
    {
        $is_mobile = phcentIsPhoneNumber($params->name);
        $is_email = phcentIsEmailText($params->name);
        if ($is_mobile){
            if(CodeLogService::verifyCode($params->name,$params->code,3)){
                $user = User::where('mobile',$params->name)->first();
                if($user ==null){
                    throw new \Exception('手机号不存在');
                }
                $user->password = password_hash($params->password,PASSWORD_DEFAULT);
                $user->save();
            }else{
                throw new \Exception('验证码错误');
            }

        }else if($is_email){
            if(CodeLogService::verifyCode($params->name,$params->code,3,1)){
                $user = User::where('email',$params->name)->first();
                if($user ==null){
                    throw new \Exception('会员邮箱不存在');
                }
                $user->password = password_hash($params->password,PASSWORD_DEFAULT);
                $user->save();
            }else{
                throw new \Exception('验证码错误');
            }
        }else{
            throw new \Exception('数据异常');
        }
    }

    /**
     * 写入操作日志
     * @param $text
     * @param $router
     * @param $userId
     * @param $userName
     * @param array $params
     */
    public static function addLog($text,$router,$userId,$userName,$params=[])
    {
        UserLog::create([
            'user_id' => $userId,
            'user_name' => $userName,
            'content' => $text,
            'route' => $router,
            'param' => $params,
            'ip' => request()->getRealIp()
        ]);
    }
}