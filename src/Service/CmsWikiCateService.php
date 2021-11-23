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
 * @since      象讯·PHP 知识付费问答系统-CMS管理
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Service;

use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\CmsWikiCate;
use Phcent\WebmanAsk\Service\UserService;
use support\Db;

class CmsWikiCateService
{
    /**
     * 新增文档分类
     * @param $params
     * @throws \Throwable
     */
    public static function create($params)
    {
        $user = AuthLogic::getInstance()->user();
        if($user == null){
            throw new \Exception('未登入');
        }
        try {
            Db::connection()->beginTransaction();
            $cate = CmsWikiCate::create($params);
            UserService::addLog("CMS管理-文档管理-新增文档分类(编号：{$cate->id})",'WikiController@cateCreate',$user->id,$user->nick_name,$params);
            Db::connection()->commit();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            throw new \Exception($e);
        }
    }

    /**
     * 修改文档分类
     * @param $params
     * @param $id
     * @throws \Throwable
     */
    public static function update($params,$id)
    {
        $info = CmsWikiCate::where('id',$id)->first();
        if($info == null){
            throw new \Exception('文档分类不存在');
        }
        $user = AuthLogic::getInstance()->user();
        if($user == null){
            throw new \Exception('未登入');
        }
        try {
            Db::connection()->beginTransaction();
            foreach ($params as $key=>$val){
                $info->$key = $val;
            }
            $info->save();
            UserService::addLog("CMS管理-文档管理-编辑文档分类（编号：{$id}）",'WikiController@cateUpdate',$user->id,$user->nick_name,$params);
            Db::connection()->commit();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            throw new \Exception($e);
        }
    }

    /**
     * 删除文档分类
     * @param $ids
     * @throws \Throwable
     */
    public static function destroy($ids)
    {
        $user = AuthLogic::getInstance()->user();
        if($user == null){
            throw new \Exception('未登入');
        }
        $category = CmsWikiCate::whereIn('id',$ids)->has('wiki')->get();
        if($category->count() > 0){
            throw new \Exception('删除的分类中有分类下含有文档');
        }
        try {
            Db::connection()->beginTransaction();
            CmsWikiCate::destroy($ids);
            UserService::addLog('CMS管理-文档管理-删除文档分类','WikiController@cateDestroy',$user->id,$user->nick_name,$ids);
            Db::connection()->commit();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            throw new \Exception($e);
        }
    }
}