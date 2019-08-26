<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/1
 * Time: 14:22
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class WarehouseLogInfoModel
{
    private $table = 'warehouse_log_info';

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        return DB::table($this->table)->insert($data);
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function lists(array $input)
    {
        $limit = config('yucheng.limit');
        $start = is_null($input['start']) ? 0 : $input['start'];

        if (isset($input['length']) && !is_null($input['length'])) {
            $limit = $input['length'];
        }

        return DB::table($this->table)
            ->leftJoin('warehouse_log as wl', 'wl.id', '=', $this->table . '.logId')
            ->leftJoin('material as m', 'm.id', '=', $this->table . '.materialId')
            ->leftJoin('material_spec as spec', 'spec.id', '=', $this->table . '.specId')
            ->leftJoin('supplier as s', 's.id', '=', $this->table . '.supplierId')
            ->leftJoin('project as p', 'p.id', '=', 'wl.projectId')
            ->leftJoin('project as p1', 'p1.id', '=', 'wl.sourceProjectId')
            ->leftJoin('employee as e', 'e.id', '=', 'wl.sourceEmployeeId')
            ->where(function ($query) use ($input) {
                if (isset($input['projectId']) && !empty($input['projectId'])) {
                    $query->where('wl.projectId', $input['projectId']);
                }
                if (isset($input['type']) && !is_null($input['type']) && $input['type'] != 0) {
                    $query->where('wl.type', $input['type']);
                }
                if (isset($input['status']) && !is_null($input['status'])) {
                    $query->where('wl.status', $input['status']);
                }
                if (isset($input['startTime']) && !is_null($input['startTime'])) {
                    $query->where('wl.time', '>=', $input['startTime'] . " 00:00:00");
                }
                if (isset($input['endTime']) && !is_null($input['endTime'])) {
                    $query->where('wl.time', '<=', $input['endTime'] . " 23:59:59");
                }
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where('m.name', 'like', '%' . $input['search'] . '%');
                }
            })
            ->offset($start)->limit($limit)
            ->orderBy('createTime','desc')
            ->select($this->table . '.*',
                'm.name as materialName', 'spec.spec', 'spec.brand', 's.name as supplierName',
                'p.name as projectName', 'p1.name as sourceProjectName', 'e.name as sourceEmployeeName',
                'wl.projectId', 'wl.type', 'wl.sourceEmployeeId', 'wl.sourceProjectId', 'wl.time', 'wl.createTime','wl.status', 'wl.remark', 'wl.recoveryFunds')
            ->get()->toArray();
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function countLists(array $input)
    {
        return DB::table($this->table)
            ->leftJoin('warehouse_log as wl', 'wl.id', '=', $this->table . '.logId')
            ->leftJoin('material as m', 'm.id', '=', $this->table . '.materialId')
            ->where(function ($query) use ($input) {
                if (isset($input['projectId']) && !empty($input['projectId'])) {
                    $query->where('wl.projectId', $input['projectId']);
                }
                if (isset($input['type']) && !is_null($input['type']) && $input['type'] != 0) {
                    $query->where('wl.type', $input['type']);
                }
                if (isset($input['status']) && !is_null($input['status'])) {
                    $query->where('wl.status', $input['status']);
                }
                if (isset($input['startTime']) && !is_null($input['status'])) {
                    $query->where('wl.time', '>=', $input['startTime'] . " 00:00:00");
                }
                if (isset($input['endTime']) && !is_null($input['endTime'])) {
                    $query->where('wl.time', '<=', $input['endTime'] . " 23:59:59");
                }
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where('m.name', 'like', '%' . $input['search'] . '%');
                }
            })
            ->count();
    }
}