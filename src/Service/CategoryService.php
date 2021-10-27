<?php
/**
 *-------------------------------------------------------------------------p*
 * 分类数据管理
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

use Phcent\WebmanAsk\Model\AskCategory;
use support\Redis;

class CategoryService
{
    /**
     * 根据类型获取分类列表
     * @param $type
     * @return
     */
    public static function getCategoryList($type)
    {
        $siteId = request()->siteId;
        $category = Redis::get("phcentAskCategory{$siteId}");
        if($category == null){
            $category = AskCategory::where('site_id',$siteId)->get()->toJson();
            Redis::set("phcentAskCategory{$siteId}",$category);
        }
        $category = json_decode($category);
        return collect($category)->where('type',$type)->where('status',1)->values()->all();
    }

    /**
     * 清除缓存
     * @param $siteId
     */
    public static function delCache()
    {
        $siteId = request()->siteId;
        Redis::del(["phcentAskCategory{$siteId}"]);
    }
}