<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/24
 * Time: 14:45
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class ProjectGroupModel
{
    private $table = 'project_group';
    const TABLE = 'project_group';
    private $startTime = '';
    private $endTime = '';

    public function __construct()
    {
        $yearModel = new YearModel();
        $info = get_object_vars($yearModel->findYear(date('Y-m-d H:i:s')));
        $this->startTime = $info['startTime'];
        $this->endTime = $info['endTime'];
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function lists(array $data)
    {
        return DB::table($this->table)
            ->leftJoin('profession as p', 'p.id', '=', $this->table . '.professionId')
            ->leftJoin('project', 'project.id', '=', $this->table . '.projectId')
            ->leftJoin('employee as e', 'e.id', $this->table . '.groupLeader')
            ->where(function ($query) use ($data) {
                $query->where($this->table . '.projectId', $data['projectId'])
                    ->whereIn($this->table . '.status', [1, 2])
                    ->where($this->table . '.createTime', '>', $this->startTime)
                    ->where($this->table . '.createTime', '<', $this->endTime);;
                if (isset($data['professionId']) && !is_null($data['professionId'])) {
                    $query->where($this->table . '.professionId', $data['professionId']);
                }
                if (isset($data['search']) && !is_null($data['search'])) {
                    $query->where($this->table . '.name', 'like', '%' . $data['search'] . '%');
                }
            })
            ->select($this->table . '.*', 'p.name as professionName', 'e.name as employeeName', 'project.name as projectName')
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function countLists(array $data)
    {
        return DB::table($this->table)
            ->leftJoin('profession as p', 'p.id', '=', $this->table . '.professionId')
            ->leftJoin('project', 'project.id', '=', $this->table . '.projectId')
            ->leftJoin('employee as e', 'e.id', $this->table . '.groupLeader')
            ->where(function ($query) use ($data) {
                $query->where($this->table . '.projectId', $data['projectId'])
                    ->whereIn($this->table . '.status', [1, 2])
                    ->where($this->table . '.createTime', '>', $this->startTime)
                    ->where($this->table . '.createTime', '<', $this->endTime);;
                if (isset($data['professionId']) && !is_null($data['professionId'])) {
                    $query->where($this->table . '.professionId', $data['professionId']);
                }
                if (isset($data['search']) && !is_null($data['search'])) {
                    $query->where($this->table . '.name', 'like', '%' . $data['search'] . '%');
                }
            })
            ->count();
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
    public function update(array $data)
    {
        $updateData = [
            'name' => $data['name'],
            'professionId' => $data['professionId'],
        ];
        return DB::table($this->table)->where('id', $data['id'])->update($updateData);
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
     * @param $data
     * @return mixed
     */
    public function updateIsLeader($data)
    {
        return DB::table($this->table)->where('id', $data['groupId'])->update(['groupLeader' => $data['groupLeader']]);
    }

    /**
     * @param array $check
     * @param $id
     * @return array
     */
    public function checkRepeat(array $check, $id = 0)
    {
        $result = DB::table($this->table)->where($check)
            ->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->where('id', '!=', $id);
                }
            })
            ->first();
        return empty($result) ? [] : get_object_vars($result);
    }

    /**
     * @param array $data
     * @param $string
     * @return mixed
     */
    public static function getValue(array $data, $string)
    {
        return DB::table(self::TABLE)->where($data)->value($string);
    }

    /**
     * @param array $input
     * @return bool|string
     */
    public function delete(array $input)
    {
        //查看班组是否有分账
        $info = (new ProjectGroupSeparateAccountsModel())->info(['groupId' => $input['id']]);
        if (empty($info)) {
            return 'separateAccounts';
        }
        //查看班组是否有施工项
        $info = (new ProjectGroupAssignmentModel())->info(['groupId' => $input['id']]);
        if (empty($info)) {
            return 'assignment';
        }
        //查看班组下是否有成员
        $info = (new ProjectGroupAssignmentModel())->info(['groupId' => $input['id']]);
        if (empty($info)) {
            return 'members';
        }

        DB::table($this->table)->where($input)->delete();
        return true;
    }

    public function changeProject(array $input)
    {
        try {
            DB::transaction(function () use ($input) {
                //停用小组
                $this->updateStatus(['id'=>$input['id'],'status'=>2]);

                //组员调动至另一个项目
                $projectGroupMembersModel  = new ProjectGroupMembersModel();
                $memberLists = $projectGroupMembersModel->lists(['groupId'=>$input['id']]);
                $ids = [];
                foreach ($memberLists as $l){
                    array_push($ids,$l->employeeId);
                }

                //验证所有选中的工人是否是在岗状态
                $option = true;
                $employeeModel = new EmployeeModel();
                foreach ($ids as $id) {
                    $info = $employeeModel->info(['id' => $id]);
                    if ($info['status'] != 1 && $info['status'] != 2) {
                        $option = false;
                    }
                }
                if ($option) {
                    $employeeModel->batchUpdateProject('',['ids'=>$ids,'projectId'=>$input['projectId']],1);
                } else{
                    throw new \Exception('所选工人中有离职或请假状态的工人');
                }

                //在新项目建立同名班组
                $info = $this->info(['id'=>$input['id']]);
                $info = $this->checkRepeat(['projectId'=>$info['projectId'],'name'=>$info['name'],'professionId'=>$info['professionId']]);
                $insertData = ['projectId'=>$input['projectId'],'name'=>$info['name'],'professionId'=>$info['professionId']];
                if (!empty($info)){
                    $insertData['name'] = $info['name'].date('ymd');
                }
                $newGroupId = $this->insert($insertData);
                //班组下导入班组成员
                foreach ($ids as $id){
                    $data = ['projectId'=>$input['projectId'],'employeeId'=>$id,'groupId'=>$newGroupId];
                    $result = $projectGroupMembersModel->isInGroup($data);
                    if (!$result){
                        $info = $projectGroupMembersModel->info($data);
                        if (empty($info)){
                            $projectGroupMembersModel->insert($data);
                        }else{
                            if ($info['isDel'] == 1){
                                $projectGroupMembersModel->update(['id'=>$info['id']],['isDel'=>0]);
                            }
                        }
                        $employeeModel = new EmployeeModel();
                        $employeeModel->update($data['employeeId'],['groupId'=>$data['groupId']]);
                    }else{
                        throw new \Exception('工人已经属于班组'.$result['name']);
                    }
                }
            });
        } catch (\Exception $e) {

        }
    }
}