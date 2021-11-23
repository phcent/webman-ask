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


namespace Phcent\WebmanAsk\Controllers\User\V1;


use Phcent\WebmanAsk\Logic\AuthLogic;
use Phcent\WebmanAsk\Model\SysAlbumFiles;
use support\Request;

class UploadController
{
    /**
     * 获取图片信息
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        try {
            phcentMethod(['GET']);
            $userId = AuthLogic::getInstance()->userId();
            $album = new SysAlbumFiles();
            $params = phcentParams(['type']);
            $album = phcentWhereParams($album,$params);
            $list  = $album->where('user_id',$userId)->paginate($request->input('limit',config('phcentask.pageLimit')),'*','page',$request->input('page',1));
            $data['list'] = $list->items();
            return phcentSuccess($data,'附件列表',[ 'page' => $list->currentPage(),'total' => $list->total(),'hasMore' =>$list->hasMorePages()]);
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 上传图片
     * @param Request $request
     * @return \support\Response
     */
    public function create(Request $request)
    {
        try {
            $file = $request->file('file');
            $userId = AuthLogic::getInstance()->userId();
            if(empty($userId)){
                throw new \Exception('请先登入');
            }
            if ($file && $file->isValid()) {
               $data =  phcentUploadFile($file);
               //图片入库
                SysAlbumFiles::create([
                    'width' => $data['files_width'],
                    'height' => $data['files_height'],
                    'name' => $data['files_name'],
                    'original_name' => $data['original_name'],
                    'user_id' => $userId,
                    'size' => $data['files_size'],
                    'mine_type' => $data['mine_type'],
                    'disk' => $data['disk'],
                    'type' => $data['type']
                ]);
               return phcentSuccess($data);
            }
            throw new \Exception('上传失败');
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }

    /**
     * 删除图片
     * @param Request $request
     * @param $id
     * @return \support\Response
     */
    public function destroy(Request $request,$id)
    {
        $ids = is_array($id) ? $id : (is_string($id) ? explode(',', $id) : func_get_args());
        try {
            $userId = AuthLogic::getInstance()->userId();
            if(empty($userId)){
                throw new \Exception('请先登入');
            }
            //从库中软删除 平台做后续删除
            $files = SysAlbumFiles::where('user_id',$userId)->whereIn('id',$ids)->get();
            if($files->count() > 0){
                SysAlbumFiles::destroy($files->pluck('id'));
            }
            return phcentSuccess();
        }catch (\Exception $e){
            return phcentError($e->getMessage());
        }
    }
}