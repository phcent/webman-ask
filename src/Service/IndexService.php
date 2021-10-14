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


use Phcent\WebmanAsk\Model\AskArticle;
use Phcent\WebmanAsk\Model\AskCategory;
use Phcent\WebmanAsk\Model\AskCategoryRole;
use Phcent\WebmanAsk\Model\AskFollower;
use Phcent\WebmanAsk\Model\AskQuestion;
use Phcent\WebmanAsk\Model\AskTags;
use Phcent\WebmanAsk\Model\AskUser;
use Phcent\WebmanAsk\Model\User;
use support\bootstrap\Redis;

class IndexService
{
    /**
     * 获取热门问题
     * @param int $limit
     * @return mixed
     */
    public static function getHotQuestion($limit = 10)
    {
        $list = Redis::get('phcentAskHotQuestion'.$limit);
        if($list == null){
            $list = AskQuestion::orderBy('hot_sort','desc')->where('status',1)->orderBy('id','desc')->limit($limit)->get()->toJson();
            Redis::set('phcentAskHotQuestion'.$limit,$list,300);
        }
        return json_decode($list);
    }

    /**
     * 获取热门问题
     * @param int $limit
     * @return mixed
     */
    public static function getNewQuestion($limit = 10)
    {
        $list = Redis::get('phcentAskNewQuestion'.$limit);
        if($list == null){
            $list = AskQuestion::orderBy('id','desc')->where('status',1)->limit($limit)->get()->toJson();
            Redis::set('phcentAskNewQuestion'.$limit,$list,300);
        }
        return json_decode($list);
    }

    /**
     * 获取热门文章
     * @param int $limit
     * @return mixed
     */
    public static function getHotArticle($limit = 10)
    {
        $list = Redis::get('phcentAskHotArticle'.$limit);
        if($list == null){
            $list = AskArticle::orderBy('hot_sort','desc')->orderBy('id','desc')->where('status',1)->limit($limit)->get()->toJson();
            Redis::set('phcentAskHotArticle'.$limit,$list,300);
        }
        return json_decode($list);
    }

    /**
     * 获取热门标签
     * @param int $limit
     * @return mixed
     */
    public static function getHotTags($limit = 50)
    {
        $list = Redis::get('phcentAskHotTags'.$limit);
        if($list == null){
            $list = AskTags::orderBy('hot_sort','desc')->orderBy('id','desc')->where('status',1)->limit($limit)->get()->toJson();
            Redis::set('phcentAskHotTags'.$limit,$list,300);
        }
        return json_decode($list);
    }

    /**
     * 获取推荐专家
     * @param $limit
     * @return \Illuminate\Support\Collection
     */
    public static function getExpertOnline($limit = 5)
    {
        $list = AskUser::with('user')->whereHas('user')->where('is_expert',1)->limit(5)->orderBy('hot_sort','desc')->orderBy('answer_best_num','desc')->get();
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
        return $data;
    }

    /**
     * 判断是否有操作权限
     * @param $info
     * @param $userId
     * @return bool
     */
    public static function isHaveRole($info,$userId)
    {
        if($info->user_id != $userId){ //不是自己时
            $askUser = AskUser::firstOrCreate(['id'=>$userId]);
            if(empty($askUser->is_admin)){ //不是管理员时
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
    public static function isHaveAdminRole($userId,$cateId)
    {
        $askUser = AskUser::firstOrCreate(['id'=>$userId]);
        if($askUser->is_admin !== 1){ //不是管理员时
            $askCategoryRole = AskCategoryRole::where('category_id',$cateId)->where('user_id',$userId)->first();
            if($askCategoryRole == null){ //也不是分类管理员
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
        $user = User::where('id',$id)->first();
        $askUser = AskUser::firstOrCreate(['id'=>$id]);
        $data['user_name'] = $user->nick_name;
        $data['user_id'] = $user->id;
        $data['created_at'] = $user->created_at;
        $data['avatar_url'] = $user->avatar_url;
        $data['user_points'] = $user->points;
        $data['login_num'] = $user->login_num;

        $data['question_num'] = $askUser->question_num;
        $data['article_num'] = $askUser->article_num;
        $data['answer_best_num'] = $askUser->answer_best_num;
        $data['description'] = $user->description;
        $data['is_expert'] = $askUser->is_expert;
        $data['is_follow'] = 0;
        if($userId  > 0){ //判断是否关注
            $follow = AskFollower::where('user_id',$userId)->where('to_user_id',$id)->first();
            if($follow != null){
                $data['is_follow'] = 1;
            }
        }
        return $data;
    }
}