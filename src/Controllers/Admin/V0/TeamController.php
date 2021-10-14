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


namespace Phcent\WebmanAsk\Controllers\Admin\V0;


use Phcent\WebmanAsk\Model\UserTeam;
use Phcent\WebmanAsk\Model\UserTeamMenu;

use Respect\Validation\Validator;
use support\Db;
use support\Request;

class TeamController
{
    /**
     * 菜单管理
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $userTeam = new UserTeam();
            $userTeam = phcentWhereParams($userTeam, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $userTeam = $userTeam->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                $userTeam = $userTeam->orderBy('id', 'desc')->orderBy('id', 'desc');
            }
            $list = $userTeam->paginate($request->limit ?? 10);
            $data['list'] = $list->items();
            return phcentSuccess( $data,'成功', ['page' => $list->currentPage(), 'total' => $list->total()]);
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 新增
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function create(Request $request)
    {
        try {
            phcentMethod(['GET','POST']);
            if($request->method() == 'GET'){
                $data['menuList'] = UserTeamMenu::get();
                return phcentSuccess( $data);
            }else{
                Validator::input($request->post(), [
                    'name' => Validator::length(1, 32)->noWhitespace()->setName('权限组名称'),
                ]);
                $params = phcentParams([
                    'name',
                    'role',
                ]);
                Db::connection()->beginTransaction();
                UserTeamMenu::create($params);
                Db::connection()->commit();
                return phcentSuccess();

            }
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 修改菜单
     * @param Request $request
     * @param $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function update(Request $request,$id)
    {
        try {
            phcentMethod(['GET','PUT']);
            if($request->method() == 'GET'){
                $data['menuList'] = UserTeamMenu::get();
                $info = UserTeam::where('id', $id)->first();
                if ($info == null) {
                    throw new \Exception('菜单不存在');
                }
                $data['info'] = $info;
                return phcentSuccess( $data);
            }else{
                Validator::input($request->post(), [
                    'name' => Validator::length(1, 32)->noWhitespace()->setName('权限组名称'),
                ]);
                $params = phcentParams([
                    'name',
                    'role',
                ]);
                $info = UserTeam::where('id', $id)->first();
                if ($info == null) {
                    throw new \Exception('菜单不存在');
                }
                Db::connection()->beginTransaction();
                foreach ($params as $k=>$v){
                    $info->$k = $v;
                }
                $info->save();
                Db::connection()->commit();
                return phcentSuccess();

            }
        }catch (\Exception $e){
            Db::connection()->rollBack();
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 删除菜单
     * @param $id
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function destroy(Request $request,$id)
    {
        $ids = is_array($id) ? $id : (is_string($id) ? explode(',', $id) : func_get_args());
        try {
            Db::connection()->beginTransaction();
            UserTeam::destroy($ids);
            Db::connection()->commit();
            return phcentSuccess('删除成功');
        } catch (\Exception $e) {
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }
}