<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/20
 * Time: 9:14
 */

namespace App\Http\Model;

use Illuminate\Support\Facades\DB;

class EmployeeExperienceModel
{
    private $table = 'employee_experience';

    /**
     * @param $id
     * @return mixed
     */
    public function lists($id)
    {
        $field = [$this->table . '.*', 'p.name as projectName'];
        return DB::table($this->table)
            ->leftJoin('project as p', 'p.id', '=', $this->table . '.projectId')
            ->where('employeeId', $id)
            ->get($field)->toArray();
    }

    /**
     * @param $employeeId
     * @param $projectId
     * @return mixed
     */
    public function updateOutTime($employeeId, $projectId)
    {
        return DB::table($this->table)
            ->where('employeeId', $employeeId)
            ->where('projectId', $projectId)
            ->whereNull('outTime')
            ->update(['outTime' => date('Y-m-d H:i:s')]);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        return DB::table($this->table)->insert($data);
    }

    /**
     * @param array $data
     * @return array
     */
    public function info($data = [])
    {
        $result = DB::table($this->table)->where($data)->first();
        return empty($result) ? [] : get_object_vars($result);
    }
}