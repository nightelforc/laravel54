<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/25
 * Time: 11:22
 */

namespace App\Http\Model;

use Illuminate\Support\Facades\DB;

class ProjectSectionModel
{
    private $table = 'project_section';
    const TABLE = 'project_section';

    /**
     * @param array $data
     * @param bool $exports
     * @return mixed
     */
    public function lists(array $data,$exports= false)
    {
        $result = DB::table($this->table)
            ->where(function ($query) use ($data) {
                $query->where('areaId', $data['areaId']);
                if (isset($data['search']) && !is_null($data['search'])) {
                    $query->where('name', 'like', '%' . $data['search'] . '%');
                }
            })
            ->orderBy('order','desc');
        if(!$exports){
            $limit = config('yucheng.limit');
            $start = is_null($data['start']) ? 0 : $data['start'];

            if (isset($data['length']) && !is_null($data['length'])) {
                $limit = $data['length'];
            }
            $result = $result->offset($start)->limit($limit);
        }
        $result = $result->get()->toArray();
        return $result;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function countLists(array $data)
    {
        return DB::table($this->table)
            ->where(function ($query) use ($data) {
                $query->where('areaId', $data['areaId']);
                if (isset($data['search']) && !is_null($data['search'])) {
                    $query->where('name', 'like', '%' . $data['search'] . '%');
                }
            })
            ->count();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        $data['createTime'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->insert($data);
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
        if (!isset($data['order']) || empty($data['order'])){
            $data['order'] = 1;
        }
        return DB::table($this->table)->where('id', $id)->update($data);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function delete($data){
        return DB::table($this->table)->where($data)->delete();
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function selectLists(array $input)
    {
        return DB::table($this->table)
            ->where(function ($query) use ($input) {
                $query->where('areaId', $input['areaId']);
            })
            ->orderBy('order','desc')
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @return array
     */
    public function batchInfo(array $data)
    {
        $result = DB::table($this->table)->where('name','like','%层')->where('areaId',$data['areaId'])->orderBy('id', 'desc')->first();
        return empty($result) ? [] : get_object_vars($result);
    }

    /**
     * @param array $check
     * @param $id
     * @return array
     */
    public function checkRepeat(array $check, $id = 0)
    {
        $result = DB::table($this->table)->where($check)
            ->where(function ($query) use ($id){
                if ($id != 0){
                    $query->where('id','!=',$id);
                }
            })
            ->first();
        return empty($result) ? [] : get_object_vars($result);
    }

    /**
     * 对施工段面积求和
     *
     * @param $areaId
     * @return mixed
     */
    public function sumArea($areaId)
    {
        return DB::table($this->table)->where('areaId',$areaId)->sum('area');
    }

    /**
     * @param array $data
     * @param $string
     * @return mixed
     */
    public static function getValue(array $data, $string)
    {
        return DB::table(self::TABLE)->where($data)->value($string);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function getOrder(array $data)
    {
        return DB::table(self::TABLE)->where($data)->max('order');
    }
}