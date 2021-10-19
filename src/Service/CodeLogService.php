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


namespace Phcent\WebmanAsk\Service;


use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Phcent\WebmanAsk\Model\SysCodeLog;
use Phcent\WebmanAsk\Model\SysUser;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class CodeLogService
{
    const REGISTER = 1, //注册
        LOGIN = 2, //登入
        FIND_PASSWORD = 3; //找回密码

    /**
     * 发送验证码
     * @param $smsCode
     * @param int $userId
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
     */
    public static function sendSms($smsCode,$userId = 0)
    {
        //查询会员信息
        $user = SysUser::where('mobile',$smsCode['mobile'])->first();
        if(!in_array($smsCode['type'],[self::REGISTER,self::LOGIN,self::FIND_PASSWORD])){
            throw new \Exception("手机验证类型错误");
        }
        //注册、绑定手机时验证手机号是否已存在
        if ($smsCode['type'] == self::REGISTER && $user!=null) {
            throw new \Exception("当前手机号已被绑定，请更换其他号码");
        }

        $authCode = mt_rand(100000,999999);
        switch ($smsCode['type']){
            case self::REGISTER:
                $content = '您正在通过手机注册会员，验证码是：'.$authCode.'。';
                break;
            case self::LOGIN:
                $content = '您正在通过手机登录，验证码是：'.$authCode.'。';
                break;
            case self::FIND_PASSWORD:
                $content = '您正在通过手机号找回密码，验证码是：'.$authCode.'。';
                break;
            default:
                $content = '您正在进行身份安全验证，验证码是：'.$authCode.'。';
        }

        $logId = SysCodeLog::create([
            'type' => $smsCode['type'],
            'code' => $authCode,
            'receiver' => $smsCode['mobile'],
            'ip' => $smsCode['ip'],
            'role' => 2,
            'status' => 0,
            'content' => $content
        ]);

        $config = config('phcentask.sms');

        //发送短信
        try {
            $easySms = new EasySms($config);
            $easySms->send($smsCode['mobile'], ['content'=> $content]//短信内容
                );
            return $logId;
        }catch (NoGatewayAvailableException $exception){
            throw new \Exception('发送短信失败');
        }

    }

    /**
     * 发送邮件
     * @param $params
     * @param int $userId
     * @return
     * @throws \Exception
     */
    public static function sendEmail($params,$userId = 0)
    {
        //查询会员信息
        $user = SysUser::where('email',$params['email'])->first();
        if(!in_array($params['type'],[self::REGISTER,self::LOGIN,self::FIND_PASSWORD])){
            throw new \Exception("邮箱验证类型错误");
        }
        //注册、绑定手机时验证手机号是否已存在
        if ($params['type'] == self::REGISTER && $user!=null) {
            throw new \Exception("当前邮箱已被使用，请更换其他邮箱");
        }

        $authCode = mt_rand(100000,999999);
        switch ($params['type']){
            case self::REGISTER:
                $title = '[象讯科技]您正在通过邮箱注册会员';
                $content = '您正在通过邮箱注册会员，验证码是：'.$authCode;
                break;
            case self::LOGIN:
                $title = '[象讯科技]您正在通过邮箱登录';
                $content = '您正在通过邮箱登录，验证码是：'.$authCode;
                break;
            case self::FIND_PASSWORD:
                $title = '[象讯科技]您正在通过邮箱找回密码';
                $content = '您正在通过邮箱找回密码，验证码是：'.$authCode;
                break;
            default:
                $title = '[象讯科技]您正在进行身份安全验证';
                $content = '您正在进行身份安全验证，验证码是：'.$authCode;
        }

        $logId = SysCodeLog::create([
            'type' => $params['type'],
            'code' => $authCode,
            'receiver' => $params['email'],
            'ip' => $params['ip'],
            'role' => 1,
            'status' => 0,
            'content' => $content
        ]);

        //发送短信
        try {
            $text = <<<EOF
                    <div style="max-width:1000px;margin:20px auto">
                        <section style="position:relative;padding:10px;line-height:normal;background:#f3f3f3;">
                            <h3 class="yead_bdlc" style="display: inline-block; margin: 0px 12px 0px 0px; padding: 0px 0px 0px 10px; border-left: 4px solid rgb(0, 128, 255); font-size: 14px; color: rgb(51, 51, 51); line-height: 20px;">{$content}</h3>
                        </section>
                        <p><br></p><p><br></p>
                        <article class="yead_editor" data-id="1089" data-use="1" data-author="Wxeditor" style="margin: 5px auto;">
                            <section style="width:20em;margin:0 auto;text-align:center">
                                <section style="padding:6px;border: 2px solid #2196f3;color:#FFF;transform:rotate(360deg);background-color: #03a9f4;box-shadow: 1px 4px 9px #c7c6c6;">
                                    <section style="border:1px dashed #FFF;padding:5px 10px">
                                        <p style="margin: 0px;padding: 0px;line-height: 30px;font-weight: 600;">{$authCode}</p>
                                    </section>
                                </section>
                            </section>
                        </article>
                    </div>
            EOF;

            $transport = Transport::fromDsn(config('phcentask.email.dsn'));
            $mailer = new Mailer($transport);
           $email = (new Email())->from(config('phcentask.email.form_address'))->to($params['email'])->subject($title)->html($text);
           $mailer->send($email);
            return $logId;
        }catch (TransportExceptionInterface $exception){
            throw new \Exception('发送失败');
            //  dd($exception->getExceptions());
        }
    }

    /**
     * 验证验证码是否正确
     * @param $mobile
     * @param $code
     * @param int $type
     * @param int $role
     * @return bool
     */
    public static function verifyCode($mobile,$code,$type=1,$role=2)
    {
        $codeLog = SysCodeLog::where('receiver',$mobile)->where('type',$type)->where('role',$role)->where('code',$code)->where('status','0')->first();
        if($codeLog == null){
            return false;
        }
        $codeLog->status = 1;
        $codeLog->save();
        return true;
    }
}