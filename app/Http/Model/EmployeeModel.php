<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/19
 * Time: 14:59
 */

namespace App\Http\Model;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * @property  employeeStatus
 */
class EmployeeModel extends Model
{
    private $table = 'employee';
    public $employeeStatus = [1,2,3,4];

    /**
     * @param array $input
     * @param array $status
     * @return mixed
     */
    public function lists(array $input, $status = [1, 3, 4])
    {
        $limit = config('yucheng.limit');
        $start = is_null($input['start']) ? 0 : $input['start'];

        if (isset($input['length']) && !is_null($input['length'])) {
            $limit = $input['length'];
        }

        return DB::table($this->table)
            ->leftJoin('profession as p', 'p.id', '=', $this->table . '.professionId')
            ->leftJoin('project', 'project.id', '=', $this->table . '.projectId')
            ->where(function ($query) use ($input, $status) {
                $query->where('isFinish', 0);
                if (isset($input['projectId']) && !empty($input['projectId'])) {
                    $query->where('projectId', $input['projectId']);
                }
                if (isset($input['professionId']) && !is_null($input['professionId'])) {
                    $query->where('professionId', $input['professionId']);
                }
                if (isset($input['status']) && !is_null($input['status'])) {
                    $query->where($this->table . '.status', $input['status']);
                } else {
                    $query->whereIn($this->table . '.status', $status);
                }
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where(function ($query1) use ($input) {
                        $query1->where($this->table . '.name', 'like', '%' . $input['search'] . '%')->orWhere('jobNumber', 'like', '%' . $input['search'] . '%');
                    });
                }
            })
            ->offset($start)->limit($limit)
            ->select($this->table . '.*', 'p.name as professionName', 'project.name as projectName')
            ->get()->toArray();
    }

    /**
     * @param array $input
     * @param array $status
     * @return mixed
     */
    public function countLists(array $input, $status = [1, 3, 4])
    {
        return DB::table($this->table)
            ->leftJoin('profession as p', 'p.id', '=', $this->table . '.professionId')
            ->leftJoin('project', 'project.id', '=', $this->table . '.projectId')
            ->where(function ($query) use ($input, $status) {
                $query->where('isFinish', 0);
                if (isset($input['projectId']) && !empty($input['projectId'])) {
                    $query->where('projectId', $input['projectId']);
                }
                if (isset($input['professionId']) && !is_null($input['professionId'])) {
                    $query->where('professionId', $input['professionId']);
                }
                if (isset($input['status']) && !is_null($input['status'])) {
                    $query->where($this->table . '.status', $input['status']);
                } else {
                    $query->whereIn($this->table . '.status', $status);
                }
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where(function ($query1) use ($input) {
                        $query1->where($this->table . '.name', 'like', '%' . $input['search'] . '%')->orWhere('jobNumber', 'like', '%' . $input['search'] . '%');
                    });
                }
            })
            ->count();
    }

    /**
     * @param $data
     * @return array
     */
    public function info($data)
    {
        $result = DB::table($this->table)
            ->leftJoin('profession as pr', 'pr.id', '=', $this->table . '.professionId')
            ->leftJoin('project as p', 'p.id', '=', $this->table . '.projectId')
            ->where($this->table . '.id', $data['id'])
            ->select([$this->table . '.*', 'pr.name as professionName', 'p.name as projectName'])
            ->first();
        return empty($result) ? [] : get_object_vars($result);
    }

    /**
     * @param $pk
     * @param array $data
     * @return mixed
     */
    public function update($pk, array $data)
    {
        return DB::table($this->table)->where('id', $pk)->update($data);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function updateStatus($data)
    {
        return $this->update($data['id'], ['status' => $data['status']]);
    }

    /**
     * @param array $input
     * @return array
     * @throws \Exception
     */
    public function employeeInfo(Array $input)
    {
        $info = [
            'baseInfo' => '',
            'experience' => [],
            'wages' => [],
        ];
        $info['baseInfo'] = $this->info($input);
        $info['experience'] = (new EmployeeExperienceModel())->lists($input['id']);
        $info['wages'] = $this->wages($input['id']);
        return $info;
    }

    /**
     * @param $id
     * @return array|mixed
     * @throws \Exception
     */
    public function wages($id)
    {
        $findYear = (new YearModel())->findYear(date('Y-m-d H:i:s', time()));
        if (empty($findYear)) {
            $findYear['startTime'] = date('Y-01-01 00:00:00');
            $findYear['endTime'] = date('Y-12-31 23:59:59');
        } else {
            $findYear = get_object_vars($findYear);
        }

        //奖金
        $bonus = (new EmployeeOtherFeesModel())->getBonus($id, $findYear['startTime'], $findYear['endTime']);
        //罚款
        $fine = (new EmployeeOtherFeesModel())->getFine($id, $findYear['startTime'], $findYear['endTime']);
        //生活费
        $living = (new EmployeeLivingModel())->getLiving($id, $findYear['startTime'], $findYear['endTime']);
        //借款
        $loan = (new EmployeeLoanModel())->getLoan($id, $findYear['startTime'], $findYear['endTime']);
        //材料费
        $materialOrder = (new EmployeeMaterialOrderModel())->getMaterialOrder($id, $findYear['startTime'], $findYear['endTime']);
        //出勤工资
        $attendance = (new EmployeeAttendanceModel())->getAttendances($id, $findYear['startTime'], $findYear['endTime']);
        //包工费
        $separateAccounts = (new ProjectGroupSeparateAccountsModel())->getSeparateAccounts($id, $findYear['startTime'], $findYear['endTime']);
        //杂工费
        $otherSeparateAccounts = (new ProjectOtherSeparateAccountsModel())->getOtherSeparateAccounts($id, $findYear['startTime'], $findYear['endTime']);

        $wageList = $this->datePeriod($findYear['startTime'], $findYear['endTime'], '1 month', 'Y-m');
        $endTime = (new \DateTime($findYear['endTime']))->format('Y-m');
        end($wageList);
        if (key($wageList) != $endTime) {
            $wageList[$endTime] = '';
        }
        foreach ($wageList as $key => $w) {
            $wageList[$key] = [
                'bonus' => 0,
                'fine' => 0,
                'living' => 0,
                'loan' => 0,
                'materialOrder' => 0,
                'attendance' => 0,
                'separateAccounts' => 0,
                'otherSeparateAccounts' => 0,
            ];
        }

        $wageList = $this->wageList($wageList, $bonus, 'bonus', 'logTime');

        $wageList = $this->wageList($wageList, $fine, 'fine', 'logTime');
        $wageList = $this->wageList($wageList, $living, 'living', 'livingTime');
        $wageList = $this->wageList($wageList, $loan, 'loan', 'loanTime');
        $wageList = $this->wageList($wageList, $materialOrder, 'materialOrder', 'orderTime');
        $wageList = $this->wageList($wageList, $attendance, 'attendance', 'day', 'length');
        $wageList = $this->wageList($wageList, $separateAccounts, 'separateAccounts', 'separateTime');
        $wageList = $this->wageList($wageList, $otherSeparateAccounts, 'otherSeparateAccounts', 'separateTime');

        return $wageList;
    }

    /**
     * @param $wageList
     * @param $itemList
     * @param $item
     * @param $timeColumn
     * @param string $calcColumn
     * @return mixed
     * @throws \Exception
     */
    public function wageList($wageList, $itemList, $item, $timeColumn, $calcColumn = 'account')
    {
        foreach ($itemList as $i) {
            $month = (new \DateTime($i->$timeColumn))->format('Y-m');
            if (!isset($wageList[$month][$item])) {
                $wageList[$month][$item] = 0;
            }
            $wageList[$month][$item] += $i->$calcColumn;
        }
        return $wageList;
    }

    /**
     * @param $pk
     * @param $data
     * @param $approvalResult
     * @return mixed
     */
    public function batchUpdateStatus($pk, $data, $approvalResult)
    {
        if ($approvalResult != 1) {
            return true;
        }
        $employeeExperienceModel = new EmployeeExperienceModel();
        foreach ($data['ids'] as $id) {
            $this->updateStatus(['id' => $id, 'status' => $data['status']]);
            $info = $this->info(['id' => $id]);
            $employeeExperienceModel->updateOutTime($id, $info['projectId']);
            if ($data['status'] == 3) {
                DB::table($this->table)->where('id', $id)->update(['resignTime' => date('Y-m-d H:i:s')]);
            }
        }

        return true;
    }

    /**
     * @param $pk
     * @param $data
     * @param $approvalResult
     * @return mixed
     */
    public function batchUpdateProject($pk, $data, $approvalResult)
    {
        if ($approvalResult != 1) {
            return true;
        }
        $employeeExperienceModel = new EmployeeExperienceModel();
        try {
            foreach ($data['ids'] as $id) {
                DB::transaction(function () use ($id, $data, $employeeExperienceModel) {
                    //查出当前的项目id
                    $info = $this->info(['id' => $id]);
                    $oldProject = $info['projectId'];
                    //添加当前项目的离项时间
                    $employeeExperienceModel->updateOutTime($id, $oldProject);
                    //更新人员当前项目
                    DB::table($this->table)->where('id', $id)->update(['projectId' => $data['projectId']]);
                    //添加新项目的入项时间
                    $employeeExperienceModel->insert(['employeeId' => $id, 'projectId' => $data['projectId'], 'inTime' => date('Y-m-d H:i:s')]);
                });
            }
        } catch (QueryException $e) {
            $e->getBindings();
        }
        return true;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function attendanceEmployee(array $data)
    {
        $result = DB::table($this->table)
            ->leftJoin('profession as p', 'p.id', '=', $this->table . '.professionId')
            ->leftJoin('project', 'project.id', '=', $this->table . '.projectId')
            ->where(function ($query) use ($data) {
                $query->where('hasAttendance', 1)->where('isFinish', 0);
                if (isset($data['projectId']) && !empty($data['projectId'])) {
                    $query->where('projectId', $data['projectId']);
                }
                if (isset($data['professionId']) && !empty($data['professionId'])) {
                    $query->where('professionId', $data['professionId']);
                }
                if (isset($data['search']) && !is_null($data['search'])) {
                    $query->where(function ($query1) use ($data) {
                        $query1->where($this->table . '.name', 'like', '%' . $data['search'] . '%')->orWhere('jobNumber', 'like', '%' . $data['search'] . '%');
                    });
                }
            })
            ->orderBy('status', 'asc')
            ->select($this->table . '.*', 'p.name as professionName', 'project.name as projectName');

            if (isset($data['draw'])) {
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
    private function countAttendanceEmployee(array $data)
    {
        return DB::table($this->table)
            ->where(function ($query) use ($data) {
                $query->where('hasAttendance', 1)->where('isFinish', 0);
                if (isset($data['projectId']) && !empty($data['projectId'])) {
                    $query->where('projectId', $data['projectId']);
                }
                if (isset($data['professionId']) && !empty($data['professionId'])) {
                    $query->where('professionId', $data['professionId']);
                }
                if (isset($data['search']) && !is_null($data['search'])) {
                    $query->where(function ($query1) use ($data) {
                        $query1->where($this->table . '.name', 'like', '%' . $data['search'] . '%')->orWhere('jobNumber', 'like', '%' . $data['search'] . '%');
                    });
                }
            })
            ->count();
    }

    /**
     * @param array $input
     * @return mixed
     * @throws \Exception
     */
    public function attendanceList(array $input)
    {
        //获取考勤人员的列表
        $lists = $this->attendanceEmployee($input);
        if (count($lists) == 0) {
            return $lists;
        }

        //获取月考勤，不传月份则默认为当前月份
        $startTime = (new \DateTime($input['month']))->format('Y-m-01');
        $endTime = (new \DateTime($input['month']))->format('Y-m-t');
        $employeeAttendanceModel = new EmployeeAttendanceModel();
        foreach ($lists as $key => $l) {
            $dayList = $this->datePeriod($startTime, $endTime, '1 day', 'j');
            $attendances = $employeeAttendanceModel->getLists($l->id, $input['projectId'], $startTime, $endTime);
            foreach ($attendances as $a) {
                $d = (new \DateTime($a->day))->format('j');
                $dayList[$d] = $a->length;
            }
            $lists[$key]->attendance = $dayList;
        }

        return $lists;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function hasAttendance(array $data)
    {
        return DB::table($this->table)->where($data)->update(['hasAttendance' => 1]);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function searchEmployee(array $data)
    {
        return DB::table($this->table)
            ->where(function ($query) use ($data) {
                if (isset($data['projectId']) && !empty($data['projectId'])) {
                    $query->where('projectId', $data['projectId']);
                }
                $query->where('name', 'like', '%' . $data['search'] . '%')->orWhere('jobNumber', 'like', '%' . $data['search'] . '%');
            })
            ->whereIn('status', [1, 4])
            ->where('isFinish', 0)
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function dayValueLists(array $data)
    {
        $lists = $this->attendanceEmployee($data);
        $employeeAttendanceModel = new EmployeeAttendanceModel();

        $findYear = (new YearModel())->findYear(date('Y-m-d H:i:s', time()));
        if (empty($findYear)) {
            $findYear['startTime'] = date('Y-01-01 00:00:00');
            $findYear['endTime'] = date('Y-12-31 23:59:59');
        } else {
            $findYear = get_object_vars($findYear);
        }

        foreach ($lists as $key => $l) {
            $attendance = $employeeAttendanceModel->getAttendancesSum($l->id, $findYear['startTime'], $findYear['endTime']);
            $lists[$key]->attendance = $attendance->attendance;
        }

        return $lists;
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function insert(array $input)
    {
        $input['entryTime'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->insertGetId($input);
    }

    /**
     * 检查工号和当前年度所有未进行清算的工人的工号是否相同
     *
     * @param $param
     * @return mixed
     */
    public function checkJobNumber($param)
    {
        return DB::table($this->table)
            ->where(function ($query) use ($param) {
                $query->where('jobNumber', $param)->where('isFinish', 0);
            })
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function countDayValueLists(array $data)
    {
        return $this->countAttendanceEmployee($data);
    }


}