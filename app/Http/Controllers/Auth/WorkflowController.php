<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/22
 * Time: 9:19
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Model\WorkflowModel;
use App\Http\Model\WorkflowNodeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkflowController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function lists()
    {
        $workflowModel = new WorkflowModel();
        $this->data = $workflowModel->lists();
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function info(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取审批流程参数失败',
            'id.integer' => '审批流程参数类型错误',
        ];
        $input = $request->only('id');
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $workflowModel = new WorkflowModel();
            $this->data = $workflowModel->info($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 150101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 150102;
                    $this->msg = $validator->errors()->first();
                }
            }
        }

        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * 审批流程添加，不对用户开放使用
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        $rules = [
            'name' => 'required',
        ];
        $message = [
            'name.required' => '获取审批流程名称失败',
        ];
        $input = $request->only(['name', 'remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $workflowModel = new WorkflowModel();
            $result = $workflowModel->insert($input);
            if (!$result) {
                $this->code = 150202;
                $this->msg = '保存失败，请稍后重试';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 150201;
                    $this->msg = $validator->errors()->first();
                }
            }
        }

        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * 审批流程编辑
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'name' => 'required',
        ];
        $message = [
            'id.required' => '获取审批参数失败',
            'id.integer' => '审批参数类型错误',
            'name.required' => '获取审批流程参数失败',
        ];
        $input = $request->only(['id', 'name', 'remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $workflowModel = new WorkflowModel();
            $workflowModel->update($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 150301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 150302;
                    $this->msg = $validator->errors()->first();
                }
            }
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 150303;
                    $this->msg = $validator->errors()->first();
                }
            }
        }

        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * 审批流程状态修改
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function editStatus(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'status' => 'required|integer|in:0,1',
        ];
        $message = [
            'id.required' => '获取审批参数失败',
            'id.integer' => '审批参数类型错误',
            'status.required' => '未获取到审批状态',
            'status.integer' => '审批状态参数类型错误',
            'status.in' => '审批状态参数值不正确',
        ];
        $input = $request->only(['id', 'status']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $workflowModel = new WorkflowModel();
            $workflowModel->updateStatus($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 150401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 150402;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Required') {
                    $this->code = 150401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'Integer') {
                    $this->code = 150402;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'In') {
                    $this->code = 150405;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * 无分支审批流程节点列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function nodeLists(Request $request)
    {
        $rules = [
            'workflowId' => 'required|integer',
        ];
        $message = [
            'workflowId.required' => '获取审批参数失败',
            'workflowId.integer' => '审批参数类型错误',
        ];
        $input = $request->only(['search', 'workflowId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $workflowNodeModel = new WorkflowNodeModel();
            $this->data = $workflowNodeModel->showLists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'workflowId') {
                if (key($failed['workflowId']) == 'Required') {
                    $this->code = 150501;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['workflowId']) == 'Integer') {
                    $this->code = 150502;
                    $this->msg = $validator->errors()->first();
                }
            }
        }

        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * 添加无分支审批流程节点
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function nodeAdd(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'workflowId' => 'required|integer',
            'handler' => 'required|integer',
//            'order' => 'required|integer|min:0',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'workflowId.required' => '获取审批参数失败',
            'workflowId.integer' => '审批参数类型错误',
            'handler.required' => '获取审批人参数失败',
            'handler.integer' => '审批人参数类型错误',
//            'order.required' => '获取审批节点参数失败',
//            'order.integer' => '审批节点参数类型错误',
//            'order.min' => '审批节点参数数据不正确',
        ];
        $input = $request->only(['data']);
        $continue = true;
        foreach ($input['data'] as $d) {
            $validator = Validator::make($d, $rules, $message);
            if ($validator->fails()) {
                $continue = false;
                $failed = $validator->failed();
                if (key($failed) == 'projectId') {
                    if (key($failed['projectId']) == 'Required') {
                        $this->code = 150601;
                        $this->msg = $validator->errors()->first();
                        break;
                    }
                    if (key($failed['projectId']) == 'Integer') {
                        $this->code = 150602;
                        $this->msg = $validator->errors()->first();
                        break;
                    }
                } elseif (key($failed) == 'workflowId') {
                    if (key($failed['workflowId']) == 'Required') {
                        $this->code = 150603;
                        $this->msg = $validator->errors()->first();
                        break;
                    }
                    if (key($failed['workflowId']) == 'Integer') {
                        $this->code = 150604;
                        $this->msg = $validator->errors()->first();
                        break;
                    }
                } elseif (key($failed) == 'handler') {
                    if (key($failed['handler']) == 'Required') {
                        $this->code = 150605;
                        $this->msg = $validator->errors()->first();
                        break;
                    }
                    if (key($failed['handler']) == 'Integer') {
                        $this->code = 150606;
                        $this->msg = $validator->errors()->first();
                        break;
                    }
                }
//                if (key($failed) == 'order') {
//                    if (key($failed['order']) == 'Required') {
//                        $this->code = 150607;
//                        $this->msg = $validator->errors()->first();
//                        break;
//                    }
//                    if (key($failed['order']) == 'Integer') {
//                        $this->code = 150608;
//                        $this->msg = $validator->errors()->first();
//                        break;
//                    }
//                    if (key($failed['order']) == 'Min') {
//                        $this->code = 150609;
//                        $this->msg = $validator->errors()->first();
//                        break;
//                    }
//                }
            }
        }

        if ($continue) {
            $workflowNodeModel = new WorkflowNodeModel();
            $result = $workflowNodeModel->insert($input['data']);
            if (!$result) {
                $this->code = 150612;
                $this->msg = '保存失败，请稍后重试';
            }

        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function nodeDel(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'workflowId' => 'required|integer',
            'handlerList' => 'required',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'workflowId.required' => '获取流程参数失败',
            'workflowId.integer' => '流程参数类型错误',
            'handlerList.required' => '获取节点参数失败',
        ];
        $input = $request->only(['projectId', 'workflowId', 'handlerList']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $workflowNodeModel = new WorkflowNodeModel();
            $workflowNodeModel->delete($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 150701;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 150702;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'workflowId') {
                if (key($failed['workflowId']) == 'Required') {
                    $this->code = 150703;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['workflowId']) == 'Integer') {
                    $this->code = 150704;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'handlerList') {
                if (key($failed['handlerList']) == 'Required') {
                    $this->code = 150705;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}