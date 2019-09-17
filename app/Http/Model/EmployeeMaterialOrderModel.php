<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/20
 * Time: 10:07
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class EmployeeMaterialOrderModel
{
    private $table = 'employee_material_order';

    /**
     * @param $id
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    public function getMaterialOrder($id, $startTime, $endTime)
    {
        return DB::table($this->table)
            ->where('employeeId', $id)
            ->where('orderTime', '>', $startTime)
            ->where('orderTime', '<', $endTime)
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        $data['orderTime'] = date('Y-m-d H:i:s');
        $data['createTime'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->insertGetId($data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function listsByEmployee(array $data)
    {
        return DB::table($this->table)
            ->where(function ($query) use ($data){
                $query->where('projectId',$data['projectId'])->where('employeeId',$data['employeeId']);
                if (!empty($data['month'])){
                    $startTime = (new \DateTime($data['month']))->format('Y-m-01 00:00:00');
                    $endTime = (new \DateTime($data['month']))->format('Y-m-t 23:59:59');
                    $query->where('orderTime','<=',$startTime)->where('orderTime','<=',$endTime);
                }else{
                    if(!empty($data['startTime'])){
                        $query->where('orderTime','<=',$data['startTime']);
                    }
                    if(!empty($data['endTime'])){
                        $query->where('orderTime','<=',$data['endTime']);
                    }
                }
            })
            ->select($this->table.'.*')
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