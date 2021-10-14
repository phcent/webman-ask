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


namespace Phcent\WebmanAsk\Logic;


use Illuminate\Support\Str;

class AuthLogic
{
    private static $instance;
    private $guard = 'user';

    public static function getInstance()
    {
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct()
    {

    }

    private function __clone(){

    }
    /**
     * 会员自动登入
     * @param array $data
     * @return false|mixed
     */
    public function attempt($data =[]){
        try {
            if(is_array($data)) {
                $user = $this->getUserClass();
                foreach ($data as $key=>$val){
                    if($key === 'password'){
                       // password_hash()
                    //    $user->where( $key, md5($val));
                    }else{
                        $user->where($key,$val);
                    }
                }
                $user = $user->first();
                if(isset($data['password'])){
                    if(!password_verify($data['password'],$user->password)){
                        throw new \Exception('密码错误');
                    }
                }

                if($user != null){
                    $token = TokenLogic::createToken(['id'=>$user->id,'guard'=>$this->guard]);
                    if($token['status'] == 200){
                        session([ 'token' => $token['token']]);
                        return  $token['token'];
                    }
                }

            }
            return false;
        }catch (\Exception $e){
            return false;
        }
    }

    private function getUserClass(){
        $guardConfig = config('phcentask.guard.'.$this->guard);
        if(!empty($guardConfig)){
            return new $guardConfig;
        }
        return null;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function guard($name)
    {
        if(!empty($name)){
            return $this->guard = $name;
        }
    }

    /**
     * 获取会员信息
     * @param false $cache
     * @return null
     */
    public function user($cache = false)
    {
        $data = $this->getUserData();
        if(!empty($data)){
            $user = $this->getUserClass();
            return $user->where('id',$data['id'])->first();
        }
        return null;
    }

    public function lockUser()
    {
        $data = $this->getUserData();
        if(!empty($data)){
            $user = $this->getUserClass();
            return $user->where('id',$data['id'])->lockForUpdate()->first();
        }
        return null;
    }

    /**
     * 判断是否登入 并获取ID
     * @return int|mixed
     */
    public function isLogin()
    {
        $data = $this->getUserData();
        if(!empty($data)){
            return true;
        }
        return false;
    }
    /**
     * 判断是否登入 并获取ID
     * @return int|mixed
     */
    public function userId()
    {
        $data = $this->getUserData();
        if(!empty($data)){
            return $data['id'];
        }
        return 0;
    }

    private function getUserData(){
        try {
            $header = request()->header('Authorization', '');
            if (Str::startsWith($header, 'Bearer ')) {
                $token = Str::substr($header, 7);
            }
            $token = $token ?? session('token');
            $checkToken = TokenLogic::checkToken($token);
            if($checkToken['status'] == 200){
                $jwt_data = $checkToken['data']['data'];
                $arr = (array)$jwt_data;
                return $arr;
            }
            return null;
        }catch (\Exception $e){
            return null;
        }
    }

    /**
     * 登入
     * @param $user
     * @return false|mixed
     */
    public function login($user)
    {
        $token = TokenLogic::createToken(['id'=>$user->id,'guard'=>$this->guard]);
        if($token['status'] == 200){
            session([ 'token' => $token['token']]);
            return  $token['token'];
        }
        return false;
    }

    /**
     * 退出登入
     */
    public function logout()
    {
        $header = request()->header('Authorization', '');
        if (Str::startsWith($header, 'Bearer ')) {
            $token = Str::substr($header, 7);
        }
        $token = $token ?? session('token');
    }
}