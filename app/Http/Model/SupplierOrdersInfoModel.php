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
        $result = DB::table($this->table)->where($data)->first();
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