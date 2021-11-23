<?php
/**
 *-------------------------------------------------------------------------p*
 * 文章数据处理
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

use Phcent\WebmanAsk\Model\CmsCategory;
use support\Redis;

class CmsArticleService
{

    public static function getArticleByCateIds($ids,$limit = 4)
    {
        $idsString = implode('-',$ids).'_'.$limit;
        $list = Redis::get("articleByCateIds{$idsString}");
        if($list == null){
            $list = CmsCategory::whereIn('id',$ids)->get();
            $list->each(function ($item) use ($limit) {
                $item->load(['article'=>function($query) use ($limit) {
                    $query->orderBy('id', 'desc')->limit($limit);
                }]);
            });
            $list = $list->toJson();
            Redis::set("articleByCateIds{$idsString}",$list);
        }
        return json_decode($list);
    }
}