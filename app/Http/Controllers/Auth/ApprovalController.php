<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/22
 * Time: 9:18
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Model\AdminSessionModel;
use App\Http\Model\AssignmentModel;
use App\Http\Model\EmployeeModel;
use App\Http\Model\MaterialModel;
use App\Http\Model\MaterialSpecModel;
use App\Http\Model\ProfessionModel;
use App\Http\Model\ProjectAreaModel;
use App\Http\Model\ProjectGroupModel;
use App\Http\Model\ProjectModel;
use App\Http\Model\ProjectSectionModel;
use App\Http\Model\SupplierModel;
use App\Http\Model\WorkflowItemModel;
use App\Http\Model\WorkflowItemProcessModel;
use App\Http\Model\WorkflowModel;
use App\Http\Model\WorkflowNodeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApprovalController extends Controller
{
    /**
     * @param $code
     * @param $data
     * @return array
     */
    public static function approval($code, $data)
    {
        $session = AdminSessionModel::get($data[parent::$token]);
        unset($data[parent::$token]);

        $approval = [
            'status' => true,
            'result' => false,
            'approvalId' => ''
        ];
        if (isset($data['id'])){
            $pk = $data['id'];
            unset($data['id']);
        }else{
            $pk = '';
        }

        $workflowModel = new WorkflowModel();
        $info = $workflowModel->info(['code' => $code]);

        $workflowNodeModel = new WorkflowNodeModel();
        //加载审批流程当前全流程节点
        $processList = $workflowNodeModel->handlerLists(['workflowId' => $info['id'], 'projectId' => $session['projectId']]);

        $callBackClass = $info['className'];
        $callBackMethod = $info['methodName'];

        //审批流程停用或审批流程节点为空，都不进行审批
        if (!$info['status'] || empty($processList)) {
            $approval['status'] = false;
            self::afterApproval($callBackClass, $callBackMethod, $pk, $data,1);
            return $approval;
        }

        $process = json_encode($processList);
        $approvalData = [
            'workflowId' => $info['id'],
            'projectId' => $session['projectId'],
            'adminId' => $session['adminId'],
            'joinTime' => date('Y-m-d H:i:s'),
            'curnode' => (isset($processList[0]) && !empty($processList[0]))?$processList[0]:0,
            'process' =>$process,
            'callBackClass' => $callBackClass,
            'callBackMethod' => $callBackMethod,
            'pk' => $pk,
            'data' => json_encode($data)
        ];

        $workflowItemModel = new WorkflowItemModel();
        $itemId = $workflowItemModel->insert($approvalData);
        $itemProcess = [];
        foreach ($processList as $p) {
            $itemProcess[] = ['itemId' => $itemId, 'adminId' => $p, 'createTime' => date('Y-m-d H:i:s')];
        }

        $workflowItemProcessModel = new WorkflowItemProcessModel();
        $result = $workflowItemProcessModel->insertArray($itemProcess);
        if ($result) {
            $approval['result'] = true;
            $approval['approvalId'] = $itemId;
        }

        return $approval;
    }

    /**
     * @param $callBackClass
     * @param $callBackMethod
     * @param $pk
     * @param $data
     * @param $approvalResult
     */
    public static function afterApproval($callBackClass, $callBackMethod, $pk, $data,$approvalResult)
    {
        $className ='\App\Http\Model\\'.$callBackClass;
        $newClass = new $className;
        call_user_func([$newClass,$callBackMethod],$pk,$data,$approvalResult);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request){
        $rules = [
            'projectId' => 'required|integer',
            'startTime' => 'nullable|date_format:Y-m-d',
            'endTime' => 'nullable|date_format:Y-m-d',
            'status' => 'nullable|integer|in:0,1,2,3,4',
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型不正确',
            'startTime.date_format' =>'日期格式不正确',
            'endTime.date_format' =>'日期格式不正确',
            'status.integer' => '付款状态参数类型错误',
            'status.in' => '付款状态参数值不正确',
            'length.required' => '获取记录条数失败',
            'length.integer' => '记录条数参数类型错误',
            'length.in' => '记录条数参数值不正确',
            'start.required' => '获取起始记录位置失败',
            'start.integer' => '页码参数类型错误',
            'start.min' => '页码参数值不小于:min',
        ];
        $input = $request->only(['projectId', 'startTime','endTime','status','draw', 'length', 'start',self::$token]);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $WorkflowItemModel = new WorkflowItemModel();
            $session = AdminSessionModel::get($input[self::$token]);
            $input['curnode'] = $session['adminId'];
            $lists = $WorkflowItemModel->lists($input);
            $lists = $this->descFormat($lists);
            $countLists = $WorkflowItemModel->countLists($input);
            $this->data = [
                "draw"=>$input['draw'],
                "data"=>$lists,
                "recordsFiltered"=>$countLists,
                "recordsTotal"=>$countLists,
            ];
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 160101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 160102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'startTime') {
                if (key($failed['startTime']) == 'DateFormat') {
                    $this->code = 160103;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'endTime') {
                if (key($failed['endTime']) == 'DateFormat') {
                    $this->code = 160104;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Integer') {
                    $this->code = 160105;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'In') {
                    $this->code = 160106;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 160107;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 160108;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Integer') {
                    $this->code = 160109;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 160110;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Integer') {
                    $this->code = 160111;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 160112;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function accept(Request $request){
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取审批参数失败',
            'id.integer' => '项目参数类型不正确',
        ];
        $input = $request->only(['id',self::$token]);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $WorkflowItemModel = new WorkflowItemModel();
            $session = AdminSessionModel::get($input[self::$token]);
            $result = $WorkflowItemModel->accept($input,$session['adminId']);
            if (!$result){
                $this->code = 160203;
                $this->msg = '审批发生错误';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 160201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 160202;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function reject(Request $request){
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取项目参数失败',
            'id.integer' => '项目参数类型不正确',
        ];
        $input = $request->only(['id','remark',self::$token]);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $WorkflowItemModel = new WorkflowItemModel();
            $session = AdminSessionModel::get($input[self::$token]);
            $result = $WorkflowItemModel->reject($input,$session['adminId']);
            if (!$result){
                $this->code = 160303;
                $this->msg = '审批发生错误';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 160301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 160302;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function myApprovalLists(Request $request){
        $rules = [
            'startTime' => 'nullable|date_format:Y-m-d',
            'endTime' => 'nullable|date_format:Y-m-d',
            'status' => 'nullable|integer|in:0,1,2,3,4',
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'startTime.date_format' =>'日期格式不正确',
            'endTime.date_format' =>'日期格式不正确',
            'status.integer' => '付款状态参数类型错误',
            'status.in' => '付款状态参数值不正确',
            'length.required' => '获取记录条数失败',
            'length.integer' => '记录条数参数类型错误',
            'length.in' => '记录条数参数值不正确',
            'start.required' => '获取起始记录位置失败',
            'start.integer' => '页码参数类型错误',
            'start.min' => '页码参数值不小于:min',
        ];
        $input = $request->only(['startTime','endTime','status','draw', 'length', 'start',self::$token]);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $WorkflowItemModel = new WorkflowItemModel();
            $session = AdminSessionModel::get($input[self::$token]);
            $input['adminId'] = $session['adminId'];
            $lists = $WorkflowItemModel->myApprovalLists($input);
            $lists = $this->descFormat($lists);
            $countLists = $WorkflowItemModel->myApprovalCountLists($input);
            $this->data = [
                "draw"=>$input['draw'],
                "data"=>$lists,
                "recordsFiltered"=>$countLists,
                "recordsTotal"=>$countLists,
            ];
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'startTime') {
                if (key($failed['startTime']) == 'DateFormat') {
                    $this->code = 160103;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'endTime') {
                if (key($failed['endTime']) == 'DateFormat') {
                    $this->code = 160104;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Integer') {
                    $this->code = 160105;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'In') {
                    $this->code = 160106;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 160107;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 160108;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 160109;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 160109;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 160110;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 160412;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 160412;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 160412;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param $lists
     * @return mixed
     */
    private function descFormat($lists)
    {
        foreach ($lists as $key => $l){
            $methodName = $l->code.'Desc';
            if(method_exists($this,$methodName)){
                $lists[$key]->desc = $this->$methodName($l->data);
            }else{
                $lists[$key]->desc = '';
            }
        }
        return $lists;
    }

    /**
     * @param $data
     * @return mixed|string
     */
    private function unitDesc($data){
        $data = json_decode($data,true);
        $model = <<<EOF
        申请增加新计量单位，单位名称 <b>$data[name]</b>，单位缩写 <b>$data[shortname]</b>
EOF;

        return $model;
    }

    /**
     * @param $data
     * @return string
     */
    private function addAssignmentDesc($data){
        $data = json_decode($data,true);
        $projectName = ProjectModel::getValue(['id'=>$data['projectId']],'name');
        $areaName = ProjectAreaModel::getValue(['id'=>$data['areaId']],'name');
        $sectionName = ProjectSectionModel::getValue(['id'=>$data['areaId']],'name');
        $professionName = ProfessionModel::getValue(['id'=>$data['professionId']],'name');
        $groupName = ProjectGroupModel::getValue(['id'=>$data['groupId']],'name');
        $model = <<<EOF
        项目 <b>$projectName</b>，施工区 <b>$areaName</b>，施工段 <b>$sectionName</b>，工种 <b>$professionName</b>，
        班组 <b>$groupName</b>
EOF;
        $tr = '';
        $i = 0;
        foreach($data['data'] as $key => $d){
            $i++;
            $assignmentName = AssignmentModel::getValue(['id'=>$d['assignmentId']],'name');
            $tr .= <<<EOF
<tr><th>$i</th><th>$assignmentName</th><th>$d[amount]</th><th>$d[price]</th><th>$d[totalPrice]</th></tr>
EOF;
        }
        $table = "<table><thead><tr><th></th><th>施工量</th><th>工程量</th><th>单价</th><th>总价</th></tr></thead>
            <tbody>$tr</tbody></table>";

        return $model.$table;
    }

    /**
     * @param $data
     * @return string
     */
    private function batchChangeStatusDesc($data){
        $data = json_decode($data,true);
        $model = '';
        foreach($data['ids'] as $key => $d){
            $employeeName = EmployeeModel::getValue(['id'=>$d],'name');
            $model .= $employeeName.' ，';
        }
        switch ($data['status']){
            case 1:
                $status = '恢复在岗状态';
                break;
            case 2:
                $status = '调整为待岗状态';
                break;
            case 3:
                $status = '调整为请假状态';
                break;
            case 4:
                $status = '调整为离职状态';
                break;
        }
        $model .= '申请'.$status;
        return $model;
    }

    public function consumeDesc($data){
        $data = json_decode($data,true);
        $model = '';
        $projectName = ProjectModel::getValue(['id'=>$data['projectId']],'name');
        $employeeName = EmployeeModel::getValue(['id'=>$data['sourceEmployeeId']],'name');
        $model = <<<EOF
        项目 <b>$projectName</b>，工人 <b>$employeeName</b>，在 <b>$data[time]</b>，购买总价 <b>$data[price]</b>元的物资材料。
EOF;
        $tr = '';
        $i = 0;
        foreach($data['data'] as $key => $d){
            $i++;
            $materialName = MaterialModel::getValue(['id'=>$d['materialId']],'name');
            $specName = MaterialSpecModel::getValue(['id'=>$d['specId']],'spec');
            $supplierName = SupplierModel::getValue(['id'=>$d['supplierId']],'name');
            $tr .= <<<EOF
<tr><th>$i</th><th>$materialName</th><th>$specName</th><th>$supplierName</th><th>$d[amount]</th><th>$d[price]</th><th>$d[totalPrice]</th></tr>
EOF;
        }
        $table = "<table><thead><tr><th></th><th>材料名称</th><th>规格</th><th>供应商</th><th>数量</th><th>单价</th><th>总价</th></tr></thead>
            <tbody>$tr</tbody></table>";

        return $model.$table;
    }
}