<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/24
 * Time: 13:52
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class ProjectModel
{
    private $table = 'project';

    /**
     * @param array $data
     * @return mixed
     */
    public function lists(array $data)
    {
        $limit = config('yucheng.limit');
        $start = is_null($data['start']) ? 0 : $data['start'];

        if (isset($data['length']) && !is_null($data['length'])) {
            $limit = $data['length'];
        }
        return DB::table($this->table)
            ->where(function ($query) use ($data){
                $query->where('id','!=',1);
                if (isset($data['search']) && !is_null($data['search'])) {
                    $query->where(function ($query1) use ($data) {
                        $query1->where($this->table . '.name', 'like', '%' . $data['search'] . '%');
                    });
                }
            })
            ->offset($start)->limit($limit)
            ->get()->toArray();
    }

    public function insert(array $data)
    {
        $data['createTime'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->insertGetId($data);
    }

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
        $updateData = [
            'name'=>$data['name'],
            'city'=>$data['city'],
            'projectAmount'=>$data['projectAmount'],
            'projectAccount'=>$data['projectAccount'],
        ];
        return DB::table($this->table)->where('id',$data['id'])->update($updateData);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function updateStatus($data)
    {
        return DB::table($this->table)->where('id', $data['id'])->update(['status' => 2]);
    }

    /**
     * @return mixed
     */
    public function selectLists()
    {
        return DB::table($this->table)->where('id','>',1)->get()->toArray();
    }


    public function countLists(array $data)
    {
        return DB::table($this->table)
            ->where(function ($query) use ($data){
                $query->where('id','!=',1);
                if (isset($data['search']) && !is_null($data['search'])) {
                    $query->where(function ($query1) use ($data) {
                        $query1->where($this->table . '.name', 'like', '%' . $data['search'] . '%');
                    });
                }
            })
            ->count();
    }
}