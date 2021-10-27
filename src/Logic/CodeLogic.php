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


namespace Phcent\WebmanAsk\Logic;


class CodeLogic
{
    private $strBase = "";
    private $key,$length,$codeLen,$codeNums,$codeExt;

    function __construct($length = 9,$key = 2543.5415412812){
        $this->key = $key;
        $this->length = $length;
        $this->strBase = config('phcentask.codeKey',"Flpvf70CsakVjqgeWUPXQxSyJizmNH6B1u3b8cAEKwTd54nRtZOMDhoG2YLrI");
        $this->codeLen = substr($this->strBase,0,$this->length);
        $this->codeNums = substr($this->strBase,$this->length,10);
        $this->codeExt = substr($this->strBase,$this->length + 10);
    }

    /**
     * @param $nums
     * @return string
     */
    function encode($nums){
        $rtn = "";
        $numsLen = strlen($nums);
        //密文第一位标记数字的长度
        $begin = substr($this->codeLen,$numsLen - 1,1);

        //密文的扩展位
        $extLen = $this->length - $numsLen - 1;
        $temp = str_replace('.', '', $nums / $this->key);
        $temp = substr($temp,-$extLen);

        $arrExtTemp = str_split($this->codeExt);
        $arrExt = str_split($temp);
        foreach ($arrExt as $v) {
            $rtn .= $arrExtTemp[$v];
        }

        $arrNumsTemp = str_split($this->codeNums);
        $arrNums = str_split($nums);
        foreach ($arrNums as $v) {
            $rtn .= $arrNumsTemp[$v];
        }
        return $begin.$rtn;
    }


    function decode($code){

        $begin = substr($code,0,1);
        $rtn = '';
        $len = strpos($this->codeLen,$begin);
        if($len!== false){
            $len++;
            $arrNums = str_split(substr($code,-$len));
            foreach ($arrNums as $v) {
                $rtn .= strpos($this->codeNums,$v);
            }
        }
        return $rtn;
    }
}