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
    const MENU = 1;
    const BUTTON = 2;
    const RESOURCE = 3;
    /**
     * @param array $data
     * @return mixed
     */
    public function lists(array $data)
    {
        return DB::table($this->table)
            ->where(function ($query) use ($data){
                $type = [self::MENU, self::BUTTON];
                $query->where('status',1)->whereIn('type', $type);
                if (!empty($data['isProject'])){
                    $query->where('isProject',$data['isProject']);
                }
            })
            ->get()->toArray();
    }
}