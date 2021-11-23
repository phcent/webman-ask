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


namespace Phcent\WebmanAsk\Controllers\Admin\Ask;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Phcent\WebmanAsk\Model\AskAnswer;
use Phcent\WebmanAsk\Model\AskArticle;
use Phcent\WebmanAsk\Model\AskDynamic;
use Phcent\WebmanAsk\Model\AskOrders;
use Phcent\WebmanAsk\Model\AskQuestion;
use Phcent\WebmanAsk\Model\AskReport;
use Phcent\WebmanAsk\Model\AskThanks;
use Phcent\WebmanAsk\Model\AskUser;
use support\Redis;
use support\Request;

class AjaxController
{
    public function cache(Request $request)
    {
        Redis::del(['phcentAskHotQuestion','phcentAskNewQuestion','phcentAskHotArticle','phcentAskHotTags','phcentAskExpert','phcentAskCategory']);
    }

    /**
     * 统计数据
     * @param Request $request
     */
    public function card(Request $request)
    {

        try {
            phcentMethod(['GET']);
            //文章数量
            $data['articleNum'] = AskArticle::count();
            $data['articleNumToday'] = AskArticle::whereBetween('created_at',[Date::now()->startOfDay(),Date::now()->endOfDay()])->count();
            //问题数量
            $data['questionNum'] = AskQuestion::count();
            $data['questionNumToday'] = AskQuestion::whereBetween('created_at',[Date::now()->startOfDay(),Date::now()->endOfDay()])->count();
            //感谢数量
            $data['thankNum'] = AskThanks::where('status',1)->count();
            $data['thankNumToday'] = AskThanks::where('status',1)->whereBetween('created_at',[Date::now()->startOfDay(),Date::now()->endOfDay()])->count();
            //举报数量
            $data['reportNum'] = AskReport::where('status',1)->count(); //待处理
            $data['reportAllNum'] = AskReport::where('status','<>',1)->count(); //已处理
            //会员动态
            $data['dynamicNum'] = AskDynamic::count();
            $data['dynamicNumToday'] = AskDynamic::whereBetween('created_at',[Date::now()->startOfDay(),Date::now()->endOfDay()])->count();

            //待审核专家
            $data['expertNum'] = AskUser::where('expert_status',10)->count();
            $data['answerNum'] = AskAnswer::count();
            $data['questionUnNum'] = AskQuestion::where('status','<>',2)->count();

            $data['ordersNum'] = AskOrders::count();
            $data['ordersNnNum'] = AskOrders::where('status',0)->count();
            $date = $request->input('date');
            if($date && is_array($date)){
                $date = collect($request->input('date'))->sort();
            }else{
                $date = collect([Date::now()->startOfDay(),Date::now()->endOfDay()]);
            }
            $num = collect();
            $price = collect();
            if(Date::parse($date->get(1))->diffInDays(Date::parse($date->get(0))) < 1){
                $day = ['00','01','02','03','04','05','06','07', '08' , '09' ,'10' ,'11','12','13','14','15','16','17','18','19','20','21','22','23'];
            }else{
                $day = $this->generateDateRange(Date::parse($date->get(0)),Date::parse($date->get(1)));
            }
            $orders = AskOrders::whereBetween('created_at',[Date::parse($date->get(0)),Date::parse($date->get(1))])->get();
            foreach ($day as $d){
                if(Date::parse($date->get(1))->diffInDays(Date::parse($date->get(0))) < 1) {
                    $num->push($orders->whereBetween('created_at', [Date::parse($date->get(0))->startOfDay()->addHours($d)->startOfHour(), Date::parse($date->get(0))->startOfDay()->addHours($d)->endOfHour()])->count());
                    $price->push($orders->whereBetween('created_at', [Date::parse($date->get(0))->startOfDay()->addHours($d)->startOfHour(), Date::parse($date->get(0))->startOfDay()->addHours($d)->endOfHour()])->sum('amount'));
                }else{
                    $num->push($orders->whereBetween('created_at', [Date::parse($d)->startOfDay(), Date::parse($d)->endOfDay()])->count());
                    $price->push($orders->whereBetween('created_at', [Date::parse($d)->startOfDay(), Date::parse($d)->endOfDay()])->sum('amount'));
                }
            }
            $data['chart'] = [
                'day' => $day,
                'orderNum' => $num,
                'orderPrice' => $price
            ];
            return phcentSuccess($data);
        }catch (\Exception $e){
            return  phcentError($e->getMessage());
        }

    }
    /**
     * 获取时间阶段数组
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @return array
     */
    private function generateDateRange($start_date, $end_date){
        $dates = [];
        for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }
        return $dates;
    }
}