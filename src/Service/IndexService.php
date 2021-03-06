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


use Illuminate\Support\Facades\Date;
use Phcent\WebmanAsk\Model\AskArticle;
use Phcent\WebmanAsk\Model\AskCategoryRole;
use Phcent\WebmanAsk\Model\AskFollower;
use Phcent\WebmanAsk\Model\AskQuestion;
use Phcent\WebmanAsk\Model\AskTags;
use Phcent\WebmanAsk\Model\AskUser;
use Phcent\WebmanAsk\Model\SysUser;
use support\Redis;

class IndexService
{
    /**
     * 获取热门问题 60分钟
     * @param int $limit
     * @return mixed
     */
    public static function getHotQuestion($limit = 10)
    {

        $list = Redis::get("phcentAskHotQuestion");
        if($list == null){
            $list = AskQuestion::orderBy('hot_sort','desc')->orderBy('view_num','desc')->where('status',1)->orderBy('id','desc')->limit(20)
                ->get(['id','title','user_id','cate_id','view_num','answer_num','created_at'])
                ->toJson();
            Redis::setEx("phcentAskHotQuestion",3600,$list);
        }
        $list = json_decode($list);
        return collect($list)->take($limit)->all();
    }

    /**
     * 获取最新待解决问题 5分钟
     * @param int $limit
     * @return mixed
     */
    public static function getNewQuestion($limit = 10)
    {
        $list = Redis::get("phcentAskNewQuestion");
        if($list == null){
            $list = AskQuestion::orderBy('id','desc')->where('status',1)->limit(20)->get(['id','title','user_id','cate_id','view_num','answer_num','created_at'])->toJson();
            Redis::setEx("phcentAskNewQuestion",300,$list);
        }
        $list = json_decode($list);
        return collect($list)->take($limit)->all();
    }

    /**
     * 获取热门文章 60分钟
     * @param int $limit
     * @return mixed
     */
    public static function getHotArticle($limit = 10)
    {
        $list = Redis::get("phcentAskHotArticle");
        if($list == null){
            $list = AskArticle::orderBy('hot_sort','desc')->orderBy('id','desc')->where('status',1)->limit(20)->get(['id','title','user_id','cate_id','view_num','summary','created_at'])->toJson();
            Redis::setEx("phcentAskHotArticle",3600,$list);
        }
        $list = json_decode($list);
        return collect($list)->take($limit)->all();
    }

    /**
     * 获取热门标签 60分钟
     * @param int $limit
     * @return mixed
     */
    public static function getHotTags($limit = 50)
    {
        $list = Redis::get("phcentAskHotTags");
        if($list == null){
            $list = AskTags::orderBy('hot_sort','desc')->orderBy('id','desc')->where('status',1)->limit(100)->get()->toJson();
            Redis::setEx("phcentAskHotTags",3600,$list);
        }
        $list = json_decode($list);
        return collect($list)->take($limit)->all();
    }

    /**
     * 获取推荐专家
     * @param $limit
     * @return \Illuminate\Support\Collection
     */
    public static function getExpertOnline($limit = 5)
    {
        $list = Redis::get("phcentAskExpert");
        if($list == null){
            $list = AskUser::with('user')->whereHas('user')->where('expert_status',1)->limit(20)->orderBy('hot_sort','desc')->orderBy('answer_best_num','desc')->get();
            $data = collect([]);
            foreach ($list as $item){
                $data->push([
                    'user_id'=>$item->id,
                    'user_name' => $item->user->nick_name,
                    'avatar_url' => $item->user->avatar_url,
                    'is_online' => phcentIsUserOnline($item->id),
                    'answer_num' => $item->answer_num,
                    'answer_best_num' => $item->answer_best_num,
                ]);
            }
            Redis::setEx("phcentAskExpert",3600,$data->toJson());
        }else{
            $data = collect([]);
            $list =json_decode($list);
            foreach ($list as $val){
                $data->push([
                    'user_id' => $val->user_id,
                    'user_name' =>  $val->user_name,
                    'avatar_url' =>  $val->avatar_url,
                    'is_online' => phcentIsUserOnline( $val->user_id),
                    'answer_num' =>  $val->answer_num,
                    'answer_best_num' =>  $val->answer_best_num,
                ]);
            }
        }
        return $data->take($limit);
    }

    /**
     * 判断是否有操作权限
     * @param $info
     * @param $userId
     * @param bool $cateRole
     * @return bool
     */
    public static function isHaveRole($info,$userId,$cateRole = true)
    {
        if($info->user_id != $userId){ //不是自己时
            $askUser = AskUser::firstOrCreate(['id'=>$userId]);
            if($askUser->is_admin !== 1){ //不是管理员时
                if(!$cateRole) return false;
                $askCategoryRole = AskCategoryRole::where('category_id',$info->cate_id)->where('user_id',$userId)->first();
                if($askCategoryRole == null){ //也不是分类管理员
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 是否有管理权限
     * @param $userId
     * @param $cateId
     * @return bool
     * @throws \Exception
     */
    public static function isHaveAdminRole($userId,$cateId = 0)
    {
        if(empty($userId)){
            return false;
        }
        $askUser = AskUser::firstOrCreate(['id'=>$userId]);
        if($askUser->is_admin !== 1){ //不是管理员时
            if($cateId > 0){
                $askCategoryRole = AskCategoryRole::where('category_id',$cateId)->where('user_id',$userId)->first();
                if($askCategoryRole == null){ //也不是分类管理员
                    return false;
                }
            }else{
                return false;
            }

        }
        return true;
    }

    /**
     * 获取用户卡片信息
     * @param $id
     * @param $userId
     * @return mixed
     */
    public static function getUserCard($id,$userId)
    {
        $user = SysUser::where('id',$id)->first();
        $askUser = AskUser::firstOrCreate(['id'=>$id]);
        $data['user_name'] = $user->nick_name;
        $data['user_id'] = $user->id;
        $data['created_at'] = Date::parse($user->created_at)->toDateString();
        $data['avatar_url'] = $user->avatar_url;
        $data['user_points'] = $user->points;
        $data['login_num'] = $user->login_num;

        $data['question_num'] = $askUser->question_num;
        $data['article_num'] = $askUser->article_num;
        $data['answer_best_num'] = $askUser->answer_best_num;
        $data['description'] = $user->description;
        $data['expert_status'] = $askUser->expert_status;
        $data['is_follow'] = 0;
        if($userId  > 0){ //判断是否关注
            $follow = AskFollower::where('user_id',$userId)->where('theme_id',$id)->where('type',7)->first();
            if($follow != null){
                $data['is_follow'] = 1;
            }
        }
        return $data;
    }
}