<?php

/**
 *-------------------------------------------------------------------------p*
 * 公共调用
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
use Phcent\WebmanAsk\Model\AskTags;
use Phcent\WebmanAsk\Model\User;
use Phcent\WebmanAsk\Service\AskDiggService;
use Phcent\WebmanAsk\Service\CollectionService;
use Phcent\WebmanAsk\Service\FollowerService;
use Phcent\WebmanAsk\Service\IndexService;
use Respect\Validation\Validator;
use support\Db;
use support\Request;

class AjaxController
{
    /**
     * 获取tag列表
     * @param Request $request
     * @return \support\Response
     */
    public function tags(Request $request)
    {
        try {
            $askTags = new AskTags();
            $params = phcentParams(['page' => 1,'limit' =>10,'name_like','cate_id']);
            $askTags = phcentWhereParams($askTags,$params);
            if ($request->input('sortName') && in_array($request->input('sortOrder'), array('asc', 'desc'))) {
                $askTags = $askTags->orderBy($request->input('sortName'),$request->input('sortOrder'));
            }else{
                $askTags = $askTags->orderBy('id','desc');
            }
            $list  = $askTags->paginate($params['limit']);

            $data['list'] = $list->items();
            return phcentSuccess($data,'话题列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 获取用户卡片
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function card(Request $request, $id)
    {
        try {
            if(!is_numeric($id) && empty($id)) {
                throw new \Exception('编号参数异常');
            }
            $userId = AuthLogic::getInstance()->userId();
            $data = IndexService::getUserCard($id,$userId);
            return phcentSuccess($data);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 查询会员信息
     * @param Request $request
     * @return \support\Response
     */
    public function user(Request $request)
    {
        try {
            $user = new User();
            $params = phcentParams(['page' => 1,'limit' =>10,'nick_name_like','id']);
            $user = phcentWhereParams($user,$params);
            if ($request->input('sortName') && in_array($request->input('sortOrder'), array('asc', 'desc'))) {
                $user = $user->orderBy($request->input('sortName'),$request->input('sortOrder'));
            }else{
                $user = $user->orderBy('id','desc');
            }
            $list  = $user->paginate($params['limit']);
            $list->map(function ($item){
                $item->setVisible(['nick_name','id','avatar_url']);
            });
            $data['list'] = $list->items();
            return phcentSuccess($data,'会员列表',[ 'page' => $list->currentPage(),'total' => $list->total()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 顶踩操作
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function digg(Request $request)
    {
        try {
            phcentMethod(['POST','DELETE']);
            $userId = AuthLogic::getInstance()->userId();
            if(empty($userId)){
                throw new \Exception('请先登入');
            }
            Validator::input($request->all(), [
                'theme_id' => Validator::digit()->min(1)->setName('项目编号'),
                'conduct' => Validator::stringType()->in(['up','down'])->setName('操作行为'),
                'type' => Validator::digit()->in([1,2,3,4])->setName('操作类型'),
            ]);
            $params = phcentParams(['theme_id','conduct','type']);
            Db::connection()->beginTransaction();
            if($request->method() == 'POST'){
                AskDiggService::create($userId,$params['theme_id'],$params['type'],$params['conduct']);
            }else{
                AskDiggService::delete($userId,$params['theme_id'],$params['type'],$params['conduct']);
            }

            Db::connection()->commit();
            return phcentSuccess();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }

    /**
     * 收藏
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function collection(Request $request)
    {
        try {
            phcentMethod(['POST','DELETE']);
            $userId = AuthLogic::getInstance()->userId();
            if(empty($userId)){
                throw new \Exception('请先登入');
            }
            Validator::input($request->all(), [
                'theme_id' => Validator::digit()->min(1)->setName('项目编号'),
                'type' => Validator::digit()->in([1,2,3,4])->setName('操作类型'),
            ]);
            $params = phcentParams(['theme_id','type']);
            Db::connection()->beginTransaction();
            if($request->method() == 'POST'){
                CollectionService::createCollection($userId,$params['theme_id'],$params['type']);
            }else{
                CollectionService::deleteCollection($userId,$params['theme_id'],$params['type']);
            }

            Db::connection()->commit();
            return phcentSuccess();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }

    }

    /**
     * 关注
     * @param Request $request
     * @param $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function follow(Request $request,$id)
    {
        try {
            phcentMethod(['POST','DELETE','PUT']);
            if(!is_numeric($id) && empty($id)) {
                throw new \Exception('编号参数异常');
            }
            $userId = AuthLogic::getInstance()->userId();
            if(empty($userId)){
                throw new \Exception('请先登入');
            }

            Validator::input($request->all(), [
                'theme_id' => Validator::digit()->min(1)->setName('项目编号'),
                'type' => Validator::stringType()->in(['user','question'])->setName('关注类型'),
            ]);

            $params = phcentParams(['theme_id','type']);
            Db::connection()->beginTransaction();
            if($request->method() == 'POST'){
                FollowerService::createFollower($userId,$params['theme_id'],$params['type']);
            }else{
                FollowerService::deleteFollower($userId,$params['theme_id'],$params['type']);
            }

            Db::connection()->commit();
            return phcentSuccess();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }




}