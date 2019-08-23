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
        $start = 0;

        if (isset($input['limit']) && !is_null($input['limit'])) {
            $limit = $input['limit'];
        }

        if (isset($input['page']) && !is_null($input['page'])) {
            $start = ($input['page'] - 1) * $limit;
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
                if (isset($input['search']) && $input['search'] != 0) {
                    $query->where(function ($query1) use ($input) {
                        $query1->where('e.name', 'like', $input['search'])->orWhere('e.jobNumber', 'like', $input['search']);
                    });
                }
            })
            ->orderBy($this->table . '.separateTime', 'des')
            ->offset($start)->limit($limit)
            ->select($this->table . '.*', 'e.name as employeeName', 'e.jobNumber', 'p.name as professionName', 'pa.name as areaName', 'ps.name as sectionName', 'a.name as assignmentName','project.name as projectName')
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
//            ->leftJoin('project', 'project.id', '=', $this->table . '.projectId')
//            ->leftJoin('profession as p', 'p.id', '=', $this->table . '.professionId')
//            ->leftJoin('project_area as pa', 'pa.id', '=', $this->table . '.areaId')
//            ->leftJoin('project_section as ps', 'ps.id', '=', $this->table . '.sectionId')
//            ->leftJoin('assignment as a', 'a.id', '=', $this->table . '.assignmentId')
            ->where(function ($query) use ($input) {
                if (isset($input['projectId']) && $input['projectId'] != 0) {
                    $query->where($this->table . '.projectId', $input['projectId']);
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
        $insertDataId = [];
        foreach ($data['data'] as $d) {
            $insertData = [
                'projectId' => $data['projectId'],
                'employeeId' => $data['employeeId'],
                'areaId' => $d['areaId'],
                'sectionId' => $d['sectionId'],
                'professionId' => $d['professionId'],
                'assignmentId' => $d['assignmentId'],
                'assignmentDetail' => $d['assignmentDetail'],
                'account' => $data['account'] / count($data['data']),
                'separateTime' => $data['separateTime'],
                'createTime' => date('Y-m-d H:i:s'),
            ];
            $result = DB::table($this->table)->insertGetId($insertData);
            $insertDataId[] = $result;
        }

        return $insertDataId;
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
        foreach ($data['ids'] as $id) {
            DB::table($this->table)->where('id', $id)->update(['status' => $approvalResult]);
        }
    }


}