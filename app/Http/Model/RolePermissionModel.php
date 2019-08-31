<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/12
 * Time: 15:37
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class RolePermissionModel
{
    private $table = 'role_permission';

    /**
     * @param array $data
     * @return mixed
     */
    public function delete(array $data)
    {
        return DB::table($this->table)->where($data)->delete();
    }

    /**
     * @param array $input
     * @return int
     */
    public function insert(array $input)
    {
        $i = 0;
        $this->delete(['roleId'=>$input['roleId']]);
        foreach ($input['permission'] as $p){
            if (intval($p) && intval($p) > 0){
                DB::table($this->table)->insert(['roleId'=>$input['roleId'],'permissionId'=>intval($p)]);
                $i++;
            }else{
                break;
            }
        }
        return $i;
    }

    /**
     * @param $roleId
     * @return mixed
     */
    public function getPermission($roleId)
    {
        return DB::table($this->table)
            ->leftJoin('role as r','r.id','=',$this->table.'.roleId')
            ->leftJoin('permission as p','p.id','=',$this->table.'.permissionId')
            ->where('roleId',$roleId)
            ->where('r.status',1)
            ->where('p.status',1)
            ->select('p.*')
            ->get()->toArray();
    }

    /**
     * @param $roleId
     * @param $uri
     * @return bool
     */
    public function checkAuth($roleId,$uri){
        $result = DB::table($this->table)
            ->leftJoin('role as r','r.id','=',$this->table.'.roleId')
            ->leftJoin('permission as p','p.id','=',$this->table.'.permissionId')
            ->where($this->table.'.roleId',$roleId)
            ->where('p.url',$uri)
            ->where('r.status',1)
            ->get()->toArray();

        if (count($result)>0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function lists(array $input)
    {
        return DB::table($this->table)->where($input)->get()->toArray();
    }
}