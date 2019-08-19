<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/20
 * Time: 10:02
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class EmployeeAttendanceModel
{
    private $table = 'employee_attendance';

    /**
     * @param $id
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    public function getAttendances($id, $startTime, $endTime)
    {
        return DB::table($this->table)
            ->where('employeeId',$id)
            ->where('day','>',$startTime)
            ->where('day','<',$endTime)
            ->get()->toArray();
    }

    /**
     * @param $id
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    public function getAttendancesSum($id, $startTime, $endTime)
    {
        return DB::table($this->table)
            ->where('employeeId',$id)
            ->where('day','>',$startTime)
            ->where('day','<',$endTime)
            ->select(DB::raw('SUM(length) as attendance'))
            ->first();
    }

    /**
     * @param $employeeId
     * @param $projectId
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    public function getLists($employeeId, $projectId, $startTime, $endTime)
    {
        return DB::table($this->table)
            ->where('employeeId',$employeeId)
            ->where('projectId',$projectId)
            ->whereBetween('day',[$startTime,$endTime])
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        if (count($data) == count($data,1)){
            $data['createTime'] = date('Y-m-d H:i:s');
            return DB::table($this->table)->insert($data);
        }else{
            foreach ($data['data'] as $key => $d){
                $data['data'][$key]['createTime'] = date('Y-m-d H:i:s');
            }
            return DB::table($this->table)->insert($data['data']);
        }
    }

}