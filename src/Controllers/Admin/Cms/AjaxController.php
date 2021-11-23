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
 * @since      象讯·PHP知识付费问答系统
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Controllers\Admin\Cms;


use support\Redis;
use support\Request;
use Webman\App;

class AjaxController
{
    public function clear()
    {
        //清除调用文章资源
        $keys  = Redis::keys('articleByCateIds'.'*');
        Redis::del($keys);
    }

    public function test(Request $request,$id)
    {
        $callback = \app\phcent\cms\controller\admin\ArticleController::class;
        $data = [
            'controller' => $request->controller,
            'id' => $request->id,
            'action' => $request->action
        ];
        return phcentSuccess($data);
    }
}