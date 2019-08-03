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

class EmployeeModel extends Model
{
    private $table = 'employee';

    /**
     * @param $input
     * @return mixed
     */
    public function lists($input)
    {
        $limit = config('yucheng.limit');
        $start = is_null($input['start'])?0:$input['start'];

        if (isset($input['length']) && !is_null($input['length'])) {
            $limit = $input['length'];
        }

        return DB::table($this->table)
            ->leftJoin('profession as p','p.id','=',$this->table.'.professionId')
            ->where(function ($query) use ($input) {
                $query->where('projectId', $input['projectId'])->where('isFinish', 0);
                if (isset($input['professionId']) && !is_null($input['professionId'])) {
                    $query->where('professionId', $input['professionId']);
                }
                if (isset($input['status']) && !is_null($input['status'])) {
                    $query->where($this->table.'.status', $input['status']);
                } else {
                    $query->whereIn($this->table.'.status', [1, 3, 4]);
                }
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where(function ($query1) use ($input) {
                        $query1->where('name', 'like', '%' . $input['search'] . '%')->orWhere('jobNumber', 'like', '%' . $input['search'] . '%');
                    });
                }
            })
            ->offset($start)->limit($limit)
            ->select($this->table.'.*','p.name as professionName')
            ->get()->toArray();
    }

    /**
     * @param $data
     * @return array
     */
    public function info($data)
    {
        $result = DB::table($this->table)
            ->leftJoin('profession as pr','pr.id','=',$this->table.'.professionId')
            ->leftJoin('project as p','p.id','=',$this->table.'.projectId')
            ->where($this->table.'.id',$data['id'])
            ->select([$this->table.'.*','pr.name as professionName','p.name as projectName'])
            ->first();
        return empty($result) ? [] : get_object_vars($result);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function updateStatus($data)
    {
        return DB::table($this->table)->where('id', $data['id'])->update(['status' => $data['status']]);
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
        }else{
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
            if (!isset($wageList[$month][$item])){
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
    public function batchUpdateStatus($pk,$data,$approvalResult)
    {
        $employeeExperienceModel = new EmployeeExperienceModel();
        foreach ($data['ids'] as $id) {
            $this->updateStatus(['id' => $id, 'status' => $data['status']]);
            $info = $this->info(['id'=>$id]);
            $employeeExperienceModel->updateOutTime($id, $info['projectId']);
            if ($data['status'] == 3) {
                DB::table($this->table)->where('id', $id)->update(['resignTime'=>date('Y-m-d H:i:s')]);
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
    public function batchUpdateProject($pk,$data,$approvalResult)
    {
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
                    $employeeExperienceModel->insert(['employeeId' => $id, 'projectId' => $data['projectId'],'inTime' => date('Y-m-d H:i:s')]);
                });
            }
        } catch (QueryException $e) {
            $e->getBindings();
        }
        return true;
    }

    /**
     * @param array $input
     * @return mixed
     * @throws \Exception
     */
    public function attendanceList(array $input)
    {
        //获取考勤人员的列表
        $lists = DB::table($this->table)
            ->where('projectId', $input['projectId'])
            ->where('hasAttendance', 1)
            ->where('isFinish', 0)
            ->orderBy('status', 'asc')
            ->get()->toArray();
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
            foreach ($attendances as $a){
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
        return DB::table($this->table)->where($data)->update(['hasAttendance'=>1]);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function searchEmployee(array $data)
    {
        return DB::table($this->table)
            ->where('projectId',$data['projectId'])
            ->where(function ($query) use ($data){
                $query->where('name', 'like', '%' . $data['search'] . '%')->orWhere('jobNumber', 'like', '%' . $data['search'] . '%');
            })
            ->whereIn('status',[1,4])
            ->where('isFinish',0)
            ->get()->toArray();
    }


}