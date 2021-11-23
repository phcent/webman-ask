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


namespace Phcent\WebmanAsk\Controllers\User\V1;


use Illuminate\Support\Facades\Date;
use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\AskSigninLog;
use Phcent\WebmanAsk\Service\SigninService;
use support\Request;

class SigninController
{
    /**
     * 获取签到
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $user = AuthLogic::getInstance()->user();
            if($user == null){
                throw new \Exception('会员未登入');
            }
            $signinLog = new AskSigninLog();
            $date = $request->input('date',Date::now());
            $list = $signinLog->where('user_id',$user->id)->whereYear('created_at',Date::parse($date)->format('Y'))->whereMonth('created_at',Date::parse($date)->format('m'))->get();
            $data['list'] = $list;
            $data['signinRule'] = config('phcentask.signinRule');
            return phcentSuccess( $data);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 新增签到
     * @param Request $request
     * @return \support\Response
     */
    public function create(Request $request)
    {
        try {
            phcentMethod(['POST']);
            SigninService::postSignin();
            return phcentSuccess();
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

}