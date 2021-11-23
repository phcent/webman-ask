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
use Phcent\WebmanAsk\Logic\AuthLogic;
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
        $data = [
            'title' => $params['title'],
            'content' => $params['content'],
            'user_id' => $user->id,
            'cate_id' => $params['cate_id'],
            'reward_balance' => $params['reward_balance'],
            'reward_points' => $params['reward_points'],
            'seo_title' => $params['seo_title'],
            'seo_keyword' => $params['seo_keyword'],
            'seo_description' => $params['seo_description'],
            'is_private' => $params['is_private'],
        ];
        if($data['reward_balance'] > 0 || $data['reward_points'] > 0){
            $data['reward_time'] = Date::now();
        }
        $question = AskQuestion::create($data);
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
                    AskTagsQa::create(['theme_id' => $question->id,'type'=>1,'tag_id'=>$newTag->id]);
                }else{
                    //增加问题数量
                    $tags->increment('question_num');
                    //建立关联
                    AskTagsQa::create(['theme_id' => $question->id,'type'=>1,'tag_id'=>$tags->id]);
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
                $askTagsQa = AskTagsQa::where('theme_id',$id)->where('type',1)->get()->pluck('tag_id');
                foreach ($params['tags'] as $v){
                    $tags = AskTags::where('name',$v)->first();
                    if($tags == null){
                        //新增话题
                        $newTag = AskTags::create([
                            'name' => $v,
                            'question_num' => 1
                        ]);
                        //建立关联
                        AskTagsQa::create(['theme_id' => $id,'type'=>1,'tag_id'=>$newTag->id]);
                    }else{
                        if($askTagsQa->contains($tags->id)){
                            $askTagsQa->forget($tags->id);
                        }else{
                            //增加问题数量
                            $tags->increment('question_num');
                            //建立关联
                            AskTagsQa::create(['theme_id' => $id,'type'=>1,'tag_id'=>$tags->id]);
                        }
                    }
                }
                //剩余则为本次去除了的话题
                if($askTagsQa->count() > 0){
                    AskTagsQa::where('theme_id',$id)->where('type',1)->whereIn('tag_id',$askTagsQa)->delete();
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
        $haveRole = IndexService::isHaveRole($userId,$info->cate_id);
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
        $info->closed_at = Date::now();


        //退回悬赏金额
        if($info->reward_time != null && Date::now()->lte(Date::parse($info->reward_time)->subDays(config('phcentask.rewardTime')))){
            if($info->reward_balance > 0){
                BalanceService::backReward($info);
                $info->reward_balance = 0;
            }
            if($info->reward_points > 0){
                PointsService::backReward($info);
                $info->reward_points = 0;
            }
        }
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
        $info->closed_at = null;
        $info->save();
    }

    /**
     * 追加悬赏
     * @param $id
     * @param $params
     * @throws \Throwable
     */
    public static function rewardQuestion($id,$params)
    {
        try {
            $question = AskQuestion::where('id',$id)->first();
            if($question == null){
                throw new \Exception('问题不存在');
            }
            Db::connection()->beginTransaction();
            $user =  AuthLogic::getInstance()->lockUser();
            if($user == null){
                throw new \Exception('会员不存在');
            }
            if($question->reward_time != null && Date::parse($question->reward_time)->addDays(config('phcentask.rewardTime',7))->lt(Date::now())){
                throw new \Exception('悬赏时间已过期，不能追加悬赏');
            }
            if($params['type'] == 1){ //金额
                if($user->available_balance < $params['amount']){
                    throw new \Exception('可用金额不足，请先充值');
                }
                BalanceService::appendReward($params['amount'],$id,$user);
                $question->increment('reward_balance',bcmul($params['amount'],100,0));
                $question->reward_time = Date::now();
            }else{ //积分
                if($user->available_points < $params['amount']){
                    throw new \Exception('可用积分不足');
                }
                PointsService::appendReward($params['amount'],$id,$user);
                $question->increment('reward_points',bcmul($params['amount'],100,0));
                $question->reward_time = Date::now();
            }
            $question->save();
            Db::connection()->commit();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            throw new \Exception($e->getMessage());
        }
    }


}