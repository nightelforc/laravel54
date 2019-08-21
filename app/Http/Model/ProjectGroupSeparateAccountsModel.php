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
            ->leftJoin('employee as e','e.id','=',$this->table.'.id')
            ->leftJoin('project_group_members as pgm','pgm.id','=',$this->table.'.memberId')
            ->where($this->table.'.projectId',$data['projectId'])
            ->where('sectionId',$data['sectionId'])
            ->where($this->table.'.groupId',$data['groupId'])
            ->select($this->table.'.*','e.name as employeeName','e.jobNumber','pgm.isLeader')
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @return array
     */
    public function insert(array $data)
    {
        $insertDataId = [];
        foreach ($data['data'] as $d){
            $insertData = [
                'projectId'=>$data['projectId'],
                'areaId'=>$data['areaId'],
                'sectionId'=>$data['sectionId'],
                'groupId'=>$data['groupId'],
                'memberId'=>$d['memberId'],
                'employeeId'=>$d['employeeId'],
                'account'=>$d['account'],
                'remark'=>$d['remark'],
                'separateTime'=>date('Y-m-d H:i:s'),
                'createTime' =>date('Y-m-d H:i:s'),
            ];
            $result = DB::table($this->table)->insertGetId($insertData);
            $insertDataId[] = $result;
        }

        return $insertDataId;
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

}