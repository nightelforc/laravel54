<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/12
 * Time: 16:07
 */

namespace App\Http\Model;


class PermissionModel
{
    private $table = 'permission';

    /**
     * @return mixed
     */
    public function lists()
    {
        return DB::table($this->table)->get()->toArray();
    }
}