<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/12
 * Time: 16:07
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class PermissionModel
{
    private $table = 'permission';

    /**
     * @param array $data
     * @return mixed
     */
    public function lists(array $data)
    {
        return DB::table($this->table)
            ->where(function ($query) use ($data){
                if (!empty($data['type'])){
                    $query->where('type',$data['type']);
                }
            })
            ->get()->toArray();
    }
}