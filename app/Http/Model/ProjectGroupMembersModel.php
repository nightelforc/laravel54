<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/25
 * Time: 9:22
 */

namespace App\Http\Model;

use function Composer\Autoload\includeFile;
use Illuminate\Support\Facades\DB;

class ProjectGroupMembersModel
{
    private $table = 'project_group_members';

    /**
     * @param array $data
     * @return mixed
     */
    public function lists(array $data)
    {
        return DB::table($this->table)
            ->leftJoin('employee as e','e.id','=',$this->table.'.employeeId')
            ->where($this->table.'.groupId',$data['groupId'])
            ->where('isDel',0)
            ->get([$this->table.'.*','e.jobNumber','e.name as employeeName','e.status'])->toArray();
    }

    /**
     * @param array $data
     * @return bool
     */
    public function isInGroup(array $data)
    {
        $result = DB::table($this->table)
            ->leftJoin('project_group as pg','pg.id','=',$this->table.'.groupId')
            ->whereIn('pg.status',[1,2])
            ->where($this->table.'.projectId',$data['projectId'])
            ->where($this->table.'.employeeId',$data['employeeId'])
            ->where('isDel',0)
            ->first();
        if (empty($result)){
            return false;
        }else{
            return get_object_vars($result);
        }
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function info(array $data){
        $result = DB::table($this->table)->where($data)->first();
        return empty($result) ? [] : get_object_vars($result);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data){
        return DB::table($this->table)->insert($data);
    }

    /**
     * @param array $where
     * @param array $data
     * @return mixed
     */
    public function update(array $where, array $data)
    {
        return DB::table($this->table)->where($where)->update($data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function delMember(array $data)
    {
        try{
            $result = DB::transaction(function () use ($data){
                $info = $this->info(['id'=>$data['id']]);
                if($info['isLeader'] == 1){
                    (new ProjectGroupModel())->updateIsLeader(['groupId'=>$info['groupId'],'groupLeader'=>null]);
                }
                $this->update(['id'=>$data['id']],['isDel'=>1,'isLeader'=>0]);
                return true;
            });
            if ($result){
                return true;
            }else{
                return false;
            }
        }catch(\Exception $e){
            return false;
        }
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function memberSeparateLists(array $data)
    {
        return DB::table($this->table)
            ->leftJoin('project_group_separate_accounts as pgsa','pgsa.memberId','=',$this->table.'.id')
            ->leftJoin('employee as e','e.id','=',$this->table.'.employeeId')
            ->where($this->table.'.projectId',$data['projectId'])
            ->where(function ($query) use ($data){
                $query->where('sectionId',$data['sectionId'])->orWhere('sectionId',null);
            })
            ->where($this->table.'.groupId',$data['groupId'])
            ->where($this->table.'.isDel',0)
            ->select('pgsa.account','pgsa.remark','pgsa.separateTime','pgsa.status','e.name as employeeName','e.jobNumber',$this->table.'.isLeader',$this->table.'.id as memberId',$this->table.'.employeeId')
            ->get()->toArray();
    }
}