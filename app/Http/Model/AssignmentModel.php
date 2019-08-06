<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/19
 * Time: 13:56
 */

namespace App\Http\Model;

use Illuminate\Support\Facades\DB;

class AssignmentModel
{
    private $table = 'assignment';

    /**
     * @param array $data
     * @return mixed
     */
    public function lists($data=[])
    {
        return DB::table($this->table)
            ->leftJoin('unit as u','u.id','=',$this->table.'.unitId')
            ->where($data)
            ->select($this->table.'.*','u.name as unitName')
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @return array
     */
    public function info($data = [])
    {
        $result = DB::table($this->table)->where($data)->first();
        return empty($result) ? [] : get_object_vars($result);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        return DB::table($this->table)->insert($data);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function update($data)
    {
        $updateData = [
            'name' => $data['name'],
            'unitId' => $data['unitId'],
            'remark' => $data['remark'],
        ];
        return DB::table($this->table)->where('id', $data['id'])->update($updateData);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function updateStatus($data)
    {
        return DB::table($this->table)->where('id', $data['id'])->update(['status' => $data['status']]);
    }
}