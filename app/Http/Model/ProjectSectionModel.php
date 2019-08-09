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
            ->where(function ($query) use ($data) {
                $query->where('areaId', $data['areaId']);
                if (isset($data['search']) && !is_null($data['search'])) {
                    $query->where('name', 'like', '%' . $data['search'] . '%');
                }
            })
            ->offset($start)->limit($limit)
            ->get()->toArray();
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
}