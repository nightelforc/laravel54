<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/30
 * Time: 14:26
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class AdminRoleModel
{
    private $table = 'admin_role';

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data){
        return DB::table($this->table)->insert($data);
    }

    /**
     * @param $pk
     * @param $data
     * @return mixed
     */
    public function update($pk,$data){
        return DB::table($this->table)->where('adminId',$pk)->update($data);
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
     * @return mixed
     */
    public function getAdminRole($id)
    {
        $result = DB::table($this->table)
            ->leftJoin('role as r','r.id','=',$this->table.'.roleId')
            ->leftJoin('profession as p','p.id','=','r.professionId')
            ->where('r.status',1)
            ->where('adminId',$id)
            ->select('r.id as roleId','r.name as roleName','r.professionId','p.name as professionName')
            ->first();
        return empty($result) ? [] : get_object_vars($result);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function delete(array $data)
    {
        return DB::table($this->table)->where($data)->delete();
    }
}