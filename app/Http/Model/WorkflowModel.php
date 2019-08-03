<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/22
 * Time: 9:20
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class WorkflowModel extends Model
{
    private $table = 'workflow';

    /**
     * @return mixed
     */
    public function lists(){
        return DB::table($this->table)->get()->toArray();
    }

    /**
     * @param array $data
     * @return array
     */
    public function info(array $data)
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
     * @param array $data
     * @return mixed
     */
    public function update(array $data){
        $updateData = [
            'name' => $data['name'],
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
        return DB::table($this->table)->where('id', $data['id'])->update(['status'=>$data['status']]);
    }
}