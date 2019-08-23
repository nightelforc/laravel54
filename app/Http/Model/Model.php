<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/21
 * Time: 20:06
 */

namespace App\Http\Model;


class Model
{
    /**
     * @param $startTime
     * @param $endTime
     * @param $period
     * @param $periodFormat
     * @return array
     * @throws \Exception
     */
    protected function datePeriod($startTime, $endTime, $period, $periodFormat)
    {
        $result = [];
        $start = new \DateTime($startTime);
        $end = new \DateTime(date('Y-m-d 23:59:59',strtotime($endTime)));
        $interval = \DateInterval::createFromDateString($period);
        $period = new \DatePeriod($start, $interval, $end);
        foreach ($period as $dt) {
            $result[$dt->format($periodFormat)] = '';
        }
        return $result;
    }
}