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
 * @since      象讯·PHP商城系统Pro
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class AccessControllerMiddleware implements MiddlewareInterface
{

    public function process(Request $request, callable $next): Response
    {
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
                'Access-Control-Allow-Headers' => config('phcentask.cross.headers','Content-Type,Authorization,X-Requested-With,Accept,Origin'),
            ]);
        } else {
            $response = $next($request);
        }
        return $response;
    }
}