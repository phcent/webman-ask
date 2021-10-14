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


use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\AskFollower;
use Phcent\WebmanAsk\Model\AskUser;
use Phcent\WebmanAsk\Model\User;

class AskUserService
{
    /**
     * 会员相关计数操作
     * @param $userId
     * @param string $type
     * @param string $mode
     * @param int $num
     * @throws \Exception
     */
    public static function optionsNum($userId,$mode = 'question',$type = 'add',$num = 1)
    {
        $askUser = AskUser::firstOrCreate([
            'id' => $userId
        ]);
        switch ($mode){
            case 'question':
                if($type == 'add'){
                    $askUser->increment('question_num',$num);
                }else{
                    $askUser->decrement('question_num',$num);
                }
                $askUser->save();
                break;
            case 'article':
                if($type == 'add'){
                    $askUser->increment('article_num',$num);
                }else{
                    $askUser->decrement('article_num',$num);
                }
                $askUser->save();
                break;
            case 'answer':
                if($type == 'add'){
                    $askUser->increment('answer_num',$num);
                }else{
                    $askUser->decrement('answer_num',$num);
                }
                $askUser->save();
                break;
            case 'reply':
                if($type == 'add'){
                    $askUser->increment('reply_num',$num);
                }else{
                    $askUser->decrement('reply_num',$num);
                }
                $askUser->save();
                break;
            case 'collection':
                if($type == 'add'){
                    $askUser->increment('collection_num',$num);
                }else{
                    $askUser->decrement('collection_num',$num);
                }
                $askUser->save();
                break;
            case 'follow':
                if($type == 'add'){
                    $askUser->increment('follow_num',$num);
                }else{
                    $askUser->decrement('follow_num',$num);
                }
                $askUser->save();
                break;
            case 'view':
                if($type == 'add'){
                    $askUser->increment('view_num',$num);
                }else{
                    $askUser->decrement('view_num',$num);
                }
                $askUser->save();
                break;
            case 'fans':
                if($type == 'add'){
                    $askUser->increment('fans_num',$num);
                }else{
                    $askUser->decrement('fans_num',$num);
                }
                $askUser->save();
                break;
            case 'answer_best':
                if($type == 'add'){
                    $askUser->increment('answer_best_num',$num);
                }else{
                    $askUser->decrement('answer_best_num',$num);
                }
                $askUser->save();
                break;
            default:
                throw new \Exception('操作异常');
                break;
        }
    }

    /**
     * 获取会员用户信息
     * @return int
     * @throws \Exception
     */
    public static function getUInfo($userId)
    {
        $uid = AuthLogic::getInstance()->userId();
        $user = User::where('id',$userId)->first();
        if($user == null){
            throw new \Exception('会员信息异常');
        }
        Date::setLocale('zh');
        $askUser = AskUser::firstOrCreate(['id'=>$userId]);
        $data['user_name'] = $user->nick_name;
        $data['user_id'] = $user->id;
        $data['created_at'] = Date::parse($user->created_at)->diffForHumans();
        $data['avatar_url'] = $user->avatar_url;

        $data['question_num'] = $askUser->question_num;
        $data['article_num'] = $askUser->article_num;
        $data['fans_num'] = $askUser->fans_num;
        $data['description'] = $user->description;
        $data['is_expert'] = $askUser->is_expert;
        $data['is_follow'] = 0;
        if($uid  > 0){ //判断是否关注
            $follow = AskFollower::where('user_id',$userId)->where('to_user_id',$userId)->first();
            if($follow != null){
                $data['is_follow'] = 1;
            }
            if($userId != $uid){
                $askUser->increment('view_num');
            }
        }
        $data['view_num'] = $askUser->view_num;

        return $data;
    }


}