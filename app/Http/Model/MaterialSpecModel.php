<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/31
 * Time: 21:36
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class MaterialSpecModel
{
    private $table = 'material_spec';

    /**
     * @param array $data
     * @return mixed
     */
    public function lists(array $data)
    {
        $limit = config('yucheng.limit');
        $start = 0;

        if (isset($data['limit']) && !is_null($data['limit'])) {
            $limit = $data['limit'];
        }

        if (isset($data['page']) && !is_null($data['page'])) {
            $start = ($data['page'] - 1) * $limit;
        }
        return DB::table($this->table)->where($data)->where('isDel',0)->offset($start)->limit($limit)->get()->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $data['createTime'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->insert($data);
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
     * @return mixed
     */
    public function delete(array $data)
    {
        return DB::table($this->table)->where($data)->update(['isDel'=>1]);
    }
}