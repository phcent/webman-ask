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


use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\AskArticle;
use Phcent\WebmanAsk\Model\AskCategory;
use Phcent\WebmanAsk\Model\AskReply;
use Phcent\WebmanAsk\Model\AskTags;
use Phcent\WebmanAsk\Model\AskTagsQa;
use Phcent\WebmanAsk\Model\User;
use support\Db;

class ArticleService
{


    /**
     * 新增文章
     * @param $params
     * @param $userId
     * @return
     * @throws \Exception
     */
    public static function createArticle($params,$userId)
    {
        $user = User::where('id',$userId)->first();
        if($user == null){
            throw new \Exception('会员不存在');
        }
        $category = AskCategory::where('type',2)->where('id',$params['cate_id'])->first();
        if($category == null){
            throw new \Exception('分类不存在');
        }
        $article = AskArticle::create([
            'title' => $params['title'],
            'content' => $params['content'],
            'user_id' => $user->id,
            'cate_id' => $params['cate_id'],
            'reward_balance' => $params['reward_balance'],
            'reward_points' => $params['reward_points'],
            'keyword' => $params['keyword'],
            'description' => $params['description'],
        ]);
        if(isset($params['tags'])){
            if(!is_array($params['tags'])){
                throw new \Exception('话题数据异常');
            }
            foreach ($params['tags'] as $v){
                $tags = AskTags::where('name',$v)->first();
                if($tags == null){
                    //新增话题
                    $newTag = AskTags::create([
                        'name' => $v,
                        'article_num' => 1
                    ]);
                    //建立关联
                    AskTagsQa::create(['article_id' => $article->id,'tag_id'=>$newTag->id]);
                }else{
                    //增加问题数量
                    $tags->increment('article_num');
                    //建立关联
                    AskTagsQa::create(['article_id' => $article->id,'tag_id'=>$tags->id]);
                }
            }
        }
        //增加文章数量
        AskUserService::optionsNum($user->id,'article');
        //奖励发布文章积分
        PointsService::publishArticle($article);

        //增加会员动态
        DynamicService::create([
            'user_id' => $user->id,
            'type' => 1,
            'item_id' => $article->id,
            'operation_stage' => 'create',
            'title' => $article->title,
            'content' => ''
        ]);

        return $article;
    }

    /**
     * 补充文章
     * @param $params
     * @param $id
     * @param $userId
     * @throws \Exception
     */
    public static function updateArticle($params,$id,$userId)
    {
        $info = AskArticle::where('id',$id)->first();
        if($info == null){
            throw new \Exception('文章不存在');
        }
        //判断是否有修改权限
        $haveRole = IndexService::isHaveRole($info,$userId);
        if(!$haveRole){
            throw new \Exception('无权限修改');
        }
        if(isset($params['tags'])){
            if(!is_array($params['tags'])){
                throw new \Exception('话题数据异常');
            }
            $askTagsQa = AskTagsQa::where('article_id',$id)->get()->pluck('tag_id');
            foreach ($params['tags'] as $v){
                $tags = AskTags::where('name',$v)->first();
                if($tags == null){
                    //新增话题
                    $newTag = AskTags::create([
                        'name' => $v,
                        'article_num' => 1
                    ]);
                    //建立关联
                    AskTagsQa::create(['article_id' => $id,'tag_id'=>$newTag->id]);
                }else{
                    if($askTagsQa->contains($tags->id)){
                        $askTagsQa->forget($tags->id);
                    }else{
                        //增加问题数量
                        $tags->increment('article_num');
                        //建立关联
                        AskTagsQa::create(['article_id' => $id,'tag_id'=>$tags->id]);
                    }
                }
            }
            //剩余则为本次去除了的话题
            if($askTagsQa->count() > 0){
                AskTagsQa::where('article_id',$id)->whereIn('tag_id',$askTagsQa)->delete();
                AskTags::whereIn('id',$askTagsQa)->decrement('article_num');
            }
            unset($params['tags']);
        }
        foreach ($params as $k=>$v){
            $info->$k = $v;
        }
        $info->save();
        //增加会员动态
        DynamicService::create([
            'user_id' => $userId,
            'type' => 1,
            'item_id' => $info->id,
            'operation_stage' => 'update',
            'title' => $info->title,
            'content' => ''
        ]);
    }

    /**
     * 删除文章
     * @param $id
     * @param $userId
     * @throws \Exception
     */
    public static function destroyArticle($id,$userId)
    {
        $info = AskArticle::where('id',$id)->first();
        if($info == null){
            throw new \Exception('文章不存在');
        }
        //判断是否有修改权限
        $haveRole = IndexService::isHaveAdminRole($userId,$info->cate_id);
        if(!$haveRole){
            throw new \Exception('无权限删除');
        }
        AskArticle::destroy($id);

        //删除评论
        $askCommentReply = AskReply::where('theme_id', $id)->where('type', 2)->get();
        AskReply::destroy($askCommentReply->pluck('id'));
    }
}