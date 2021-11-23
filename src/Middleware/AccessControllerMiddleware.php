<?php
/**
 *-------------------------------------------------------------------------p*
 * 全局中间件
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


namespace Phcent\WebmanAsk\Middleware;

use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Logic\CodeLogic;
use Phcent\WebmanAsk\Service\AdminService;
use support\Redis;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class AccessControllerMiddleware implements MiddlewareInterface
{

    public function process(Request $request, callable $next): Response
    {
        $host = $request->host(true);
        $userId = AuthLogic::getInstance()->userId();
        if(!empty($userId)){
            Redis::setEx("user-is-online-{$userId}",1800,$userId);
        }
        // 允许uri以 /api 开头的地址跨域访问
        if (strpos($request->path(), '/api') === 0) {
            // 如果是options请求，不处理业务
            if ($request->method() == 'OPTIONS') {
                $response = response('');
            } else {
                $response = $next($request);
            }
            $response->withHeaders([
                'Access-Control-Allow-Origin' => config('phcentask.cross.origin','*'),
                'Access-Control-Allow-Methods' => config('phcentask.cross.methods','GET,POST,PUT,DELETE,OPTIONS'),
                'Access-Control-Allow-Headers' => config('phcentask.cross.headers','SiteId,Content-Type,Authorization,X-Requested-With,Accept,Origin'),
            ]);

        } else {
            $response = $next($request);
        }
        return $response;
    }
}