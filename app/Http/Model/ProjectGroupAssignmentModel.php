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
            ->where('status',1)
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
            ->where($data)
            ->select($this->table.'.*','a.name as assignmentName')
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
    }
}