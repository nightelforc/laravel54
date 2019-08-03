<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/18
 * Time: 18:01
 */

namespace App\Http\Model;

use Illuminate\Support\Facades\DB;

class YearModel
{
    private $table = 'year';

    /**
     * @return mixed
     */
    public function lists()
    {
        return DB::table($this->table)->orderBy('startTime', 'desc')->get()->toArray();
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
            'startTime' => $data['startTime'] . ' 00:00:00',
            'endTime' => $data['endTime'] . ' 23:59:59',
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
            'startTime' => $data['startTime'] . ' 00:00:00',
            'endTime' => $data['endTime'] . ' 23:59:59',
        ];
        return DB::table($this->table)->where('id', $data['id'])->update($updateData);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function delete($data)
    {
        return DB::table($this->table)->where($data)->delete();
    }

    /**
     * @param $startTime
     * @return mixed
     */
    public function checkYear($startTime)
    {
        return DB::table($this->table)->where('endTime', '>', $startTime)->count();
    }

    /**
     * @param $time
     * @return mixed
     */
    public function findYear($time){
        return DB::table($this->table)->where('endTime','>',$time)->where('startTime','<',$time)->first();
    }
}