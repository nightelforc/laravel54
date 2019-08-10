<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Auth\ApprovalController;
use App\Http\Controllers\Controller;
use App\Http\Model\EmployeeAttendanceModel;
use App\Http\Model\EmployeeLeaveModel;
use App\Http\Model\EmployeeLivingModel;
use App\Http\Model\EmployeeLoanModel;
use App\Http\Model\EmployeeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $rules = [
            'search' => 'required',
            'projectId' => 'required|integer'
        ];
        $message = [
            'search.required' => '请填写工人姓名或工号',
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
        ];
        $input = $request->only(['search', 'projectId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeModel = new EmployeeModel();
            $this->data = $employeeModel->searchEmployee($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'search') {
                if (key($failed['search']) == 'Required') {
                    $this->code = 410901;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 410902;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 410903;
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
    public function lists(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'professionId' => 'nullable|integer',
            'status' => 'nullable|integer',
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'professionId.integer' => '工种参数类型错误',
            'status.integer' => '工作状态参数类型错误',
            'length.required' => '获取记录条数失败',
            'length.integer' => '记录条数参数类型错误',
            'length.in' => '记录条数参数值不正确',
            'start.required' => '获取起始记录位置失败',
            'start.integer' => '页码参数类型错误',
            'start.min' => '页码参数值不小于:min',
        ];
        $input = $request->all();
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeModel = new EmployeeModel();
            $lists = $employeeModel->lists($input);
            $this->data = [
                "draw"=>$input['draw'],
                "data"=>$lists,
                "recordsFiltered"=>count($lists),
                "recordsTotal"=>count($lists),
            ];
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 410101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 410102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 410103;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Integer') {
                    $this->code = 410104;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 410105;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 410106;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 410107;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 410108;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 410109;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 410110;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 410111;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 410112;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function info(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取工人参数失败',
            'id.integer' => '工人参数类型错误',
        ];
        $input = $request->only('id');
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeModel = new EmployeeModel();
            $this->data = $employeeModel->employeeInfo($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 410201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 410202;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * 更改工人工作状态
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function batchChangeStatus(Request $request)
    {
        $rules = [
            'ids' => 'required|array',
            'status' => 'required|integer|in:1,2,3,4'
        ];
        $message = [
            'ids.required' => '获取工人参数组失败',
            'ids.array' => '工人参数类型错误',
            'status.required' => '请选择工作状态',
            'status.integer' => '工作状态参数类型错误',
            'status.in' => '工作状态参数值不正确'
        ];
        $input = $request->only(['ids', 'status']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            //审批流程
            $approval = ApprovalController::approval('batchChangeStatus', $input);
            if ($approval['status']) {
                if ($approval['result']) {
                    $this->msg = '申请提交成功，请等待审批结果';
                } else {
                    $this->code = 410306;
                    $this->msg = '保存失败，请稍后重试';
                }
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'ids') {
                if (key($failed['ids']) == 'Required') {
                    $this->code = 410301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['ids']) == 'Array') {
                    $this->code = 410302;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Required') {
                    $this->code = 410303;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'Integer') {
                    $this->code = 410304;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'In') {
                    $this->code = 410305;
                    $this->msg = $validator->errors()->first();
                }
            }
        }

        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * 工人项目调配
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function batchChangeProject(Request $request)
    {
        $rules = [
            'ids' => 'required|array',
            'projectId' => 'required|integer'
        ];
        $message = [
            'ids.required' => '获取工人参数组失败',
            'ids.array' => '工人参数类型错误',
            'projectId.required' => '请选择工作状态',
            'projectId.integer' => '工作状态参数类型错误',
        ];
        $input = $request->only(['ids', 'projectId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeModel = new EmployeeModel();
            //验证所有选中的工人是否是在岗状态
            $option = true;
            foreach ($input['ids'] as $id) {
                $info = $employeeModel->info(['id' => $id]);
                if ($info['status'] != 1) {
                    $option = false;
                }
            }
            if ($option) {
                //审批流程
                $approval = ApprovalController::approval('batchChangeProject', $input);
                if ($approval['status']) {
                    if ($approval['result']) {
                        $this->msg = '申请提交成功，请等待审批结果';
                    } else {
                        $this->code = 410406;
                        $this->msg = '保存失败，请稍后重试';
                    }
                }
            } else {
                $this->code = 410405;
                $this->msg = '请选择在岗员工';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'ids') {
                if (key($failed['ids']) == 'Required') {
                    $this->code = 410401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['ids']) == 'Array') {
                    $this->code = 410402;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Required') {
                    $this->code = 410403;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'Integer') {
                    $this->code = 410404;
                    $this->msg = $validator->errors()->first();
                }
            }
        }

        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * 增加工人的借款记录
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function addLoan(Request $request)
    {
        $rules = [
            'employeeId' => 'required|integer',
            'account' => 'required|numeric',
            'loanTime' => 'required|date_format:Y-m-d',
        ];
        $message = [
            'employeeId.required' => '获取工人参数失败',
            'employeeId.integer' => '工人参数类型错误',
            'account.required' => '请填写借款金额',
            'account.numeric' => '借款金额类型错误',
            'loanTime.required' => '请选择借款时间',
            'loanTime.date_format' => '借款时间格式不正确',
        ];
        $input = $request->only(['employeeId', 'account', 'loanTime']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            //追加工人当前的projectId
            $employeeModel = new EmployeeModel();
            $info = $employeeModel->info(['id' => $input['employeeId']]);
            $input['projectId'] = $info['projectId'];
            $employeeLoanModel = new EmployeeLoanModel();
            $insertId = $employeeLoanModel->insert($input);
            if ($insertId){
                $input['id'] = $insertId;
                $approval = ApprovalController::approval('addLoan', $input);
                if ($approval['status']) {
                    if ($approval['result']) {
                        $this->msg = '申请提交成功，请等待审批结果';
                    } else {
                        $this->code = 410508;
                        $this->msg = '保存失败，请稍后重试';
                    }
                }
            }else{
                $this->code = 410507;
                $this->msg = '保存失败，请稍后重试';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'employeeId') {
                if (key($failed['employeeId']) == 'Required') {
                    $this->code = 410501;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['employeeId']) == 'Integer') {
                    $this->code = 410502;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'account') {
                if (key($failed['account']) == 'Required') {
                    $this->code = 410503;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['account']) == 'Numeric') {
                    $this->code = 410504;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'loanTime') {
                if (key($failed['loanTime']) == 'Required') {
                    $this->code = 410505;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['loanTime']) == 'DateFormat') {
                    $this->code = 410506;
                    $this->msg = $validator->errors()->first();
                }
            }
        }

        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * 增加工人的生活费记录
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function addLiving(Request $request)
    {
        $rules = [
            'employeeId' => 'required|integer',
            'account' => 'required|numeric',
            'type' => 'required|integer|in:1,2',
            'livingTime' => 'required|date_format:Y-m-d',
        ];
        $message = [
            'employeeId.required' => '获取工人参数失败',
            'employeeId.integer' => '工人参数类型错误',
            'account.required' => '请填写生活费金额',
            'account.numeric' => '生活费类型错误',
            'type.required' => '请选择充值或退款',
            'type.integer' => '生活费操作类型错误',
            'type.in' => '生活费操作类型不正确',
            'livingTime.required' => '请选择借款时间',
            'livingTime.date_format' => '借款时间格式不正确',
        ];
        $input = $request->only(['employeeId', 'account', 'type', 'livingTime']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            //追加工人当前的projectId
            $employeeModel = new EmployeeModel();
            $info = $employeeModel->info(['id' => $input['employeeId']]);
            $input['projectId'] = $info['projectId'];
            $employeeLivingModel = new EmployeeLivingModel();
            $insertId = $employeeLivingModel->insert($input);
            if ($insertId){
                $input['id'] = $insertId;
                //审批流程
                $approval = ApprovalController::approval('addLiving', $input);
                if ($approval['status']) {
                    if ($approval['result']) {
                        $this->msg = '申请提交成功，请等待审批结果';
                    } else {
                        $this->code = 410611;
                        $this->msg = '保存失败，请稍后重试';
                    }
                }
            }else{
                $this->code = 410610;
                $this->msg = '保存失败，请稍后重试';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'employeeId') {
                if (key($failed['employeeId']) == 'Required') {
                    $this->code = 410601;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['employeeId']) == 'Integer') {
                    $this->code = 410602;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'account') {
                if (key($failed['account']) == 'Required') {
                    $this->code = 410603;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['account']) == 'Numeric') {
                    $this->code = 410604;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'type') {
                if (key($failed['type']) == 'Required') {
                    $this->code = 410605;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['type']) == 'Integer') {
                    $this->code = 410606;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['type']) == 'In') {
                    $this->code = 410607;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'livingTime') {
                if (key($failed['livingTime']) == 'Required') {
                    $this->code = 410608;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['livingTime']) == 'DateFormat') {
                    $this->code = 410609;
                    $this->msg = $validator->errors()->first();
                }
            }
        }

        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function attendanceList(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'month' => 'nullable|date_format:Y-m',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'month.date_format' => '月份参数格式不正确',
        ];
        $input = $request->only(['projectId', 'month']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeModel = new EmployeeModel();
            $this->data = $employeeModel->attendanceList($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 410701;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 410702;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'month') {
                if (key($failed['month']) == 'DateFormat') {
                    $this->code = 410703;
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
    public function supplement(Request $request)
    {
        $rules = [
            'employeeId' => 'required|integer',
            'day' => 'required|date_format:Y-m-d',
            'length' => 'required|numeric'
        ];
        $message = [
            'employeeId.required' => '获取工人参数失败',
            'employeeId.integer' => '工人参数类型错误',
            'day.required' => '请选择考勤日期',
            'day.date_format' => '考勤日期格式不正确',
            'length.required' => '请填写工作时长',
            'length.numeric' => '工作时长类型错误',
        ];
        $input = $request->only(['employeeId', 'day', 'length']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            //追加工人当前的projectId
            $employeeModel = new EmployeeModel();
            $info = $employeeModel->info(['id' => $input['employeeId']]);
            $input['projectId'] = $info['projectId'];
            $employeeAttendanceModel = new EmployeeAttendanceModel();
            $employeeAttendanceModel->insert($input);
            $employeeModel->hasAttendance(['id' => $input['employeeId']]);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'employeeId') {
                if (key($failed['employeeId']) == 'Required') {
                    $this->code = 410801;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['employeeId']) == 'Integer') {
                    $this->code = 410802;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'day') {
                if (key($failed['day']) == 'Required') {
                    $this->code = 410803;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['day']) == 'DateFormat') {
                    $this->code = 410804;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 410805;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Numeric') {
                    $this->code = 410806;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * 保存考勤
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function addAttendance(Request $request)
    {
        $rules = [
            'employeeId' => 'required|integer',
            'projectId' => 'required|integer',
            'day' => 'required|date_format:Y-m-d',
            'length' => 'required|numeric'
        ];
        $message = [
            'employeeId.required' => '获取工人参数失败',
            'employeeId.integer' => '工人参数类型错误',
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'day.required' => '请选择考勤日期',
            'day.date_format' => '考勤日期格式不正确',
            'length.required' => '请填写工作时长',
            'length.numeric' => '工作时长类型错误',
        ];
        $data = $request->only(['data']);
        $return = false;
        foreach ($data['data'] as $key => $d) {
            $validator = Validator::make($d, $rules, $message);
            if ($validator->fails()) {
                $failed = $validator->failed();
                if (key($failed) == 'employeeId') {
                    if (key($failed['employeeId']) == 'Required') {
                        $this->code = 410901;
                        $this->msg = '第'.$key.'条'.$validator->errors()->first();
                    }
                    if (key($failed['employeeId']) == 'Integer') {
                        $this->code = 410902;
                        $this->msg = $validator->errors()->first();
                    }
                } elseif (key($failed) == 'projectId') {
                    if (key($failed['projectId']) == 'Required') {
                        $this->code = 410903;
                        $this->msg = $validator->errors()->first();
                    }
                    if (key($failed['projectId']) == 'Integer') {
                        $this->code = 410904;
                        $this->msg = $validator->errors()->first();
                    }
                } elseif (key($failed) == 'day') {
                    if (key($failed['day']) == 'Required') {
                        $this->code = 410905;
                        $this->msg = $validator->errors()->first();
                    }
                    if (key($failed['day']) == 'DateFormat') {
                        $this->code = 410906;
                        $this->msg = $validator->errors()->first();
                    }
                } elseif (key($failed) == 'length') {
                    if (key($failed['length']) == 'Required') {
                        $this->code = 410907;
                        $this->msg = $validator->errors()->first();
                    }
                    if (key($failed['length']) == 'Numeric') {
                        $this->code = 410908;
                        $this->msg = $validator->errors()->first();
                    }
                }
                $return = true;
                break;
            }
        }

        if (!$return) {
            $employeeAttendanceModel = new EmployeeAttendanceModel();
            $result = $employeeAttendanceModel->insert($data);
            if (!$result) {
                $this->code = 410909;
                $this->msg = '保存失败，请稍后重试';
            }
        }

        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function leaveLists(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'professionId' => 'nullable|integer',
            'backStatus' => 'nullable|integer|in:0,1',
            'status' => 'nullable|integer|in:0,1,2',
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'professionId.integer' => '工种参数类型错误',
            'backStatus.integer' => '销假状态参数类型错误',
            'backStatus.in' => '销假状态参数不正确',
            'status.integer' => '审批状态参数类型错误',
            'status.in' => '审批状态参数不正确',
            'length.required' => '获取记录条数失败',
            'length.integer' => '记录条数参数类型错误',
            'length.in' => '记录条数参数值不正确',
            'start.required' => '获取起始记录位置失败',
            'start.integer' => '页码参数类型错误',
            'start.min' => '页码参数值不小于:min',
        ];
        $input = $request->only(['projectId', 'professionId', 'backStatus', 'status', 'search','draw','length','start']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeLeaveModel = new  EmployeeLeaveModel();
            $lists = $employeeLeaveModel->lists($input);
            $this->data = [
                "draw"=>$input['draw'],
                "data"=>$lists,
                "recordsFiltered"=>count($lists),
                "recordsTotal"=>count($lists),
            ];
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 411001;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 411002;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 411003;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'backStatus') {
                if (key($failed['backStatus']) == 'Integer') {
                    $this->code = 411004;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['backStatus']) == 'In') {
                    $this->code = 411005;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Integer') {
                    $this->code = 411006;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'In') {
                    $this->code = 411007;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 411008;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 411009;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 411010;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 411011;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 411012;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 411013;
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
    public function back(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'backTime' => 'required|date_format:Y-m-d',
        ];
        $message = [
            'id.required' => '获取请假参数失败',
            'id.integer' => '请假参数类型错误',
            'backTime.required' => '获取销假时间失败',
            'backTime.date_format' => '销假时间格式不正确',
        ];
        $input = $request->only(['id', 'backTime']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeLeaveModel = new EmployeeLeaveModel();
            $employeeLeaveModel->back($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 411101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 411102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'backTime') {
                if (key($failed['backTime']) == 'Required') {
                    $this->code = 411103;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['backTime']) == 'DateFormat') {
                    $this->code = 411104;
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
    public function addLeave(Request $request)
    {
        $rules = [
            'employeeId' => 'required|integer',
            'projectId' => 'required|integer',
            'preLeaveTime' => 'required|date_format:Y-m-d',
            'preBackTime' => 'required|date_format:Y-m-d|after:preLeaveTime',
        ];
        $message = [
            'employeeId.required' => '获取工人参数失败',
            'employeeId.integer' => '工人参数类型错误',
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'preLeaveTime.required' => '获取预计请假时间失败',
            'preLeaveTime.date_format' => '预计请假时间格式不正确',
            'preBackTime.required' => '获取预计销假时间失败',
            'preBackTime.date_format' => '预计销假时间格式不正确',
            'preBackTime.after' => '预计销假时间必须晚于预计请假时间',
        ];
        $input = $request->only(['employeeId', 'projectId', 'preLeaveTime', 'preBackTime', 'remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeLeaveModel = new  EmployeeLeaveModel();
            $insertId = $employeeLeaveModel->insert($input);
            if ($insertId) {
                $input['id'] = $insertId;
                $approval = ApprovalController::approval('addLeave', $input);
                if ($approval['status']) {
                    if ($approval['result']) {
                        $this->msg = '申请提交成功，请等待审批结果';
                    } else {
                        $this->code = 411211;
                        $this->msg = '保存失败，请稍后重试';
                    }
                }
            }else{
                $this->code = 411210;
                $this->msg = '保存失败，请稍后重试';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'employeeId') {
                if (key($failed['employeeId']) == 'Required') {
                    $this->code = 411201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['employeeId']) == 'Integer') {
                    $this->code = 411202;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 411203;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 411204;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'preLeaveTime') {
                if (key($failed['preLeaveTime']) == 'Required') {
                    $this->code = 411205;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['preLeaveTime']) == 'DateFormat') {
                    $this->code = 411206;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'preBackTime') {
                if (key($failed['preBackTime']) == 'Required') {
                    $this->code = 411207;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['preBackTime']) == 'DateFormat') {
                    $this->code = 411208;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['preBackTime']) == 'After') {
                    $this->code = 411209;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}