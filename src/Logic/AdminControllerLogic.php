<?php
/**
 *-------------------------------------------------------------------------p*
 * 后台通用型控制器
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


namespace Phcent\WebmanAsk\Logic;

use Phcent\WebmanAsk\Service\UserService;
use support\Db;
use support\Request;

class AdminControllerLogic
{

    public  $guard = "user";
    public  $model = '';
    public  $name;
    public  $projectName;
    public  $orderBy = ['id' => 'desc'];
    public  $limit = 10;
    public  $key = 'id';

    /**
     * 获取列表
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $model = new $this->model;
            $model = phcentWhereParams($model, $request->all());
            if (request()->input('sortName') && in_array(request()->input('sortOrder'), array('asc', 'desc'))) {
                $model = $model->orderBy(request()->input('sortName'), request()->input('sortOrder'));
            } else {
                foreach ($this->orderBy as $key=>$value){
                    $model = $model->orderBy($key,$value);
                }
            }
            if($request->input('dataRecovery')){
                $model = $model->onlyTrashed();
            }
            $model = $this->beforeAdminIndex($model);
            $list = $model->paginate($request->input('limit',$this->limit));

            $data = $this->afterAdminIndex($list);
            return phcentSuccess( $data,$this->name.'列表', ['page' => $list->currentPage(), 'total' => $list->total()]);
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 新增数据
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    public function create(Request $request)
    {
        try {
            phcentMethod(['POST','GET']);
            if($request->method() == 'GET'){
                $data = $this->getAdminCreate();
                return phcentSuccess($data);
            }else{
                $user = AuthLogic::getInstance()->user();
                if($user == null){
                    throw new \Exception('未登入');
                }
                $params = $this->beforeAdminCreate($user);
                $create = $this->adminCreate($user,$params);
                $data = $this->afterAdminCreate($user,$create);
                return phcentSuccess($data,'新增'.$this->name.'成功');
            }
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 修改数据
     * @param Request $request
     * @param $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function update(Request $request,$id)
    {
        try {
            phcentMethod(['PUT','GET']);
            if($request->method() == 'GET'){
                $data = $this->getAdminUpdate($id);
                return phcentSuccess($data);
            }else{
                $user = AuthLogic::getInstance()->user();
                if($user == null){
                    throw new \Exception('未登入');
                }
                $params = $this->beforeAdminUpdate($user,$id);
                $update = $this->adminUpdate($user,$params,$id);
                $data = $this->afterAdminUpdate($user,$update);
                return phcentSuccess($data,'修改'.$this->name.'成功');
            }
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 获取详情
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function show(Request $request,$id)
    {
        try {
            phcentMethod(['GET']);
            $data = $this->getAdminShow($id);
            return phcentSuccess($data);
        } catch (\Exception $e) {
            return phcentError( $e->getMessage());
        }
    }

    /**
     * 删除数据
     * @param Request $request
     * @param $id
     * @return \support\Response
     * @throws \Throwable
     */
    public function destroy(Request $request,$id)
    {
        $ids = is_array($id) ? $id : (is_string($id) ? explode(',', $id) : func_get_args());
        try {
            $user = AuthLogic::getInstance()->user();
            if($user == null) {
                throw new \Exception('未登入');
            }
            Db::connection()->beginTransaction();
            $data = $this->adminDestroy($user,$ids,$id);
            UserService::addLog($this->projectName.'删除'.$this->name,get_class($this).'@destroy',$user->id,$user->nick_name,$ids);
            Db::connection()->commit();
            return phcentSuccess($data,'删除'.$this->name.'成功');
        } catch (\Exception $e) {
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }

    /**
     * 彻底删除与还原
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function recovery(Request $request,$id)
    {
        $ids = is_array($id) ? $id : (is_string($id) ? explode(',', $id) : func_get_args());
        try {
            phcentMethod(['DELETE','PUT']);
            $user = AuthLogic::getInstance()->user();
            if($user == null) {
                throw new \Exception('未登入');
            }
            Db::connection()->beginTransaction();
            if($request->method() == 'DELETE') {
                $this->adminRecoveryDelete($user,$ids,$id);
                UserService::addLog($this->projectName.'回收站删除'.$this->name,get_class($this).'@recovery',$user->id,$user->nick_name,$ids);
            }else{
                $this->adminRecovery($user,$ids,$id);
                UserService::addLog($this->projectName.'恢复'.$this->name,get_class($this).'@recovery',$user->id,$user->nick_name,$ids);
            }
            Db::connection()->commit();
            return phcentSuccess();
        } catch (\Exception $e) {
            Db::connection()->rollBack();
            return phcentError($e->getMessage());
        }
    }

    /**
     * 获取数据之前
     * @param $model
     * @return mixed
     */
     function beforeAdminIndex($model){
        return $model;
    }

    /**
     * 获取数据之后
     * @param $list
     * @return mixed
     */
    function afterAdminIndex($list){
        $data['list'] = $list->items();
        return $data;
    }

    /**
     * @return array
     */
    function getAdminCreate(){
        return [];
    }

    /**
     * 新增之前数据处理
     * @param $user
     * @return array|mixed
     */
    function beforeAdminCreate($user){
        return phcentParams([]);
    }

    /**
     * 新增数据
     * @param $user
     * @param $params
     * @return mixed
     * @throws \Throwable
     */
    function adminCreate($user,$params){
        try {
            Db::connection()->beginTransaction();
            $create =(new $this->model)->create($params);
            $id = $this->key;
            UserService::addLog($this->projectName.'新增'.$this->name."(编号：{$create->$id})",get_class($this).'@create',$user->id,$user->nick_name,$params);
            $this->insertAdminCreate($user,$create);
            Db::connection()->commit();
            return $create;
        }catch (\Exception $e){
            Db::connection()->rollBack();
            throw new \Exception($e);
        }
    }

    /**
     * 新增数据之后处理
     * @param $user
     * @param $create
     * @return mixed
     */
    function afterAdminCreate($user,$create){
        $data['info'] = $create;
        return $data;
    }
    function insertAdminCreate($user,$create){
        return true;
    }

    /**
     * 获取修改数据
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    function getAdminUpdate($id){
        $info =(new $this->model)->where($this->key, $id)->first();
        if ($info == null) {
            throw new \Exception('数据不存在');
        }
        $data = $this->insertGetAdminUpdate($info,$id);
        return $data;
    }

    /**
     * 编辑返回数据插入
     * @param $info
     * @param $id
     * @return mixed
     */
    function insertGetAdminUpdate($info,$id){
        $data['info'] = $info;
        return $data;
    }

    /**
     * 修改之前数据处理
     * @param $user
     * @param $id
     * @return array|mixed
     */
     function beforeAdminUpdate($user,$id){
        return phcentParams([]);
    }

    /**
     * 修改数据
     * @param $user
     * @param $params
     * @param $id
     * @return mixed
     * @throws \Throwable
     */
    function adminUpdate($user,$params,$id){
        $info = (new $this->model)->where($this->key,$id)->first();
        if($info == null){
            throw new \Exception('数据不存在');
        }
        try {
            Db::connection()->beginTransaction();
            foreach ($params as $key=>$val){
                $info->$key = $val;
            }
            $info->save();
            $this->insertAdminUpdate($user,$params,$info,$id);
            UserService::addLog($this->projectName.'修改'.$this->name."(编号：{$id})",get_class($this).'@update',$user->id,$user->nick_name,$params);
            Db::connection()->commit();
            return $info;
        }catch (\Exception $e){
            Db::connection()->rollBack();
            throw new \Exception($e);
        }
    }

    /**
     * 修改数据时插入
     * @param $user
     * @param $params
     * @param $info
     * @param $id
     */
    function insertAdminUpdate($user,$params,$info,$id){

    }

    /**
     * 修改数据之后抛出
     * @param $user
     * @param $update
     * @return mixed
     */
    function afterAdminUpdate($user,$update){
        $data['info'] = $update;
        return $data;
    }

    /**
     * 获取详情
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    function getAdminShow($id){
        $info =(new $this->model)->where($this->key, $id)->first();
        if ($info == null) {
            throw new \Exception('数据不存在');
        }
        $data['info'] = $info;
        return $data;
    }

    /**
     * 删除数据
     * @param $user
     * @param $ids
     * @param $id
     * @return array
     */
    function adminDestroy($user,$ids,$id){
        (new $this->model)->destroy($ids);
        return [];
    }

    /**
     * 数据恢复
     * @param $user
     * @param $ids
     * @param $id
     */
    function adminRecovery($user,$ids,$id){
        if(is_numeric($id) && empty($id)){
            (new $this->model)->onlyTrashed()->restore();
        }else{
            (new $this->model)->whereIn($this->key,$ids)->onlyTrashed()->restore();
        }
//        $list = (new $this->model)->whereIn($this->key,$ids)->onlyTrashed()->get();
//        foreach ($list as $item) {
//            $item->restore();
//        }
    }

    /**
     * 数据彻底删除
     * @param $user
     * @param $ids
     * @param $id
     */
    function adminRecoveryDelete($user,$ids,$id){
        if(is_numeric($id) && empty($id)){
            (new $this->model)->onlyTrashed()->forceDelete();
        }else{
            (new $this->model)->whereIn($this->key,$ids)->onlyTrashed()->forceDelete();
        }
    }


}