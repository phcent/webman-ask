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

use Webman\Route;

if (! function_exists('phcentWhereParams')) {
    /**
     * 索引条件
     * @param $model
     * @param $param
     * @param array $unset
     * @return mixed
     */
    function phcentWhereParams($model, $param, $unset = [])
    {
        unset($param['page']);
        unset($param['limit']);
        unset($param['sortName']);
        unset($param['_t']);
        unset($param['sortOrder']);
        unset($param['resultType']);
        unset($param['dataRecovery']);
        unset($param['client_type']); //优先排除
        foreach ($unset as $val) {
            unset($param[$val]);
        }
        $with_has = '';
        foreach ($param as $value => $key) {

            if (strchr($value, '_like') != null && $key != null) {
                $model = $model->where(strchr($value, '_like', true), 'like', '%' . $key . '%');
            } else if (strchr($value, '_betweenTime') != null && $key != null) {
                if (preg_match('/^\d{4}-\d{2}-\d{2} ~ \d{4}-\d{2}-\d{2}$/', $key)) {
                    $date = explode('~', $key);
                    $timeMin = $date[0];
                    $timeMax = $date[1];
                    $model = $model->whereBetween(strchr($value, '_betweenTime', true), [$timeMin, $timeMax]);
                }
            } else if (strchr($value, '_array') != null && $key != null) {
                if (is_array($key)) {
                    $model = $model->whereBetween(strchr($value, '_array', true), [$key[0], $key[1]]);
                }
            } else if (strchr($value, '_not') != null && $key != null) {
                $model = $model->where(explode('_not', $value)[0], '<>', $key);
            } else if (strchr($value, '_has') != null && $key != null) {
                $relation = explode('_has', $value)[0];
                if (!empty($param[$value])) {
                    $with_has = $param[$value];
                    $model = $model->whereHas($relation, function ($q) use (&$param, $value) {
                        if (!empty($param[$param[$value]])) {
                            $q->where($param[$value], $param[$param[$value]]);
                        }
                    });

                }
            } else if ($key != null && $with_has != $value) {
                $model = $model->where($value, $key);
            }
        }
        return $model;
    }
}
if (! function_exists('phcentParams')) {
    /**
     * 设定默认词
     * @param $params
     * @param null $request
     * @param false $object
     * @return array|mixed
     */
    function phcentParams($params, $request = null, $object = false)
    {
        if ($request === null) $request = request();
        $data = $request->all();
        $item = [];
        foreach ($params as $key => $val) {
            if (is_int($key)) {
                $default = null;
                $key = $val;
                if (!isset($data[$key])) {
                    continue;
                }
                //当传过来的值为非数字型 且为空时直接过滤掉
                if(!is_numeric($data[$key]) && empty($data[$key])){
                    continue;
                }
            } else {
                $default = $val;
            }

            $item[$key] = $data[$key] ?? $default;
        }
        if ($object) {
            return phcentGetStdObject($item);
        }
        return $item;
    }
}

if (! function_exists('phcentGetStdObject')) {
    /**
     * 转为对象
     * @param $arrayList
     * @return mixed
     */
    function phcentGetStdObject($arrayList)
    {
        return json_decode(json_encode($arrayList));
    }
}
if (! function_exists('phcentJson')) {
    /**
     * 状态返回
     * @param int $code
     * @param string $msg
     * @param array $datas
     * @param array $extend_data
     * @return \support\Response
     */
    function phcentJson($code = 200, $msg = "成功", $datas = [], $extend_data = [])
    {
        $data = array();
        $data['code'] = $code;
        $data['msg'] = $msg;
        $data['message'] = $msg;
        $data['type'] = $code == config('phcentask.code.intel_yes') ? 'success' : 'error';
        $data['data'] = $datas;

        if (!empty($extend_data)) {
            $data = array_merge($data, $extend_data);
        }

        //$data['result'] = $datas;
        return json($data);
    }
}
if (! function_exists('phcentSuccess')) {
    function phcentSuccess($datas = [], $msg = "成功", $extend_data = [])
    {
        return phcentJson(config('phcentask.code.intel_yes'), $msg, $datas, $extend_data);
    }
}
if (! function_exists('phcentError')) {
    function phcentError($msg = "失败", $datas = [], $extend_data = [])
    {
        return phcentJson(config('phcentask.code.intel_no'), $msg, $datas, $extend_data);
    }
}
if (! function_exists('phcentMethod')) {
    /**
     * 判断请求类型的正确性
     * @param $method
     * @return void
     * @throws \Exception
     */
    function phcentMethod($method)
    {

        if (is_array($method)) {
            if (!in_array(request()->method(), $method)) {
                throw new \Exception('请求方式不正确');
            }
        } else {
            if (request()->method() != $method) {
                throw new \Exception('请求方式不正确');
            }
        }
    }
}
if (! function_exists('phcentIsPhoneNumber')) {
    /**
     * 验证是否手机号
     * @param $phone_number
     * @return bool
     */
    function phcentIsPhoneNumber($phone_number)
    {
        //中国联通号码：130、131、132、145（无线上网卡）、155、156、185（iPhone5上市后开放）、186、176（4G号段）、175（2015年9月10日正式启用，暂只对北京、上海和广东投放办理）,166,146
        //中国移动号码：134、135、136、137、138、139、147（无线上网卡）、148、150、151、152、157、158、159、178、182、183、184、187、188、198
        //中国电信号码：133、153、180、181、189、177、173、149、199
        $g = "/^1[34578]\d{9}$/";
        $g1 = "/^19[89]\d{8}$/";
        $g2 = "/^166\d{8}$/";
        if (preg_match($g, $phone_number)) {
            return true;
        } else if (preg_match($g1, $phone_number)) {
            return true;
        } else if (preg_match($g2, $phone_number)) {
            return true;
        }
        return false;
    }
}
if (! function_exists('phcentIsTelNumber')) {
    function phcentIsTelNumber($tel)
    {
        $isTel = "/^([0-9]{3,4}-)?[0-9]{7,8}$/";
        if (preg_match($isTel, $tel)) {
            return true;
        }
        return false;
    }
}
if (! function_exists('phcentIsEmailText')) {
    function phcentIsEmailText($email)
    {
        $regex = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
        $regex = "/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";
        if (preg_match($regex, $email)) {
            return true;
        }
        return false;
    }
}
if(! function_exists('phcentIsUserOnline')){
    function phcentIsUserOnline($uid){
        return 1;
    }
}

if(! function_exists('phcentAddRouter')){
    function phcentAddRouter($prefix,$controller,$actions){
        return Route::group($prefix,function () use ($controller, $actions) {
            foreach ($actions as $k) {
                Route::any("/{$k}[/{id}]",[$controller,$k]);
            }
        });
    }
}

if(! function_exists('phcentFileUrl')){
    function phcentFileUrl($url){
        return preg_match('/^http(s)?:\\/\\/.+/',$url) ? $url: config('phcentask.fileUrl','http://www.phcent.com');
    }
}


