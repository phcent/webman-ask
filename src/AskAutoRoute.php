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


namespace Phcent\WebmanAsk;


use Webman\Route;

class AskAutoRoute
{
    /**
     * 路由注册
     */
    static function load()
    {
        AskRoute::group('/api', function () {

            collect(glob(app_path().'/phcent/*/routes.php'))->map(function($filename) {
                AskRoute::group('/extend',function () use ($filename) {
                    return include_once $filename;
                });
            });


           AskRoute::group('/system/admin',  function () {
                AskRoute::resource('/ajax',Controllers\Admin\System\AjaxController::class,['cache','menu','card'])->middleware(\Phcent\WebmanAsk\Middleware\UserAuthMiddleware::class);
                AskRoute::resource('/album',Controllers\Admin\System\AlbumController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/cash',Controllers\Admin\System\CashController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/log',Controllers\Admin\System\LogController::class,['index','destroy'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/menu',Controllers\Admin\System\MenuController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/orders',Controllers\Admin\System\OrdersController::class,['index','update'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/recharge',Controllers\Admin\System\RechargeController::class,['index','destroy'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/team',Controllers\Admin\System\TeamController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/user',Controllers\Admin\System\UserController::class,['index','update','create','destroy','recovery','money','points'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
               AskRoute::resource('/balance',Controllers\Admin\System\BalanceController::class,['index'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
            });

            AskRoute::group('/ask/admin', function () {
                AskRoute::resource('/ajax',Controllers\Admin\Ask\AjaxController::class,['cache','card'])->middleware(\Phcent\WebmanAsk\Middleware\UserAuthMiddleware::class);
                AskRoute::resource('/answer',Controllers\Admin\Ask\AnswerController::class,['index','update','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/article',Controllers\Admin\Ask\ArticleController::class,['index','update','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/category',Controllers\Admin\Ask\CategoryController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/dynamic',Controllers\Admin\Ask\DynamicController::class,['index','update','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/grade',Controllers\Admin\Ask\GradeController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/message',Controllers\Admin\Ask\MessageController::class,['index','update','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/notice',Controllers\Admin\Ask\NoticeController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/orders',Controllers\Admin\Ask\OrdersController::class,['index','update'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/question',Controllers\Admin\Ask\QuestionController::class,['index','update','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/reply',Controllers\Admin\Ask\ReplyController::class,['index','update','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/report',Controllers\Admin\Ask\ReportController::class,['index','update','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/signin',Controllers\Admin\Ask\SigninController::class,['index','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/tags',Controllers\Admin\Ask\TagsController::class,['index','update','create','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/thanks',Controllers\Admin\Ask\ThanksController::class,['index','update','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
                AskRoute::resource('/user',Controllers\Admin\Ask\UserController::class,['index','update','destroy','recovery'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class);
            });

            AskRoute::group('/cms/admin', function () {
                AskRoute::resource('/article',Controllers\Admin\Cms\ArticleController::class,['index','update','create','destroy','recovery','cate'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class); //文章
                AskRoute::resource('/category',Controllers\Admin\Cms\CategoryController::class,['index','update','destroy','create'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class); //分类
                AskRoute::resource('/wiki',Controllers\Admin\Cms\WikiController::class,['index','create','update','destroy','recovery','oldRecovery','oldList','oldDestroy','copy','copyAll','cateList','cateCreate','cateUpdate','cateDestroy'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class); //文档
                AskRoute::resource('/case',Controllers\Admin\Cms\CaseController::class,['index','update','destroy','create'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class); //案例管理
                AskRoute::resource('/seller',Controllers\Admin\Cms\SellerController::class,['index','update','destroy','create'])->middleware(\Phcent\WebmanAsk\Middleware\AdminAuthMiddleware::class); //案例管理
                AskRoute::resource('/ajax',Controllers\Admin\Cms\AjaxController::class,['clear','test'])->middleware(\Phcent\WebmanAsk\Middleware\UserAuthMiddleware::class); //公用路由 只判断是否登入
            });

            //会员路由
            AskRoute::group('/{version}/user/{controller}', function () {
                AskRoute::any('/{action}[/{id}]', function ($request, $version, $controller, $action, $id = null) {
                    $class_name = 'Phcent\\WebmanAsk\\Controllers\\User\\' . Ucfirst($version) . '\\' . Ucfirst($controller) . 'Controller';
                    if (!method_exists($class_name, $action)) {
                        return phcentJson(config('phcentask.code.intel_no'), $version . "目录下的控制器: {$controller}里面的方法: {$action}不存在");
                    }
                    $controller = new $class_name;
                    $request->controller = $class_name;
                    return call_user_func([$controller, $action], $request, $id);
                });
            })->middleware(\Phcent\WebmanAsk\Middleware\UserAuthMiddleware::class);
            //前台路由
            AskRoute::group('/{version}/web/{controller}', function () {
                AskRoute::any('/{action}[/{id}]', function ($request, $version, $controller, $action, $id = null) {
                    $class_name = 'Phcent\\WebmanAsk\\Controllers\\Web\\' . Ucfirst($version) . '\\' . Ucfirst($controller) . 'Controller';
                    if (!method_exists($class_name, $action)) {
                        return phcentJson(config('phcentask.code.intel_no'), $version . "目录下的控制器: {$controller}里面的方法: {$action}不存在");
                    }
                    $controller = new $class_name;
                    $request->controller = $class_name;
                    $request->page = $request->input('page',1);
                    return call_user_func([$controller, $action], $request, $id);
                });
            });
        });

        AskRoute::options('/api/{all:.*}', function () {
            return response('不要淘气哦！路由不存在啦');
        });
        AskRoute::fallback(function () {
            return phcentJson(config('phcentask.code.intel_bad'), '不要淘气哦！链接不存在啦');
        });
    }
}