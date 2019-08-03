<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/19
 * Time: 13:42
 */

namespace App\Http\Model;

use Illuminate\Support\Facades\DB;

class ProfessionModel
{
    private $table = 'profession';

    /**
     * @return mixed
     */
    public function lists()
    {
        return DB::table($this->table)->get()->toArray();
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
    public function insert(Array $data)
    {
        $insertData = [
            'name' => $data['name'],
        ];
        return DB::table($this->table)->insert($insertData);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function update($data)
    {
        $updateData = [
            'name' => $data['name'],
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