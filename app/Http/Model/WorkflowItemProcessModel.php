<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/22
 * Time: 9:23
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class WorkflowItemProcessModel extends Model
{
    private $table = 'workflow_item_process';

    /**
     * @param array $itemProcess
     * @return mixed
     */
    public function insertArray(array $itemProcess)
    {
        return DB::table($this->table)->insert($itemProcess);
    }

    /**
     * @param array $where
     * @param array $data
     * @return mixed
     */
    public function updateProcess(array $where, array $data)
    {
        return DB::table($this->table)->where($where)->update($data);
    }
}