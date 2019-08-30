<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/12
 * Time: 15:04
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class RoleModel
{
    private $table = 'role';

    /**
     * @param array $input
     * @return mixed
     */
    public function lists(array $input){
        $limit = config('yucheng.limit');
        $start = is_null($input['start']) ? 0 : $input['start'];

        if (isset($input['length']) && !is_null($input['length'])) {
            $limit = $input['length'];
        }

        return DB::table($this->table)
            ->offset($start)->limit($limit)
            ->select($this->table.'.*')
            ->get()->toArray();
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function countLists(array $input)
    {
        return DB::table($this->table)->count();
    }

    /**
     * @return mixed
     */
    public function selectLists(){
        return DB::table($this->table)->get()->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        return DB::table($this->table)->insertGetId($data);
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
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data)
    {
        return DB::table($this->table)->where('id',$id)->update($data);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function delete(array $data)
    {
        try{
            DB::transaction(function () use ($data) {
                DB::table($this->table)->where($data)->delete();
                (new RolePermissionModel())->delete(['roleId'=>$data['id']]);
            });
            return true;
        }catch(\Exception $e){
            return false;
        }
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function checkRepeat(array $data)
    {
        return DB::table($this->table)->where('name',$data['name'])->where('type',$data['type'])->count();
    }
}