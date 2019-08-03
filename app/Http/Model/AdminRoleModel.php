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
}