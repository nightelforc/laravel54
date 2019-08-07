<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/22
 * Time: 9:18
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        $callBackClass = $info['className'];
        $callBackMethod = $info['methodName'];
        if (!$info['status']) {
            $approval['status'] = false;
            self::afterApproval($callBackClass, $callBackMethod, $pk, $data,1);
            return $approval;
        }
        $session = session(parent::pasn);
        $workflowNodeModel = new WorkflowNodeModel();
        //加载审批流程当前全流程节点
        $processList = $workflowNodeModel->handlerLists(['workflowId' => $info['id'], 'projectId' => $session['projectId']]);

        $approvalData = [
            'workflowId' => $info['id'],
            'projectId' => $session['projectId'],
            'adminId' => $session['id'],
            'joinTime' => date('Y-m-d H:i:s'),
            'curnode' => $processList[0],
            'process' => json_encode($processList),
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
        $input = $request->only(['projectId', 'startTime','endTime','status','draw', 'length', 'start']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $WorkflowItemModel = new WorkflowItemModel();
            $input['curnode'] = $request->session()->get(parent::pasn)['id'];
            $this->data = $WorkflowItemModel->lists($input);
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
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $WorkflowItemModel = new WorkflowItemModel();
            $adminId = $request->session()->get(parent::pasn)['id'];
            $result = $WorkflowItemModel->accept($input,$adminId);
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
        $input = $request->only(['id','remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $WorkflowItemModel = new WorkflowItemModel();
            $adminId = $request->session()->get(parent::pasn)['id'];
            $result = $WorkflowItemModel->reject($input,$adminId);
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
}