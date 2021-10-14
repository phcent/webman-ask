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

namespace Phcent\WebmanAsk;

use Phcent\WebmanAsk\Logic\ConfigLogic;

class PhcentAsk
{
    /**
     * 索引条件
     * @param $model
     * @param $param
     * @param array $unset
     * @return mixed
     */
    static function whereParams($model, $param, $unset = [])
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

    /**
     * 设定默认词
     * @param $params
     * @param null $request
     * @param false $object
     * @return array|mixed
     */
    static function params($params, $request = null, $object = false)
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
            } else {
                $default = $val;
            }
            $item[$key] = $data[$key] ?? $default;
        }
        if ($object) {
            return self::getStdObject($item);
        }
        return $item;
    }

    /**
     * 转为对象
     * @param $arrayList
     * @return mixed
     */
    static function getStdObject($arrayList)
    {
        return json_decode(json_encode($arrayList));
    }

    /**
     * 状态返回
     * @param int $code
     * @param string $msg
     * @param array $datas
     * @param array $extend_data
     * @return \support\Response
     */
    static function json($code = 200, $msg = "成功", $datas = [], $extend_data = [])
    {
        $data = array();
        $data['code'] = $code;
        $data['msg'] = $msg;
        $data['message'] = $msg;
        $data['type'] = $code == ConfigLogic::STATUS_YES_CODE ? 'success' : 'error';
        $data['data'] = $datas;

        if (!empty($extend_data)) {
            $data = array_merge($data, $extend_data);
        }

        //$data['result'] = $datas;
        return json($data);
    }

    static function success($datas = [], $msg = "成功", $extend_data = [])
    {
        return self::json(config('phcentask.code.intel_yes'), $msg, $datas, $extend_data);
    }

    static function error($msg = "失败", $datas = [], $extend_data = [])
    {
        return self::json(config('phcentask.code.intel_no'), $msg, $datas, $extend_data);
    }

    /**
     * 判断请求类型的正确性
     * @param $method
     * @return void
     * @throws \Exception
     */
    static function method($method)
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

    /**
     * 验证是否手机号
     * @param $phone_number
     * @return bool
     */
    public static function isPhoneNumber($phone_number)
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

    static function isTelNumber($tel)
    {
        $isTel = "/^([0-9]{3,4}-)?[0-9]{7,8}$/";
        if (preg_match($isTel, $tel)) {
            return true;
        }
        return false;
    }

    static function isEmailText($email)
    {
        $regex = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
        $regex = "/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";
        if (preg_match($regex, $email)) {
            return true;
        }
        return false;
    }
}

