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


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuttLogic extends Model
{
    protected $attributes = [
        'guard' => 'user',
        'is_login' => 0,
        'user_id' => 0,
        'user' => null,
    ];

    public function first()
    {
        return $this;
    }


    public function getUserAttribute($key)
    {
        $userData = $this->getUserData();
        if(!empty($userData)){
            $user = $this->getUserClass();
            if($user == null){
                return null;
            }
            return $user->where('id',$userData['id'])->first();
        }
        return null;
    }

    private function getUserClass(){
        $guardConfig = config('phcentask.guard.'.$this->guard);
        if(!empty($guardConfig)){
            return new $guardConfig;
        }
        return null;
    }

    private function getUserData()
    {
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
                //     $user = $this->getUserClass();
                return $arr;
            }
            return null;
        }catch (\Exception $e){
            return null;
        }
    }


}