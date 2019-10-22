<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/20
 * Time: 11:00
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class ProjectOtherSeparateAccountsModel
{
    private $table = 'project_other_separate_accounts';

    /**
     * @param $id
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    public function getOtherSeparateAccounts($id, $startTime, $endTime)
    {
        return DB::table($this->table)
            ->where('employeeId', $id)
            ->where('separateTime', '>', $startTime)
            ->where('separateTime', '<', $endTime)
            ->where('status', 1)
            ->get()->toArray();
    }

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
            ->leftJoin('employee as e', 'e.id', '=', $this->table . '.employeeId')
            ->leftJoin('project', 'project.id', '=', $this->table . '.projectId')
            ->leftJoin('profession as p', 'p.id', '=', $this->table . '.professionId')
            ->leftJoin('project_area as pa', 'pa.id', '=', $this->table . '.areaId')
            ->leftJoin('project_section as ps', 'ps.id', '=', $this->table . '.sectionId')
            ->leftJoin('assignment as a', 'a.id', '=', $this->table . '.assignmentId')
            ->where(function ($query) use ($input) {
                if (isset($input['projectId']) && $input['projectId'] != 0) {
                    $query->where($this->table . '.projectId', $input['projectId']);
                }
                if (isset($input['sectionId']) && $input['sectionId'] != 0) {
                    $query->where($this->table . '.sectionId', $input['sectionId']);
                }
                if (isset($input['search']) && $input['search'] != 0) {
                    $query->where(function ($query1) use ($input) {
                        $query1->where('e.name', 'like', '%' . $input['search'] . '%')->orWhere('e.jobNumber', 'like', '%' . $input['search'] . '%');
                    });
                }
            })
            ->orderBy($this->table . '.separateTime', 'des')
            ->offset($start)->limit($limit)
            ->select($this->table . '.*', 'e.name as employeeName', 'e.jobNumber', 'p.name as professionName', 'pa.name as areaName', 'ps.name as sectionName', 'a.name as assignmentName', 'project.name as projectName')
            ->get()->toArray();
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function lists2(array $input)
    {
        return DB::table($this->table)
            ->leftJoin('employee as e', 'e.id', '=', $this->table . '.employeeId')
            ->leftJoin('project', 'project.id', '=', $this->table . '.projectId')
            ->leftJoin('profession as p', 'p.id', '=', $this->table . '.professionId')
            ->leftJoin('project_area as pa', 'pa.id', '=', $this->table . '.areaId')
            ->leftJoin('project_section as ps', 'ps.id', '=', $this->table . '.sectionId')
            ->leftJoin('assignment as a', 'a.id', '=', $this->table . '.assignmentId')
            ->where(function ($query) use ($input) {
                if (isset($input['projectId']) && $input['projectId'] != 0) {
                    $query->where($this->table . '.projectId', $input['projectId']);
                }
                $query->where($this->table . '.createTime','>', date("Y-m-d H:i:s",strtotime("-3 day")));
            })
            ->orderBy($this->table . '.separateTime', 'des')
            ->select($this->table . '.*', 'e.name as employeeName', 'e.jobNumber', 'p.name as professionName', 'pa.name as areaName', 'ps.name as sectionName', 'a.name as assignmentName', 'project.name as projectName')
            ->get()->toArray();
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function countLists(array $input)
    {
        return DB::table($this->table)
            ->leftJoin('employee as e', 'e.id', '=', $this->table . '.employeeId')
            ->leftJoin('project', 'project.id', '=', $this->table . '.projectId')
            ->leftJoin('profession as p', 'p.id', '=', $this->table . '.professionId')
            ->leftJoin('project_area as pa', 'pa.id', '=', $this->table . '.areaId')
            ->leftJoin('project_section as ps', 'ps.id', '=', $this->table . '.sectionId')
            ->leftJoin('assignment as a', 'a.id', '=', $this->table . '.assignmentId')
            ->where(function ($query) use ($input) {
                if (isset($input['projectId']) && $input['projectId'] != 0) {
                    $query->where($this->table . '.projectId', $input['projectId']);
                }
                if (isset($input['sectionId']) && $input['sectionId'] != 0) {
                    $query->where($this->table . '.sectionId', $input['sectionId']);
                }
                if (isset($input['search']) && $input['search'] != 0) {
                    $query->where(function ($query1) use ($input) {
                        $query1->where('e.name', 'like', $input['search'])->orWhere('e.jobNumber', 'like', $input['search']);
                    });
                }
            })
            ->count();
    }

    /**
     * @param $data
     * @return array
     */
    public function insert($data)
    {
        $insertData = [
            'projectId' => $data['projectId'],
            'employeeId' => $data['employeeId'],
            'areaId' => $data['areaId'],
            'sectionId' => $data['sectionId'],
            'professionId' => $data['professionId'],
            'assignmentId' => $data['assignmentId'],
            'assignmentDetail' => $data['assignmentDetail'],
            'account' => $data['account'],
            'separateTime' => $data['separateTime'],
            'createTime' => date('Y-m-d H:i:s'),
        ];
        $result = DB::table($this->table)->insertGetId($insertData);
        return $result;
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
     * @param $pk
     * @param $data
     * @param $approvalResult
     */
    public function otherSeparateApproval($pk, $data, $approvalResult)
    {
        DB::table($this->table)->where('id', $pk)->update(['status' => $approvalResult]);
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

    public function otherSeparateSummary(array $data){
        return DB::table($this->table)->where($data)->sum('account');
    }


}