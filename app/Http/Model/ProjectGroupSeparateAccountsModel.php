<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/20
 * Time: 10:56
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class ProjectGroupSeparateAccountsModel
{
    private $table = 'project_group_separate_accounts';

    /**
     * @param $id
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    public function getSeparateAccounts($id, $startTime, $endTime)
    {
        return DB::table($this->table)
            ->where('employeeId',$id)
            ->where('separateTime','>',$startTime)
            ->where('separateTime','<',$endTime)
            ->where('status',1)
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function lists(array $data)
    {
        return DB::table($this->table)
            ->leftJoin('employee as e','e.id','=',$this->table.'.employeeId')
            ->leftJoin('project_group_members as pgm','pgm.id','=',$this->table.'.memberId')
            ->leftJoin('admin as a','a.id','=',$this->table.'.adminId')
            ->where($this->table.'.projectId',$data['projectId'])
            ->where('sectionId',$data['sectionId'])
            ->where($this->table.'.groupId',$data['groupId'])
            ->select($this->table.'.*','e.name as employeeName','e.jobNumber','pgm.isLeader','a.name as adminName')
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @return array
     */
    public function insert(array $data)
    {
        $result = DB::table($this->table)->insertGetId($data);
        return $result;
    }

    /**
     * @param $pk
     * @param $data
     * @param $approvalResult
     */
    public function separateApproval($pk,$data,$approvalResult)
    {
        foreach ($data['ids'] as $id){
            DB::table($this->table)->where('id',$id)->update(['status'=>$approvalResult]);
        }
    }

    /**
     * @param array $array
     * @return mixed
     */
    public function delete(array $array)
    {
        return DB::table($this->table)->where($array)->delete();
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
    public function listsByEmployee(array $data){
        return DB::table($this->table)
            ->where(function ($query) use ($data){
                $query->where('projectId',$data['projectId'])->where('employeeId',$data['employeeId']);
                if (!empty($data['month'])){
                    $startTime = (new \DateTime($data['month']))->format('Y-m-01 00:00:00');
                    $endTime = (new \DateTime($data['month']))->format('Y-m-t 23:59:59');
                    $query->where('separateTime','<=',$startTime)->where('separateTime','<=',$endTime);
                }else{
                    if(!empty($data['startTime'])){
                        $query->where('separateTime','<=',$data['startTime']);
                    }
                    if(!empty($data['endTime'])){
                        $query->where('separateTime','<=',$data['endTime']);
                    }
                }
            })
            ->select($this->table.'.*')
            ->get()->toArray();
    }

    /**
     * @param array $array
     * @param array $updateData
     * @return mixed
     */
    public function update(array $array, array $updateData)
    {
        return DB::table($this->table)->where($array)->update($updateData);
    }

}