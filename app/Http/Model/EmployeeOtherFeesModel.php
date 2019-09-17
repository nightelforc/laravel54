<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/20
 * Time: 10:07
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class EmployeeOtherFeesModel
{
    private $table = 'employee_other_fees';

    /**
     * @param $id
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    public function getBonus($id, $startTime, $endTime)
    {
        return $this->getOtherFees($id, $startTime, $endTime,1);
    }

    /**
     * @param $id
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    public function getFine($id, $startTime, $endTime)
    {
        return $this->getOtherFees($id, $startTime, $endTime,2);
    }

    /**
     * @param $id
     * @param $startTime
     * @param $endTime
     * @param $type
     * @return mixed
     */
    public function getOtherFees($id, $startTime, $endTime,$type){
        return DB::table($this->table)
            ->where('employeeId',$id)
            ->where('logTime','>',$startTime)
            ->where('logTime','<',$endTime)
            ->where('type',$type)
            ->where('status',1)
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @return array
     */
    public function info($data = [])
    {
        $result = DB::table($this->table)->where($data)->first();
        return empty($result) ? [] : get_object_vars($result);
    }
}