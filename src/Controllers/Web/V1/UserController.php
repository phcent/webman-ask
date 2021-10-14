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
 * @since      象讯·PHP商城系统Pro
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Controllers\Web\V1;

use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\AskAnswer;
use Phcent\WebmanAsk\Model\AskArticle;
use Phcent\WebmanAsk\Model\AskCollection;
use Phcent\WebmanAsk\Model\AskDynamic;
use Phcent\WebmanAsk\Model\AskFollower;
use Phcent\WebmanAsk\Model\AskQuestion;
use Phcent\WebmanAsk\Service\AskUserService;
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
            $list  = $askDynamic->where('user_id',$id)->paginate($params['limit']);
            $data['list'] = $list->items();
            $data['dynamicType'] = config('phcentask.dynamicType');
            $data['dynamicStage'] = config('phcentask.dynamicStage');
            $data['userInfo'] = AskUserService::getUInfo($id);
            return phcentSuccess($data,'动态列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
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
            $params = phcentParams(['page' => 1,'limit' =>10,'cate_id']);
            $askQuestion = phcentWhereParams($askQuestion,$params);
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askQuestion = $askQuestion->orderBy(request()->input('sortName'),request()->input('sortOrder'));
            }else{
                $askQuestion = $askQuestion->orderBy('id','desc');
            }
            $list  = $askQuestion->where('user_id',$id)->paginate($params['limit']);
            $data['list'] = $list->items();
            $data['userInfo'] = AskUserService::getUInfo($id);
            return phcentSuccess($data,'问题列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
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
            $list  = $askArticle->where('user_id',$id)->paginate($params['limit']);
            $data['list'] = $list->items();
            $data['userInfo'] = AskUserService::getUInfo($id);
            return phcentSuccess($data,'文章列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
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
            $askAnswer = new AskAnswer();
            $params = phcentParams(['page' => 1,'limit' =>10,'cate_id']);
            $askAnswer = phcentWhereParams($askAnswer,$params);
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $askAnswer = $askAnswer->orderBy(request()->input('sortName'),request()->input('sortOrder'));
            }else{
                $askAnswer = $askAnswer->orderBy('id','desc');
            }
            $list  = $askAnswer->where('user_id',$id)->paginate($params['limit']);
            $data['list'] = $list->items();
            $data['userInfo'] = AskUserService::getUInfo($id);
            return phcentSuccess($data,'回答列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
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
                $list  = $askFollower->where('user_id',$id)->where('to_user_id','>',0)->with('toUser')->paginate($params['limit']);
                $list->map(function ($item){
                    if($item->toUser == null){
                        AskCollection::destroy($item->id);
                        $item->user_name = '';
                        $item->avatar_url = '';
                    }else{
                        $item->user_name = $item->toUser->nick_name;
                        $item->avatar_url = $item->toUser->avatar_url;
                    }

                    $item->setHidden(['user']);
                });
            }else{
                $list  = $askFollower->where('user_id',$id)->where('question_id','>',0)->with('question')->paginate($params['limit']);
                $list->map(function ($item){
                    if($item->question == null){
                        AskCollection::destroy($item->id);
                        $item->title = '';
                    }else{
                        $item->title = $item->question->title;
                    }

                    $item->setHidden(['question']);
                });
            }

            $data['list'] = $list->items();
            $data['userInfo'] = AskUserService::getUInfo($id);
            return phcentSuccess($data,'关注列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
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
            $askCollection = new AskCollection();
            $params = phcentParams(['page' => 1,'limit' =>10]);
            $list  = $askCollection->where('to_user_id',$id)->with('user')->paginate($params['limit']);
            $list->map(function ($item){
                if($item->user == null){
                    AskCollection::destroy($item->id);
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
            return phcentSuccess($data,'粉丝列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }


}