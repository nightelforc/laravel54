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
}