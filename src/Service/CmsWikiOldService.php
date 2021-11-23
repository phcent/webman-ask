<?php
/**
 *-------------------------------------------------------------------------p*
 * 文档历史记录
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

class CmsWikiOldService
{
    /**
     * 删除文档历史记录
     * @param $ids
     * @throws \Throwable
     */
    public static function destroy($ids)
    {
        $user = AuthLogic::getInstance()->user();
        if($user == null){
            throw new \Exception('未登入');
        }
        try {
            Db::connection()->beginTransaction();
            CmsWikiOld::destroy($ids);
            UserService::addLog('CMS管理-文档管理-删除历史记录','WikiController@oldDestroy',$user->id,$user->nick_name,$ids);
            Db::connection()->commit();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            throw new \Exception($e);
        }
    }

    /**
     * 恢复历史记录
     * @param $id
     * @throws \Throwable
     */
    public static function recovery($id)
    {
        $user = AuthLogic::getInstance()->user();
        if($user == null){
            throw new \Exception('未登入');
        }
        try {
            Db::connection()->beginTransaction();
            $oldInfo = CmsWikiOld::where('id',$id)->first();
            if($oldInfo == null){
                throw new \Exception('历史记录异常');
            }
            $info  = CmsWiki::where($oldInfo->wiki_id)->first();
            if($info == null){
                throw new \Exception('文档异常');
            }
            //写入历史记录
            CmsWikiOld::create([
                'name' => $info->name,
                'wiki_id' => $info->id,
                'content' => $info->content,
                'user_name' => $info->user_name,
                'user_id' => $info->user_id,
                'role' => $info->role
            ]);
            //变更记录
            $info->name = $oldInfo->name;
            $info->content = $oldInfo->content;
            $info->role = $oldInfo->role;
            $info->user_id = $user->id;
            $info->user_name = $user->name;
            $info->save();
            UserService::addLog("CMS管理-文档管理-恢复文档历史记录[{$oldInfo->name}],编号：{$id}","WikiController@oldRecovery",$user->id,$user->name);
            Db::connection()->commit();
        }catch (\Exception $e){
            Db::connection()->rollBack();
            throw new \Exception($e);
        }
    }
}