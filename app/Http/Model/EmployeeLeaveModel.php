<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/24
 * Time: 9:49
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class EmployeeLeaveModel
{
    private $table = 'employee_leave';

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
            ->where(function ($query) use ($input) {
                if (isset($input['projectId']) && !empty($input['projectId'])) {
                    $query->where($this->table.'.projectId', $input['projectId']);
                }
                if (isset($input['professionId']) && !empty($input['professionId'])) {
                    $query->where('e.professionId', $input['professionId']);
                }
                if (isset($input['backStatus']) && !is_null($input['backStatus'])) {
                    $query->where($this->table.'.backStatus', $input['backStatus']);
                }
                if (isset($input['status']) && !is_null($input['status'])) {
                    $query->where($this->table.'.status', $input['status']);
                }
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where(function ($query1) use ($input) {
                        $query1->where('e.name', 'like', '%' . $input['search'] . '%')->orWhere('e.jobNumber', 'like', '%' . $input['search'] . '%');
                    });
                }
            })
            ->leftJoin('employee as e','e.id','=',$this->table.'.employeeId')
            ->leftJoin('profession as p','p.id','=','e.professionId')
            ->leftJoin('project','project.id','=','e.projectId')
            ->offset($start)->limit($limit)
            ->orderBy('createTime','desc')
            ->select($this->table.'.*','e.name as employeeName','e.jobNumber','p.name as professionName','e.status as employeeStatus','project.name as projectName')
            ->get()->toArray();
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function countLists(array $input)
    {
        return DB::table($this->table)
            ->leftJoin('employee as e','e.id','=',$this->table.'.employeeId')
            ->leftJoin('profession as p','p.id','=','e.professionId')
            ->where(function ($query) use ($input) {
                if (isset($input['projectId']) && !empty($input['projectId'])) {
                    $query->where($this->table.'.projectId', $input['projectId']);
                }
                if (isset($input['professionId']) && !empty($input['professionId'])) {
                    $query->where('e.professionId', $input['professionId']);
                }
                if (isset($input['backStatus']) && !is_null($input['backStatus'])) {
                    $query->where($this->table.'.backStatus', $input['backStatus']);
                }
                if (isset($input['status']) && !is_null($input['status'])) {
                    $query->where($this->table.'.status', $input['status']);
                }
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where(function ($query1) use ($input) {
                        $query1->where('e.name', 'like', '%' . $input['search'] . '%')->orWhere('e.jobNumber', 'like', '%' . $input['search'] . '%');
                    });
                }
            })
            ->count();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function back(array $data)
    {
        $info  = get_object_vars(DB::table($this->table)->where('id',$data['id'])->first());
        (new EmployeeModel())->updateStatus(['id'=>$info['employeeId'],'status'=>1]);
        return DB::table($this->table)
            ->where('id',$data['id'])
            ->update(['backTime'=>$data['backTime'],'backStatus'=>1]);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        $data['createTime'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->insertGetId($data);
    }

    /**
     * @param $pk
     * @param $data
     * @param $approvalResult
     */
    public function leaveApproval($pk,$data,$approvalResult)
    {
        //变更审批状态
        DB::table($this->table)->where('id',$pk)->update(['status'=>$approvalResult]);
        if ($approvalResult == 1){
            //修改工人的status为3请假
            (new EmployeeModel())->updateStatus(['id'=>$data['employeeId'],'status'=>3]);
        }

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