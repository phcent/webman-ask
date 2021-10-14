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

class AdminAuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next) : Response
    {
        $user = AuthLogic::getInstance()->user();
        if($user != null){
            if($user->current_team_id != 1){
                return phcentError('无操作权限');
            }
            return $next($request);
        }
        return phcentError('请先登入');
//        $session = $request->session();
//        if (!$session->get('userinfo')) {
//            return redirect('/user/login');
//        }

    }
}