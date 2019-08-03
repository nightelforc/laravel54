<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/22
 * Time: 9:22
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class WorkflowItemModel extends Model
{
    private $table = 'workflow_item';

    public function insert(array $data)
    {
        return DB::table($this->table)->insertGetId($data);
    }
}