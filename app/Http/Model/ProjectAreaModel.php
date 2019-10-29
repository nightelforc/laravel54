<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/25
 * Time: 11:21
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class ProjectAreaModel
{
    private $table = 'project_area';
    const TABLE = 'project_area';

    /**
     * @param array $input
     * @return mixed
     */
    public function lists(array $input)
    {
        $limit = config('yucheng.limit');
        $start = is_null($input['start']) ? 0 : $input['start'];

        if (isset($input['length']) && !is_null($input['length'])) {
            $limit = $input['length'];
        }

        return DB::table($this->table)
            ->leftJoin('project as p','p.id','=',$this->table.'.projectId')
            ->where(function ($query) use ($input) {
                $query->where('projectId', $input['projectId']);
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where($this->table.'.name', 'like', '%' . $input['search'] . '%');
                }
            })
            ->orderBy('order','desc')
            ->select($this->table.'.*','p.name as projectName')
            ->offset($start)->limit($limit)->get()->toArray();
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function countLists(array $input)
    {
        return DB::table($this->table)
            ->leftJoin('project as p','p.id','=',$this->table.'.projectId')
            ->where(function ($query) use ($input) {
                $query->where('projectId', $input['projectId']);
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where($this->table.'.name', 'like', '%' . $input['search'] . '%');
                }
            })
            ->select($this->table.'.*','p.name as projectName')
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
     * @param array $data
     * @return mixed
     */
    public function delete(array $data)
    {
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
                $query->where('projectId', $input['projectId']);
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
        $result = DB::table($this->table)->where('name','like','%号楼')->where('projectId',$data['projectId'])->orderBy('id', 'desc')->first();
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
     * 更新施工区的面积
     *
     * @param $areaId
     * @return mixed
     */
    public function updateAreaArea($areaId)
    {
        $areaArea = (new ProjectSectionModel)->sumArea($areaId);
        return $this->update(['id'=>$areaId,'area'=>$areaArea]);
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