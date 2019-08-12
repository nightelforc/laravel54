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
            ->leftJoin('permission as p','p.id','=',$this->table.'.permissionId')
            ->where('roleId',$roleId)
            ->select('p.name as permissionName','p.code','p.type','p.url','p.isMenu')
            ->get()->toArray();
    }
}