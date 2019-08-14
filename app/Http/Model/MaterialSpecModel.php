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
        $start = is_null($data['start']) ? 0 : $data['start'];

        if (isset($data['limit']) && !is_null($data['limit'])) {
            $limit = $data['limit'];
        }

        return DB::table($this->table)
            ->where(function ($query) use($data){
                $query->where('materialId',$data['materialId'])->where('isDel',0);
            })
            ->offset($start)->limit($limit)->get()->toArray();
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
    public function selectLists(array $data)
    {
        return DB::table($this->table)
            ->where(function ($query) use($data){
                $query->where('materialId',$data['materialId'])->where('isDel',0);
            })
            ->get()->toArray();
    }
}