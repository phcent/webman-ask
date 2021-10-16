<?php
/**
 *-------------------------------------------------------------------------p*
 * 动态数据管理
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


use Phcent\WebmanAsk\Model\AskDynamic;


class DynamicService
{

    /**
     * 新建动态
     * @param $params
     * @param $userId
     */
    public static function create($params)
    {
        AskDynamic::create([
            'user_id' => $params['user_id'],
            'type' => $params['type'],
            'item_id' => $params['item_id'],
            'operation_stage' => $params['operation_stage'],
            'title' => $params['title'],
            'content' => $params['content']
        ]);
    }
}