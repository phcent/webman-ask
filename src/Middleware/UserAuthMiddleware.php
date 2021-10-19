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


use Phcent\WebmanAsk\Logic\AuthLogic;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class UserAuthMiddleware  implements MiddlewareInterface
{

    public function process(Request $request, callable $next): Response
    {
        $userId = AuthLogic::getInstance()->userId();
        if(!empty($userId)){
            return $next($request);
        }
        return phcentError('请先登入');
    }
}