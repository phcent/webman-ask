<?php
/**
 *-------------------------------------------------------------------------p*
 * 文档数据处理
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
use Phcent\WebmanAsk\Model\CmsWiki;
use Phcent\WebmanAsk\Model\CmsWikiOld;
use Phcent\WebmanAsk\Service\UserService;
use support\Db;

class CmsWikiService
{


    /**
     * 删除文档
     * @param $ids
     * @param $user
     * @param $model
     * @param $log
     * @param $id
     * @return array
     * @throws \Throwable
     */
    static function adminDestroy($ids,$user,$model,$log,$id)
    {
        $wikiList = $model->whereIn('id',$ids)->with('child')->get();
        self::delWikiChild($wikiList);
        UserService::addLog("CMS管理-文档管理-删除文档",'WikiController@destroy',$user->id,$user->nick_name,$ids);
        return [];
    }

    /**
     * 新增数据之前 可用于获取数据
     * @param $user
     * @param $model
     * @return array|mixed
     */
    static function beforeAdminCreate($user,$model){
        return phcentParams(['name',
            'pid',
            'content',
            'cate_id',
            'role']);
    }
    /**
     * 初始化新增获取数据
     * @return array
     */
    static function getAdminCreate(){
        return [];
    }
    /**
     * 初始化修改获取数据
     * @param $model
     * @param $key
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    static function getAdminUpdate($model,$key,$id){
        $info = $model->where($key, $id)->first();
        if ($info == null) {
            throw new \Exception('数据不存在');
        }
        $data['info'] = $info;
        $data['categoryList'] = CmsWikiCate::get();
        return $data;
    }
    /**
     * 编辑数据之前 可用于获取数据
     * @param $model
     * @param $key
     * @param $id
     * @return array|mixed
     */
    static function beforeAdminUpdate($user,$model,$key,$id){
        return phcentParams(['name',
            'pid',
            'content',
            'cate_id',
            'role']);
    }

    /**
     * 循环删除
     * @param $data
     */
    public static function delWikiChild($data)
    {
        foreach ($data as $item){
            CmsWiki::destroy($item->id);
            if($item->child->count() > 0){
                self::delWikiChild($item->child);
            }
        }
    }

    /**
     * 复制文档
     * @param $id
     * @throws \Exception
     */
    public static function copyWiki($id)
    {
        $user = AuthLogic::getInstance()->user();
        if($user == null){
            throw new \Exception('未登入');
        }
        $wikiInfo = CmsWiki::where('id',$id)->first();
        if($wikiInfo == null){
            throw new \Exception('文档不存在');
        }
        try {
            $wiki = CmsWiki::create([
                'name' => $wikiInfo->name.' 副本',
                'pid' => $wikiInfo->pid,
                'content' => $wikiInfo->content,
                'user_name' => $user->nick_name,
                'user_id' => $user->id,
                'cate_id' => $wikiInfo->cate_id,
                'role' => $wikiInfo->role,
            ]);
            CmsWikiOld::create([
                'name' => $wiki->name,
                'wiki_id' => $wiki->id,
                'content' => $wiki->content,
                'user_name' => $user->nick_name,
                'user_id' => $user->id,
                'role' => $wiki->role,
            ]);
            UserService::addLog("CMS管理-复制文档[{$wikiInfo->name}],编号：{$wikiInfo->id}","WikiController@copy",$user->id,$user->name);
        }catch (\Exception $E){
            throw new \Exception($E->getMessage());
        }
    }

    /**
     * 复制文档树
     * @param $id
     * @throws \Exception
     */
    public static function copyAllWiki($id)
    {
        $user = AuthLogic::getInstance()->user();
        if($user == null){
            throw new \Exception('未登入');
        }
        $wikiInfo = CmsWiki::where('id',$id)->with('child')->first();
        if($wikiInfo == null){
            throw new \Exception('文档不存在');
        }
        try {
            $info = CmsWiki::create([
                'name' => $wikiInfo->name.' 副本',
                'pid' => $wikiInfo->pid,
                'content' => $wikiInfo->content,
                'user_name' => $user->nick_name,
                'user_id' => $user->id,
                'cate_id' => $wikiInfo->cate_id,
                'role' => $wikiInfo->role,
            ]);
            CmsWikiOld::create([
                'name' => $info->name,
                'wiki_id' => $info->id,
                'content' => $info->content,
                'user_name' => $user->nick_name,
                'user_id' => $user->id,
                'role' => $info->role,
            ]);
            if($wikiInfo->child != null){
                self::copyAllData($info->id,$user,$wikiInfo->child);
            }
            UserService::addLog("复制文档树[{$wikiInfo->name}],编号：{$wikiInfo->id}","WikiController@copyAll",$user->id,$user->name);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 循环复制文档树
     * @param $id
     * @param $user
     * @param $data
     */
    public static function copyAllData($id,$user,$data)
    {
        foreach ($data as $v){
            $info = CmsWiki::create([
                'name' => $v->name,
                'pid' => $id,
                'content' => $v->content,
                'user_name' => $user->nick_name,
                'user_id' => $user->id,
                'cate_id' => $v->cate_id,
                'role' => $v->role,
            ]);
            CmsWikiOld::create([
                'name' => $info->name,
                'wiki_id' => $info->id,
                'content' => $info->content,
                'user_name' => $user->nick_name,
                'user_id' => $user->id,
                'role' => $info->role,
            ]);
            if($v->child != null){
                self::copyAllData($info->id,$user,$v->child);
            }
        }
    }

    /**
     * 彻底删除文档
     * @param $ids
     * @throws \Exception
     */
    public static function deleteWiki($ids)
    {
        try {
            $cmsWiki = CmsWiki::whereIn('id',$ids)->onlyTrashed()->get();
            CmsWiki::whereIn('id',$cmsWiki->pluk('id'))->forceDelete();
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 回收站还原
     * @param $ids
     * @throws \Exception
     */
    public static function recoveryWiki($ids)
    {
        try {
            $cmsWiki = CmsWiki::whereIn('id',$ids)->onlyTrashed()->get();
            CmsWiki::whereIn('id',$cmsWiki->pluk('id'))->restore();
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public static function deleteWikiAll()
    {
        try {
            $cmsWiki = CmsWiki::onlyTrashed()->get();
            CmsWiki::whereIn('id',$cmsWiki->pluk('id'))->forceDelete();
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
}