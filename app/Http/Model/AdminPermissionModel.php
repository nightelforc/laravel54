<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/12
 * Time: 17:01
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class AdminPermissionModel
{
    private $table = 'admin_permission';

    /**
     * @param $id
     * @return mixed
     */
    public function getPermission($id)
    {
        return DB::table($this->table)
            ->leftJoin('permission as p','p.id','=',$this->table.'.permissionId')
            ->where('adminId',$id)
            ->select('p.name as permissionName','p.code','p.type','p.url','p.isMenu')
            ->get()->toArray();
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
                DB::table($this->table)->insert(['adminId'=>$input['adminId'],'permissionId'=>intval($p)]);
                $i++;
            }else{
                break;
            }
        }
        return $i;
    }

    /**
     * @param $adminId
     * @param $uri
     * @return bool
     */
    public function checkAuth($adminId,$uri){
        $result = DB::table($this->table)
            ->leftJoin('permission as p','p.id','=',$this->table.'.permissionId')
            ->where($this->table.'.adminId',$adminId)
            ->where('p.url',$uri)
            ->get()->toArray();

        if (count($result)>0){
            return true;
        }else{
            return false;
        }
    }
}