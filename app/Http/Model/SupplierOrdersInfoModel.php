<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/29
 * Time: 23:24
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class SupplierOrdersInfoModel
{
    private $table = 'supplier_orders_info';

    /**
     * @param array $data
     * @return array
     */
    public function infoLists(array $data)
    {
        $result = DB::table($this->table)
            ->leftJoin('supplier_orders as so', 'so.id', '=', $this->table . '.orderId')
            ->leftJoin('material as m', 'm.id', '=', $this->table . '.materialId')
            ->leftJoin('material_spec as ms', 'ms.id', '=', $this->table . '.specId')
            ->where(function ($query) use ($data) {
                $query->where($this->table . '.orderId', $data['orderId']);
                if (isset($data['projectId']) && !empty($data['projectId'])) {
                    $query->where('so.projectId', $data['projectId']);
                }
            })
            ->select($this->table . '.*','m.name as materialName','ms.spec','ms.brand')
            ->first();
        return empty($result) ? [] : get_object_vars($result);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function insert($data)
    {
        return DB::table($this->table)->insert($data);
    }
}