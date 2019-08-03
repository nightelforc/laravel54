<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/27
 * Time: 10:28
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class SupplierModel
{
    private $table = 'supplier';

    /**
     * @param array $data
     * @return mixed
     */
    public function lists(array $data)
    {
        return DB::table($this->table)
            ->where(function($query) use ($data){
                $query->where('name', 'like', '%' . $data['search'] . '%');
            })
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $data['createTime'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->insertGetId($data);
    }

    /**
     * @param array $data
     * @return array
     */
    public function info(array $data)
    {
        $result = DB::table($this->table)->where($data)->first();
        return empty($result) ? [] : get_object_vars($result);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function update(array $data)
    {
        $id = $data['id'];
        unset($data['id']);
        return DB::table($this->table)->where('id',$id)->update($data);
    }
}