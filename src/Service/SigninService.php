<?php
/**
 *-------------------------------------------------------------------------p*
 * 签到处理
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
use Phcent\WebmanAsk\Model\AskSigninLog;

class SigninService
{
    /**
     * 获取签到首页信息
     * @return mixed
     */
    public static function getIndexSigninRank()
    {
        $userId = AuthLogic::getInstance()->userId();
        $data['is_login'] = 0;
        $data['is_signin'] = 0;
        $data['signin_days'] = 0;
        if(!empty($userId)){
            $data['is_login'] = 1;
            $signinNow = AskSigninLog::whereDate('created_at',Date::now())->where('user_id',$userId)->first();
            if($signinNow != null){
                $data['is_signin'] = 1;
                $data['signin_days'] = $signinNow->days;
            }
        }
        $signinLogList = AskSigninLog::whereDate('created_at',Date::now())->orderBy('days','desc')->limit(10)->get();
        $data['rank_list'] = $signinLogList;
        return $data;
    }


    /**
     * 签到
     */
    public static function postSignin()
    {
        try {
            $user = AuthLogic::getInstance()->user();
            if($user == null){
                throw new \Exception('请先登入');
            }
            $signinNow = AskSigninLog::whereDate('created_at',Date::now())->where('user_id',$user->id)->first();
            if($signinNow != null){
                throw new \Exception('今日已签到，请勿重复签到');
            }
            //查看昨日是否签到
            $yesterday = AskSigninLog::whereDate('created_at',Date::now()->subDay())->where('user_id',$user->id)->first();
            $signingDays = $yesterday != null  ? $yesterday->days+1 : 1;
            $signinRule = collect(config('phcentask.signinRule'))->sortDesc();
            $addPoints = 0;
            foreach ($signinRule as $k=>$v){
                if($k <= $signingDays){
                    $addPoints = $v;
                    break;
                }
            }
            AskSigninLog::create([
                'user_id' => $user->id,
                'days' => $signingDays,
                'user_name' => $user->nick_name,
                'user_avatar' => $user->avatar,
                'points' => $addPoints
            ]);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
}