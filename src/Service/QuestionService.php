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
use Phcent\WebmanAsk\Model\AskCategory;
use Phcent\WebmanAsk\Model\AskAnswer;
use Phcent\WebmanAsk\Model\AskReply;
use Phcent\WebmanAsk\Model\AskQuestion;
use Phcent\WebmanAsk\Model\AskTags;
use Phcent\WebmanAsk\Model\AskTagsQa;
use Phcent\WebmanAsk\Model\SysUser;
use support\Db;

class QuestionService
{

    /**
     * 新增问题
     * @param $params
     * @param $userId
     * @return
     * @throws \Exception
     */
    public static function createQuestion($params,$userId)
    {
        $user = SysUser::where('id',$userId)->first();
        if($user == null){
            throw new \Exception('会员不存在');
        }
        if($params['reward_balance'] > 0 && $params['reward_balance'] > $user->available_balance){
            throw new \Exception('可用余额不足');
        }
        if($params['reward_points'] > 0 && $params['reward_points'] > $user->reward_points){
            throw new \Exception('可用积分不足');
        }
        $category = AskCategory::where('type',1)->where('id',$params['cate_id'])->first();
        if($category == null){
            throw new \Exception('分类不存在');
        }
        $question = AskQuestion::create([
            'title' => $params['title'],
            'content' => $params['content'],
            'user_id' => $user->id,
            'cate_id' => $params['cate_id'],
            'reward_balance' => $params['reward_balance'],
            'reward_points' => $params['reward_points'],
            'reward_time' => Date::now(),
            'keyword' => $params['keyword'],
            'description' => $params['description'],
            'is_private' => $params['is_private'],
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
                        'question_num' => 1
                    ]);
                    //建立关联
                    AskTagsQa::create(['question_id' => $question->id,'tag_id'=>$newTag->id]);
                }else{
                    //增加问题数量
                    $tags->increment('question_num');
                    //建立关联
                    AskTagsQa::create(['question_id' => $question->id,'tag_id'=>$tags->id]);
                }
            }
        }
        if($params['reward_balance'] > 0){
            BalanceService::postReward($question,$user);
        }
        if($params['reward_points'] > 0){
            PointsService::postReward($question,$user);
        }
        //增加问题数量
        AskUserService::optionsNum($user->id,'question');
        //奖励发布问题积分
        PointsService::publishQuestion($question);

        //增加会员动态
        DynamicService::create([
            'user_id' => $user->id,
            'type' => 1,
            'item_id' => $question->id,
            'operation_stage' => 'create',
            'title' => $question->title,
            'content' => ''
        ]);

        return $question;
    }

    /**
     * 补充问题
     * @param $params
     * @param $id
     * @param $userId
     * @throws \Exception
     */
    public static function updateQuestion($params,$id,$userId)
    {
        $info = AskQuestion::where('id',$id)->first();
        if($info == null){
            throw new \Exception('问题不存在');
        }
        //判断是否有修改权限
        $haveRole = IndexService::isHaveRole($info,$userId);
        if(!$haveRole){
            throw new \Exception('无权限修改');
        }
        try {
            Db::connection()->beginTransaction();
            if(isset($params['tags'])){
                if(!is_array($params['tags'])){
                    throw new \Exception('话题数据异常');
                }
                $askTagsQa = AskTagsQa::where('question_id',$id)->get()->pluck('tag_id');
                foreach ($params['tags'] as $v){
                    $tags = AskTags::where('name',$v)->first();
                    if($tags == null){
                        //新增话题
                        $newTag = AskTags::create([
                            'name' => $v,
                            'question_num' => 1
                        ]);
                        //建立关联
                        AskTagsQa::create(['question_id' => $id,'tag_id'=>$newTag->id]);
                    }else{
                        if($askTagsQa->contains($tags->id)){
                            $askTagsQa->forget($tags->id);
                        }else{
                            //增加问题数量
                            $tags->increment('question_num');
                            //建立关联
                            AskTagsQa::create(['question_id' => $id,'tag_id'=>$tags->id]);
                        }
                    }
                }
                //剩余则为本次去除了的话题
                if($askTagsQa->count() > 0){
                    AskTagsQa::where('question_id',$id)->whereIn('tag_id',$askTagsQa)->delete();
                    AskTags::whereIn('id',$askTagsQa)->decrement('question_num');
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
            Db::connection()->commit();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            throw  new \Exception($e->getMessage());
        }
    }

    /**
     * 删除问题
     * @param $id
     * @param $userId
     * @throws \Exception
     */
    public static function destroyQuestion($id,$userId)
    {
        $info = AskQuestion::where('id',$id)->first();
        if($info == null){
            throw new \Exception('问题不存在');
        }
        //判断是否有修改权限
        $haveRole = IndexService::isHaveAdminRole($userId,$info->cate_id);
        if(!$haveRole){
            throw new \Exception('无权限删除');
        }
        AskQuestion::destroy($id);
        //删除回答
        $askComment = AskAnswer::where('question_id', $id)->get();
        AskAnswer::destroy($askComment->pluck('id'));

        //删除评论
        $askCommentReply = AskReply::whereIn('theme_id', $askComment->pluck('id'))->where('type', 1)->get();
        AskReply::destroy($askCommentReply->pluck('id'));
    }

    /**
     * 关闭问题
     * @param $id
     * @param $userId
     * @throws \Exception
     */
    public static function closeQuestion($id,$userId)
    {
        $info = AskQuestion::where('id',$id)->first();
        if($info == null){
            throw new \Exception('问题不存在');
        }
        //判断是否有修改权限
        $haveRole = IndexService::isHaveAdminRole($userId,$info->cate_id);
        if(!$haveRole){
            throw new \Exception('无权限关闭');
        }
        $info->status = 3;
        $info->save();
    }

    /**
     * 打开问题
     * @param $id
     * @param $userId
     * @throws \Exception
     */
    public static function openQuestion($id,$userId)
    {
        $info = AskQuestion::where('id',$id)->first();
        if($info == null){
            throw new \Exception('问题不存在');
        }
        //判断是否有修改权限
        $haveRole = IndexService::isHaveAdminRole($userId,$info->cate_id);
        if(!$haveRole){
            throw new \Exception('无权限打开');
        }
        $info->status = 1;
        $info->save();
    }


}