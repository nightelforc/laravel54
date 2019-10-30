<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/25
 * Time: 14:53
 */

namespace App\Http\Model;

use Illuminate\Support\Facades\DB;

class ProjectGroupAssignmentModel
{
    private $table = 'project_group_assignment';

    /**
     * @param array $data
     * @return mixed
     */
    public function isAssignment(array $data){
        return DB::table($this->table)->where($data)->count();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function costLists(array $data)
    {
        return DB::table($this->table)
            ->leftJoin('profession as p','p.id','=',$this->table.'.professionId')
            ->where($data)
            ->where($this->table.'.status',1)
            ->groupBy('professionId')
            ->select('p.id','projectId','areaId','sectionId','professionId','p.name as professionName',DB::raw('sum(totalPrice) as totalPrice'),DB::raw('sum(amount) as amount'))
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function sectionLists(array $data){
        return DB::table($this->table)
            ->leftJoin('project_group as pg','pg.id','=',$this->table.'.groupId')
            ->where($data)
            ->groupBy('groupId')
            ->select($this->table.'.id',$this->table.'.projectId','areaId','sectionId','groupId','pg.name as groupName')
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function lists(array $data)
    {
        return DB::table($this->table)
            ->leftJoin('assignment as a','a.id','=',$this->table.'.assignmentId')
            ->leftJoin('unit as u','u.id','=','a.unitId')
            ->leftJoin('admin','admin.id','=',$this->table.'.adminId')
            ->where(function($query) use ($data){
                $query->where($this->table.'.projectId',$data['projectId']);
                $query->where($this->table.'.sectionId',$data['sectionId']);
                $query->where($this->table.'.groupId',$data['groupId']);
            })
            ->select($this->table.'.*','a.name as assignmentName','u.name as unitName','admin.name as adminName')
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
                'groupId'=>$data['groupId'],
                'areaId'=>$data['areaId'],
                'sectionId'=>$data['sectionId'],
                'professionId'=>$data['professionId'],
                'assignmentId'=>$d['assignmentId'],
                'amount'=>$d['amount'],
                'price'=>$d['price'],
                'totalPrice'=>$d['totalPrice'],
                'completeTime'=>date('Y-m-d H:i:s'),
                'createTime' =>date('Y-m-d H:i:s'),
                'adminId' =>$data['adminId'],
                'remark' =>$d['remark'],
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
    public function assignmentApproval($pk,$data,$approvalResult)
    {
        foreach ($data['ids'] as $id){
            DB::table($this->table)->where('id',$id)->update(['status'=>$approvalResult]);
        }
        //更新施工段成本
        $cost = $this->sumCost(['sectionId'=>$data['sectionId']]);
        (new ProjectSectionModel())->update(['id'=>$data['sectionId'],'cost'=>$cost]);
        //更新施工区成本
        $cost = $this->sumCost(['areaId'=>$data['areaId']]);
        (new ProjectAreaModel())->update(['id'=>$data['areaId'],'cost'=>$cost]);
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
     * @param $data
     * @return mixed
     */
    public function delete($data){
        return DB::table($this->table)->where($data)->delete();
    }

    /**
     * @param array $data
     * @return bool
     */
    public function delAssignment(array $data)
    {
        try{
            $result = DB::transaction(function () use ($data) {
                $info = $this->info($data);
                $this->delete($data);
                (new ProjectGroupSeparateAccountsModel())->delete([
                    'projectId'=>$info['projectId'],
                    'areaId'=>$info['areaId'],
                    'sectionId'=>$info['sectionId'],
                    'groupId'=>$info['groupId'],
                ]);

                return true;
            });

            if ($result){
                return true;
            }else{
                return false;
            }
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * @param array $data
     * @return mixed
     */
    private function sumCost(array $data)
    {
        return DB::table($this->table)->where($data)->sum('totalPrice');
    }
}