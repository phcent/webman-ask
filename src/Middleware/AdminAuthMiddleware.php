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
 * @since      象讯·PHP 知识付费问答系统
 *-------------------------------------------------------------------------t*
 */


namespace Phcent\WebmanAsk\Middleware;


use Illuminate\Support\Str;
use Phcent\WebmanAsk\Logic\AuthLogic;

use Phcent\WebmanAsk\Service\AdminService;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class AdminAuthMiddleware implements MiddlewareInterface
{
    /**
     * 由于无法获取控制器信息 因此暂时不走中间件鉴权
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function process(Request $request, callable $next) : Response
    {
        $header = $request->header('Authorization', '');
        if (Str::startsWith($header, 'Bearer ')) {
            $userData = AuthLogic::getInstance()->getUserData();
            if($userData != null){
                $haveRole = AdminService::checkHaveRole($userData);
                if($haveRole){
                    return $next($request);
                }else{
                    return phcentJson(config('phcentask.code.intel_authority'),'无权限访问');
                }
            }else{
                return phcentJson(config('phcentask.code.intel_no_login'),'令牌已失效');
            }
        }
        return phcentError('请先登入');
    }
}