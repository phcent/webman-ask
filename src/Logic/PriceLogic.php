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


namespace Phcent\WebmanAsk\Logic;


use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class PriceLogic implements CastsAttributes
{

    /**
     * 将取出的数据进行转换
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return int
     */
    public function get($model, $key, $value, $attributes)
    {
        return $value = (float) bcdiv($value, 100, 2);
    }

    /**
     * 转换成将要进行存储的值
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param array $value
     * @param array $attributes
     * @return string
     * @throws \Exception
     */
    public function set($model, $key, $value, $attributes)
    {
        if (is_numeric($value)) {
            $value = intval(bcmul($value, 100, 0));
        }
        return $value;
    }
}