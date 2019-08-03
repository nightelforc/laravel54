<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/19
 * Time: 11:09
 */

namespace App\Http\Model;

use Illuminate\Support\Facades\DB;

class UnitModel
{
    private $table = 'unit';

    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * @return mixed
     */
    public function lists()
    {
        return DB::table($this->table)->where('status',1)->get()->toArray();
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
            'shortname' => $data['shortname'],
            'createTime' => date('Y-m-d H:i:s', time()),
        ];
        return DB::table($this->table)->insertGetId($insertData);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function update($data)
    {
        $updateData = [
            'name' => $data['name'],
            'shortname' => $data['shortname'],
        ];
        return DB::table($this->table)->where('id', $data['id'])->update($updateData);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function updateStatus($data)
    {
        return DB::table($this->table)->where('id', $data['id'])->update(['useStatus' => $data['status']]);
    }

    /**
     * @param $pk
     * @param $data
     * @param $approvalResult
     */
    public function unitApproval($pk,$data,$approvalResult){
        DB::table($this->table)->where('id',$pk)->update(['status'=>$approvalResult]);
    }
}