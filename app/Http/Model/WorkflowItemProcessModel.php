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

    public function insertArray(array $itemProcess)
    {
        return DB::table($this->table)->insert($itemProcess);
    }
}