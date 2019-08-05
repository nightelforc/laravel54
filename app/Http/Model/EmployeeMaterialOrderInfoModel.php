<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/5
 * Time: 10:43
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class EmployeeMaterialOrderInfoModel
{
    private $table = 'employee_material_order_info';
    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        return DB::table($this->table)->insert($data);
    }
}