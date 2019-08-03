<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/24
 * Time: 13:52
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class ProjectModel
{
    private $table = 'project';

    /**
     * @return mixed
     */
    public function lists()
    {
        return DB::table($this->table)->get()->toArray();
    }

    public function insert(array $data)
    {
        $data['createTime'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->insertGetId($data);
    }

    public function info(array $data)
    {
        $result = DB::table($this->table)->where($data)->first();
        return empty($result) ? [] : get_object_vars($result);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function update(array $data)
    {
        $updateData = [
            'name'=>$data['name'],
            'city'=>$data['city'],
            'projectAmount'=>$data['projectAmount'],
            'projectAccount'=>$data['projectAccount'],
        ];
        return DB::table($this->table)->where('id',$data['id'])->update($updateData);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function updateStatus($data)
    {
        return DB::table($this->table)->where('id', $data['id'])->update(['status' => 2]);
    }
}