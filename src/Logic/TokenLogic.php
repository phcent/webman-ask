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


namespace Phcent\WebmanAsk\Logic;


use Firebase\JWT\JWT;

class TokenLogic
{
    /**
     * 创建 token
     * @param string $data
     * @param integer $exp_time 必填 token过期时间 单位:秒 例子：7200=2小时
     * @param string $scopes 选填 token标识，请求接口的token
     * @return array
     */
    // private $TokenKey = config('jwt.key');
    public static function createToken($data="",$exp_time=0,$scopes=""){
        //JWT标准规定的声明，但不是必须填写的；
        //iss: jwt签发者
        //sub: jwt所面向的用户
        //aud: 接收jwt的一方
        //exp: jwt的过期时间，过期时间必须要大于签发时间
        //nbf: 定义在什么时间之前，某个时间点后才能访问
        //iat: jwt的签发时间
        //jti: jwt的唯一身份标识，主要用来作为一次性token。
        //公用信息
        try {
            $key= config('phcentask.jwt.key');
            $time = time(); //当前时间
            $token['iss']= config('phcentask.jwt.iss'); //签发者 可选
            $token['aud']= config('phcentask.jwt.aud'); //接收该JWT的一方，可选
            $token['iat'] = $time; //签发时间
            $token['nbf'] = $time; //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用
            if($scopes){
                $token['scopes']=$scopes; //token标识，请求接口的token
            }
            if(!$exp_time){
                $exp_time= config('phcentask.jwt.exp');//默认=2小时过期
            }
            $token['exp']=$time+$exp_time; //token过期时间,这里设置2个小时
            if($data){
                $token['data']= $data; //自定义参数
            }
            $json = JWT::encode($token,$key);
            return ['status' => 200, 'msg'=>'success', 'token' => $json ]; //返回信息
        }catch(\Firebase\JWT\ExpiredException $e){ //签名不正确
            return ['status'=>101,'msg'=>$e->getMessage(),'data'=>'']; //返回信息 103=token过期
        }catch(\Exception $e) { //其他错误
            return ['status'=>199,'msg'=>$e->getMessage(),'data'=>'']; //返回信息 199 其他错误
        }
    }

    /**
     * 验证token是否有效,默认验证exp,nbf,iat时间
     * @param string $jwt 需要验证的token
     * @return array
     */
    public static function checkToken($jwt){
        $key= config('phcentask.jwt.key');
        try {
            JWT::$leeway = 60; //当前时间减去60，把时间留点余地
            $decoded = JWT::decode($jwt, $key, ['HS256']); //HS256方式，这里要和签发的时候对应
            $arr = (array)$decoded;
            return ['status'=>200,'msg'=>'success','data'=>$arr]; //返回信息
        } catch(\Firebase\JWT\SignatureInvalidException $e) { //签名不正确
            return ['status'=>101,'msg'=>$e->getMessage(),'data'=>'']; //返回信息 101=签名不正确
        }catch(\Firebase\JWT\BeforeValidException $e) { // 签名在某个时间点之后才能用
            return ['status'=>102,'msg'=>$e->getMessage(),'data'=>'']; //返回信息
        }catch(\Firebase\JWT\ExpiredException $e) { // token过期
            return ['status'=>103,'msg'=>$e->getMessage(),'data'=>'']; //返回信息 103=token过期
        }catch(\Exception $e) { //其他错误
            return ['status'=>199,'msg'=>$e->getMessage(),'data'=>'']; //返回信息 199 其他错误
        }
        //Firebase定义了多个 throw new，我们可以捕获多个catch来定义问题，catch加入自己的业务，比如token过期可以用当前Token刷新一个新Token
    }
}