<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/22
 * Time: 9:22
 */

namespace App\Http\Model;


use App\Http\Controllers\Auth\ApprovalController;
use Illuminate\Support\Facades\DB;

class WorkflowItemModel extends Model
{
    const PENDING_APPROVAL = 0;
    const PROCESSING = 1;
    const ACCEPT = 2;
    const REJECT = 3;
    const WITHDRAW = 4;
    private $table = 'workflow_item';

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        return DB::table($this->table)->insertGetId($data);
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

    /**
     * @param $pk
     * @param $data
     * @return mixed
     */
    public function update($pk,$data){
        return DB::table($this->table)->where('id',$pk)->update($data);
    }

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
            ->leftJoin('workflow as wf','wf.id','=',$this->table.'.workflowId')
            ->leftJoin('project as p','p.id','=',$this->table.'.projectId')
            ->leftJoin('admin as a','a.id','=',$this->table.'.adminId')
            ->where(function ($query) use ($input){
                $query->where($this->table.'.curnode',$input['curnode']);
                if (isset($input['projectId']) && !empty($input['projectId'])){
                    $query->where($this->table.'.projectId',$input['projectId']);
                }
                if (isset($input['startTime']) && !is_null($input['startTime'])){
                    $query->where('joinTime','>=',$input['startTime'].' 00:00:00');
                }
                if (isset($input['endTime']) && !is_null($input['endTime'])){
                    $query->where('joinTime','<=',$input['endTime'].' 23:59:59');
                }
                if (isset($input['status']) && !is_null($input['status'])) {
                    $query->where($this->table . '.status', $input['status']);
                }
            })
            ->offset($start)->limit($limit)
            ->select($this->table . '.*','wf.name as workflowName','p.name as projectName','a.name as adminName')
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @param $adminId
     * @return bool
     */
    public function accept(array $data, $adminId)
    {
        return $this->handle($data, $adminId,1);
    }

    /**
     * @param array $data
     * @param $adminId
     * @return bool
     */
    public function reject(array $data, $adminId)
    {
        return $this->handle($data, $adminId,1);
    }

    /**
     * @param array $data
     * @param $adminId
     * @param $result
     * @return bool
     */
    public function handle(array $data, $adminId,$result){
        try {
            $id = $data['id'];
            $workflowItemInfo = $this->info(['id' => $id]);
            //记录审批进程的审批结果
            $workflowItemProcessModel = new WorkflowItemProcessModel();
            $workflowItemProcessModel->updateProcess(['itemId' => $id, 'adminId' => $adminId], ['updateTime' => date('Y-m-d H:i:s'), 'status' => $result, 'remark' => $data['remark']]);
            //更新审批的状态
            if ($result == 1) {
                $process = json_decode($workflowItemInfo['process'], true);
                //判断是否完成审批
                $node = array_search($workflowItemInfo['curnode'], $process);
                if ($node == (count($process) - 1)) {
                    $this->update($id, ['curnode' => 0, 'status' => self::ACCEPT]);
                    ApprovalController::afterApproval($workflowItemInfo['callBackClass'], $workflowItemInfo['callBackMethod'], $workflowItemInfo['pk'], $workflowItemInfo['data'], 1);
                } else {
                    $this->update($id, ['curnode' => $process[$node + 1], 'status' => self::PROCESSING]);
                }
            } else {
                //更新审批状态
                $this->update($id, ['curnode' => 0, 'status' => self::REJECT]);
                ApprovalController::afterApproval($workflowItemInfo['callBackClass'], $workflowItemInfo['callBackMethod'], $workflowItemInfo['pk'], $workflowItemInfo['data'], 2);
            }
            return true;
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function countLists(array $input)
    {
        return DB::table($this->table)
            ->leftJoin('workflow as wf','wf.id','=',$this->table.'.workflowId')
            ->leftJoin('project as p','p.id','=',$this->table.'.projectId')
            ->leftJoin('admin as a','a.id','=',$this->table.'.adminId')
            ->where(function ($query) use ($input){
                $query->where($this->table.'.curnode',$input['curnode']);
                if (isset($input['projectId']) && !empty($input['projectId'])){
                    $query->where($this->table.'.projectId',$input['projectId']);
                }
                if (isset($input['startTime']) && !is_null($input['startTime'])){
                    $query->where('joinTime','>=',$input['startTime'].' 00:00:00');
                }
                if (isset($input['endTime']) && !is_null($input['endTime'])){
                    $query->where('joinTime','<=',$input['endTime'].' 23:59:59');
                }
                if (isset($input['status']) && !is_null($input['status'])) {
                    $query->where($this->table . '.status', $input['status']);
                }
            })
            ->count();
    }
}