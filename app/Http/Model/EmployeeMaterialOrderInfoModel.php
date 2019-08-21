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

    public function lists(array $input)
    {
        $limit = config('yucheng.limit');
        $start = is_null($input['start']) ? 0 : $input['start'];

        if (isset($input['length']) && !is_null($input['length'])) {
            $limit = $input['length'];
        }

        return DB::table($this->table)
            ->leftJoin('employee_material_order as emo', 'emo.id', '=', $this->table . '.orderId')
            ->leftJoin('material as m', 'm.id', '=', $this->table . '.materialId')
            ->leftJoin('material_spec as spec', 'spec.id', '=', $this->table . '.specId')
            ->leftJoin('supplier as s', 's.id', '=', $this->table . '.supplierId')
            ->leftJoin('project as p', 'p.id', '=', 'emo.projectId')
            ->leftJoin('employee as e', 'e.id', '=', 'emo.employeeId')
            ->where(function ($query) use ($input) {
                $query->where('emo.projectId', $input['projectId']);
                if (isset($input['startTime']) && !is_null($input['status'])) {
                    $query->where('emo.orderTime', '>=', $input['startTime'] . " 00:00:00");
                }
                if (isset($input['endTime']) && !is_null($input['endTime'])) {
                    $query->where('emo.orderTime', '<=', $input['endTime'] . " 23:59:59");
                }
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where('m.name', 'like', '%' . $input['search'] . '%');
                }
            })
            ->offset($start)->limit($limit)
            ->select($this->table . '.*',
                'm.name as materialName', 'spec.spec', 'spec.brand', 's.name as supplierName',
                'p.name as projectName', 'e.name as employeeName',
                'emo.employeeId', 'emo.orderTime', 'emo.createTime')
            ->get()->toArray();
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function countLists(array $input)
    {
        return DB::table($this->table)
            ->where(function ($query) use ($input) {
                $query->where('emo.projectId', $input['projectId']);
                if (isset($input['startTime']) && !is_null($input['status'])) {
                    $query->where('emo.orderTime', '>=', $input['startTime'] . " 00:00:00");
                }
                if (isset($input['endTime']) && !is_null($input['endTime'])) {
                    $query->where('emo.orderTime', '<=', $input['endTime'] . " 23:59:59");
                }
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where('m.name', 'like', '%' . $input['search'] . '%');
                }
            })
            ->count();
    }
}