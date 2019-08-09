<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/6
 * Time: 10:44
 */

namespace App\Http\Controllers\Project;


use App\Http\Controllers\Controller;
use App\Http\Model\EmployeeLivingModel;
use App\Http\Model\EmployeeLoanModel;
use App\Http\Model\EmployeeModel;
use App\Http\Model\SupplierOrdersModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FinanceController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function wageLists(Request $request){
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
            foreach ($lists as $key => $l){
                $wages = $employeeModel->wages($l->id);
                $wagesTotal = [
                    'bonus' => 0,
                    'fine' => 0,
                    'living' => 0,
                    'loan' => 0,
                    'materialOrder' => 0,
                    'attendance' => 0,
                    'separateAccounts' => 0,
                    'otherSeparateAccounts' => 0,
                ];
                foreach ($wages as $w){
                    $wagesTotal['bonus'] += $w['bonus'];
                    $wagesTotal['fine'] += $w['fine'];
                    $wagesTotal['living'] += $w['living'];
                    $wagesTotal['loan'] += $w['loan'];
                    $wagesTotal['materialOrder'] += $w['materialOrder'];
                    $wagesTotal['attendance'] += $w['attendance'];
                    $wagesTotal['separateAccounts'] += $w['separateAccounts'];
                    $wagesTotal['otherSeparateAccounts'] += $w['otherSeparateAccounts'];
                }

                $lists[$key]->wage = $wagesTotal;
            }
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
                    $this->code = 470101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 470102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 470103;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Integer') {
                    $this->code = 470104;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 470105;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 470106;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 470107;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 470108;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 470109;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 470110;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 470111;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 470112;
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
    public function supplierOrder(Request $request){
        $rules = [
            'projectId' => 'required|integer',
            'time' => 'nullable|date_format:Y-m',
            'isPay' => 'nullable|integer|in:0,1',
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型不正确',
            'time.date_format' =>'月份格式不正确',
            'isPay.integer' => '付款状态参数类型错误',
            'isPay.in' => '付款状态参数值不正确',
            'length.required' => '获取记录条数失败',
            'length.integer' => '记录条数参数类型错误',
            'length.in' => '记录条数参数值不正确',
            'start.required' => '获取起始记录位置失败',
            'start.integer' => '页码参数类型错误',
            'start.min' => '页码参数值不小于:min',
        ];
        $input = $request->only(['projectId', 'isPay','draw', 'length', 'start', 'search','time']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $supplierOrdersModel = new SupplierOrdersModel();
            $lists = $supplierOrdersModel->lists($input);
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
                    $this->code = 470201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 470202;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'time') {
                if (key($failed['time']) == 'DateFormat') {
                    $this->code = 470203;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'isPay') {
                if (key($failed['isPay']) == 'Integer') {
                    $this->code = 470204;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['isPay']) == 'In') {
                    $this->code = 470205;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 470206;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 470207;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Integer') {
                    $this->code = 470208;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 470209;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Integer') {
                    $this->code = 470210;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 470211;
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
    public function loanLists(Request $request){
        $rules = [
            'projectId' => 'required|integer',
            'startTime' => 'nullable|date_format:Y-m-d',
            'endTime' => 'nullable|date_format:Y-m-d',
            'status' => 'nullable|integer|in:0,1',
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
        $input = $request->only(['projectId', 'startTime','endTime','status','draw', 'length', 'start', 'search']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeLoanModel = new EmployeeLoanModel();
            $lists = $employeeLoanModel->lists($input);
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
                    $this->code = 470301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 470302;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'startTime') {
                if (key($failed['startTime']) == 'DateFormat') {
                    $this->code = 470303;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'endTime') {
                if (key($failed['endTime']) == 'DateFormat') {
                    $this->code = 470304;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Integer') {
                    $this->code = 470305;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'In') {
                    $this->code = 470306;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 470307;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 470308;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Integer') {
                    $this->code = 470309;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 470310;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Integer') {
                    $this->code = 470311;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 470312;
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
    public function livingLists(Request $request){
        $rules = [
            'projectId' => 'required|integer',
            'startTime' => 'nullable|date_format:Y-m-d',
            'endTime' => 'nullable|date_format:Y-m-d',
            'status' => 'nullable|integer|in:0,1',
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
        $input = $request->only(['projectId', 'startTime','endTime','status','draw', 'length', 'start', 'search']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeLivingModel = new EmployeeLivingModel();
            $lists = $employeeLivingModel->lists($input);
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
                    $this->code = 470401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 470402;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'startTime') {
                if (key($failed['startTime']) == 'DateFormat') {
                    $this->code = 470403;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'endTime') {
                if (key($failed['endTime']) == 'DateFormat') {
                    $this->code = 470404;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Integer') {
                    $this->code = 470405;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'In') {
                    $this->code = 470406;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 470407;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 470408;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Integer') {
                    $this->code = 470409;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 470410;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Integer') {
                    $this->code = 470411;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 470412;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}