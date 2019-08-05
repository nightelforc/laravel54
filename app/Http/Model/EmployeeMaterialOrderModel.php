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
}