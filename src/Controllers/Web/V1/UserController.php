<?php
/**
 *-------------------------------------------------------------------------p*
 * 我的主页
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


namespace Phcent\WebmanAsk\Controllers\Web\V1;

use Illuminate\Support\Facades\Date;
use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\AskAnswer;
use Phcent\WebmanAsk\Model\AskArticle;
use Phcent\WebmanAsk\Model\AskCollection;
use Phcent\WebmanAsk\Model\AskDynamic;
use Phcent\WebmanAsk\Model\AskFollower;
use Phcent\WebmanAsk\Model\AskQuestion;
use Phcent\WebmanAsk\Service\AnswerService;
use Phcent\WebmanAsk\Service\AskUserService;
use Phcent\WebmanAsk\Service\FollowerService;
use Phcent\WebmanAsk\Service\IndexService;
use support\Request;

class UserController
{
    /**
     * 我的主页动态列表
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function index(Request $request, $id)
    {
        try {
            phcentMethod(['GET']);
            if(!is_numeric($id) && empty($id)){
                throw new \Exception('编号不正确');
            }
            $askDynamic = new AskDynamic();
            $params = phcentParams(['page' => 1,'limit' =>10,'cate_id']);
            $askDynamic = phcentWhereParams($askDynamic,$params);
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askDynamic = $askDynamic->orderBy(request()->input('sortName'),request()->input('sortOrder'));
            }else{
                $askDynamic = $askDynamic->orderBy('id','desc');
            }
            $list  = $askDynamic->where('user_id',$id)->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $data['list'] = $list->items();
            $data['dynamicType'] = config('phcentask.allType');
            $data['dynamicStage'] = config('phcentask.dynamicStage');
            $data['userInfo'] = AskUserService::getUInfo($id);
            return phcentSuccess($data,'动态列表',[ 'page' => $list->currentPage(),'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }


    /**
     * 个人主页问题
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function question(Request $request, $id)
    {
        try {
            phcentMethod(['GET']);
            if(!is_numeric($id) && empty($id)){
                throw new \Exception('编号不正确');
            }
            $askQuestion = new AskQuestion();
            $type = $request->input('type','new');
            switch ($type){
                case 'hot':
                    $askQuestion = $askQuestion->where('status','<>',0)->where('hot_sort','>',0)->orderBy('hot_sort','desc')->orderBy('id','desc')->orderBy('view_num','desc');
                    break;
                case 'price':
                    $askQuestion = $askQuestion->where(function ($query){
                        return $query->where('reward_balance','>','0')->orWhere('reward_points','>','0');
                    })->where('status','<>',0)->orderBy('id','desc');
                    break;
                case 'unsolved':
                    $askQuestion = $askQuestion->where('status',1)->orderBy('id','desc');
                    break;
                case 'unanswer':
                    $askQuestion = $askQuestion->where('answer_num',0)->where('status','<>',0)->orderBy('id','desc');
                    break;
                case 'solved':
                    $askQuestion = $askQuestion->where('status',2)->orderBy('id','desc');
                    break;
                case 'unsettled':
                    $askQuestion = $askQuestion->where('reward_time','<',Date::now()->subDays(config('phcentask.rewardTime',7)))->where('status','<>',2)->orderBy('id','desc');
                    break;
                default:
                    $askQuestion = $askQuestion->where('status','<>',0)->orderBy('id','desc');
                    break;
            }
            $list  = $askQuestion->where('user_id',$id)->with(['tags','user'])->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $list->map(function ($item){
                if($item->tags != null){
                    $item->tags->map(function ($item2){
                        $item2->setVisible(['id','name']);
                    });
                }
                if($item->user == null){
                    $item->user_name = '异常';
                }else{
                    $item->user_name = $item->user->nick_name;
                }
                $item->setHidden(['user']);
            });
            $data['list'] = $list->items();
            $data['userInfo'] = AskUserService::getUInfo($id);
            return phcentSuccess($data,'问题列表',[ 'page' => $list->currentPage(),'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 个人主页文章模块
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function article(Request $request, $id)
    {
        try {
            phcentMethod(['GET']);
            if(!is_numeric($id) && empty($id)){
                throw new \Exception('编号不正确');
            }
            $askArticle = new AskArticle();
            $params = phcentParams(['page' => 1,'limit' =>10,'cate_id']);
            $askArticle = phcentWhereParams($askArticle,$params);
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askArticle = $askArticle->orderBy(request()->input('sortName'),request()->input('sortOrder'));
            }else{
                $askArticle = $askArticle->orderBy('id','desc');
            }
            $userId = AuthLogic::getInstance()->userId();
            $list  = $askArticle->where('user_id',$id)->with(['tags',
                'collection' => function($query) use ($userId) {
                    $query->where('user_id',$userId);
                }])->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $list->map(function ($item) use ($userId, $id) {
                if($item->tags != null){
                    $item->tags->map(function ($item2){
                        $item2->setVisible(['id','name']);
                    });
                }
                $item->is_collection = 0; //是否收藏
                $item->show_edit = $id == $userId ? 1: 0;
                $item->show_delete = $id == $userId ? 1: 0;
                $item->is_collection =  $item->collection->count() > 0 ? 1 : 0; //是否收藏
                $item->setHidden(['collection']);
            });
            $data['list'] = $list->items();
            $data['userInfo'] = AskUserService::getUInfo($id);
            return phcentSuccess($data,'文章列表',[ 'page' => $list->currentPage(),'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 查询我的回答
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function answer(Request $request, $id)
    {
        try {
            phcentMethod(['GET']);
            if(!is_numeric($id) && empty($id)){
                throw new \Exception('编号不正确');
            }
            $userId = AuthLogic::getInstance()->userId();
            $adminRole = IndexService::isHaveAdminRole($userId,0);
            $askAnswer = new AskAnswer();
            $params = phcentParams(['cate_id']);
            $askAnswer = phcentWhereParams($askAnswer,$params);
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askAnswer = $askAnswer->orderBy(request()->input('sortName'),request()->input('sortOrder'));
            }else{
                $askAnswer = $askAnswer->orderBy('id','desc');
            }
            $list  = $askAnswer->where('user_id',$id)->with(['user','question',
                'collection'=>function($query) use ($userId) {
                    $query->where('user_id',$userId);
                }
            ])->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $list->map(function ($item) use ($adminRole, $userId) {
                $item->question_title = $item->question == null ?'':$item->question->title;
                $item = AnswerService::calcItem($item,$userId,$adminRole,$item->question);
                $item->setHidden(['user','question','collection']);
            });
            $data['list'] = $list->items();
            $data['userInfo'] = AskUserService::getUInfo($id);
            return phcentSuccess($data,'回答列表',[ 'page' => $list->currentPage(),'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 获取我的关注列表
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function follow(Request $request, $id)
    {
        try {
            phcentMethod(['GET']);
            if(!is_numeric($id) && empty($id)){
                throw new \Exception('编号不正确');
            }
            $askFollower = new AskFollower();
            $params = phcentParams(['page' => 1,'limit' =>10,'type'=>'user']);
            if(!in_array($params['type'],['user','question'])){
                throw new \Exception('数据异常');
            }
            if($params['type'] == 'user'){
                $list  = $askFollower->where('user_id',$id)->where('type',7)->with('toUser')->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
                $list->map(function ($item) use ($id) {
                    if($item->toUser == null){
                        FollowerService::deleteFollower($id,$item->theme_id,'user');
                        AskFollower::destroy($item->id);
                        $item->user_name = '';
                        $item->avatar_url = '';
                    }else{
                        $item->user_name = $item->toUser->nick_name;
                        $item->avatar_url = $item->toUser->avatar_url;
                    }

                    $item->setHidden(['toUser']);
                });
            }else{
                $userId =AuthLogic::getInstance()->userId();
                $list  = $askFollower->where('user_id',$id)->where('type',1)->with(['question.follow'=>function($query) use ($userId) {
                    $query->where('user_id',$userId);
                }])->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
                $list->map(function ($item) use ($id) {
                    if($item->question == null){
                        FollowerService::deleteFollower($id,$item->theme_id,'question');
                        $item->title = '';
                    }else{
                        $item->title = $item->question->title;
                        $item->is_follow = $item->question->follow->count() > 0 ?1:0;
                    }

                    $item->setHidden(['question']);
                });
            }

            $data['list'] = $list->items();
            $data['userInfo'] = AskUserService::getUInfo($id);
            return phcentSuccess($data,'关注列表',[ 'page' => $list->currentPage(),'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 我的粉丝
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function fans(Request $request, $id)
    {
        try {
            phcentMethod(['GET']);
            if(!is_numeric($id) && empty($id)){
                throw new \Exception('编号不正确');
            }
            $askFollower = new AskFollower();
            $list  = $askFollower->where('theme_id',$id)->where('type',7)->with('user')->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $list->map(function ($item){
                if($item->user == null){
                    AskFollower::destroy($item->id);
                    $item->user_name = '';
                    $item->avatar_url = '';
                }else{
                    $item->user_name = $item->user->nick_name;
                    $item->avatar_url = $item->user->avatar_url;
                }
                $item->setHidden(['user']);
            });
            $data['list'] = $list->items();
            $data['userInfo'] = AskUserService::getUInfo($id);
            return phcentSuccess($data,'粉丝列表',[ 'page' => $list->currentPage(),'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }


}