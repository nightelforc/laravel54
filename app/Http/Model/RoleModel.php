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
            ->leftJoin('profession as p','p.id','=',$this->table.'.professionId')
            ->where(function ($query) use ($input){
                if (isset($input['isProject']) && !is_null($input['isProject'])){
                    $query->where('isProject',$input['isProject']);
                }
                if (!empty($input['search'])){
                    $query->where('name','like','%'.$input['search'].'%');
                }
            })
            ->offset($start)->limit($limit)
            ->select($this->table.'.*','p.name as professionName')
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
     * @param array $data
     * @return mixed
     */
    public function selectLists(array $data){
        return DB::table($this->table)
            ->where($data)
            ->get()->toArray();
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
     * @param array $check
     * @param $id
     * @return array
     */
    public function checkRepeat(array $check, $id = 0)
    {
        $result = DB::table($this->table)->where($check)
            ->where(function ($query) use ($id){
                if ($id != 0){
                    $query->where('id','!=',$id);
                }
            })
            ->first();
        return empty($result) ? [] : get_object_vars($result);
    }
}