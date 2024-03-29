<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/20
 * Time: 10:03
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class EmployeeLivingModel
{
    private $table = 'employee_living';

    /**
     * @param $id
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    public function getLiving($id, $startTime, $endTime)
    {
        return DB::table($this->table)
            ->where('employeeId',$id)
            ->where('livingTime','>',$startTime)
            ->where('livingTime','<',$endTime)
            ->where('status',1)
            ->get()->toArray();
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function insert(array $input)
    {
        $input['createTime'] = date('Y-m-d H:i:s');
        if ($input['type'] == 2){
            $input['account'] *= -1;
        }
        return DB::table($this->table)->insertGetId($input);
    }

    /**
     * @param array $input
     * @param bool $exports
     * @return mixed
     */
    public function lists(array $input,$exports = false)
    {
        $result = DB::table($this->table)
            ->leftJoin('employee as e','e.id','=',$this->table.'.employeeId')
            ->where(function ($query) use ($input) {
                if (isset($input['projectId']) && !empty($input['projectId'])){
                    $query->where($this->table.'.projectId', $input['projectId']);
                }
                if (isset($input['startTime']) && !is_null($input['startTime'])){
                    $query->where('livingTime','>=',$input['startTime'].' 00:00:00');
                }
                if (isset($input['endTime']) && !is_null($input['endTime'])){
                    $query->where('livingTime','<=',$input['endTime'].' 23:59:59');
                }
                if (isset($input['status']) && !is_null($input['status'])) {
                    $query->where($this->table . '.status', $input['status']);
                }
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where(function ($query1) use ($input) {
                        $query1->where('e.name', 'like', '%' . $input['search'] . '%')->orWhere('e.jobNumber', 'like', '%' . $input['search'] . '%');
                    });
                }
            });
            if(!$exports){
                $limit = config('yucheng.limit');
                $start = is_null($input['start']) ? 0 : $input['start'];

                if (isset($input['length']) && !is_null($input['length'])) {
                    $limit = $input['length'];
                }
                $result = $result->offset($start)->limit($limit);
            }
            $result = $result->select($this->table . '.*', 'e.name as employeeName','e.jobNumber')
            ->get()->toArray();
            return $result;
    }

    /**
     * @param $pk
     * @param $data
     * @param $approvalResult
     */
    public function livingApproval($pk,$data,$approvalResult){
        DB::table($this->table)->where('id',$pk)->update(['status'=>$approvalResult]);
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function countLists(array $input)
    {
        return DB::table($this->table)
            ->leftJoin('employee as e','e.id','=',$this->table.'.employeeId')
            ->where(function ($query) use ($input) {
                $query->where($this->table.'.projectId', $input['projectId']);
                if (isset($input['startTime']) && !is_null($input['startTime'])){
                    $query->where('livingTime','>=',$input['startTime'].' 00:00:00');
                }
                if (isset($input['endTime']) && !is_null($input['endTime'])){
                    $query->where('livingTime','<=',$input['endTime'].' 23:59:59');
                }
                if (isset($input['status']) && !is_null($input['status'])) {
                    $query->where($this->table . '.status', $input['status']);
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
     * @return array
     */
    public function info($data = [])
    {
        $result = DB::table($this->table)->where($data)->first();
        return empty($result) ? [] : get_object_vars($result);
    }
}