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

    /**
     * @param array $input
     * @return mixed
     */
    public function lists(array $input)
    {
        $limit = config('yucheng.limit');
        $start = is_null($input['start']) ? 0 : $input['start'];

        if (isset($input['limit']) && !is_null($input['limit'])) {
            $limit = $input['limit'];
        }

        return DB::table($this->table)
            ->where(function ($query) use ($input) {
                $query->where('projectId', $input['projectId']);
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where('name', 'like', '%' . $input['search'] . '%');
                }
            })
            ->offset($start)->limit($limit)->get()->toArray();
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
}