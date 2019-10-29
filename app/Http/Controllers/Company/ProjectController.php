<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/24
 * Time: 11:57
 */

namespace App\Http\Controllers\Company;


use App\Http\Controllers\Controller;
use App\Http\Model\ProjectAreaModel;
use App\Http\Model\ProjectBudgetModel;
use App\Http\Model\ProjectModel;
use App\Http\Model\ProjectSectionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request)
    {
        $rules = [
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
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
            $projectModel = new ProjectModel();
            $lists = $projectModel->lists($input);
            $countLists = $projectModel->countLists($input);
            $this->data = [
                "draw" => $input['draw'],
                "data" => $lists,
                "recordsFiltered" => $countLists,
                "recordsTotal" => $countLists,
            ];
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 320101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 320102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 320103;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 320104;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 320105;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 320106;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 320107;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 320108;
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
    public function add(Request $request)
    {
        $rules = [
            'name' => 'required',
            'city' => 'required',
            'projectAmount' => 'nullable|numeric',
            'projectAccount' => 'nullable|numeric',
            'order' => 'integer',
        ];
        $message = [
            'name.required' => '请填写项目名称',
            'city.required' => '请填写城市',
            'projectAmount.numeric' => '项目工程量类型错误',
            'projectAccount.numeric' => '项目工程款类型错误',
            'order.integer' => '排序参数类型错误',
        ];
        $input = $request->only(['name', 'city', 'projectAmount', 'projectAccount', 'order']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectModel = new ProjectModel();
            $result = $projectModel->insert($input);
            if (!$result) {
                $this->code = 320206;
                $this->msg = '保存失败，请稍后重试';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 320201;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'city') {
                if (key($failed['city']) == 'Required') {
                    $this->code = 320202;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'projectAmount') {
                if (key($failed['projectAmount']) == 'Numeric') {
                    $this->code = 320203;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'projectAccount') {
                if (key($failed['projectAccount']) == 'Numeric') {
                    $this->code = 320204;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'order') {
                if (key($failed['order']) == 'Integer') {
                    $this->code = 320205;
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
    public function info(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取项目参数失败',
            'id.integer' => '项目参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectModel = new ProjectModel();
            $this->data = $projectModel->info($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 320301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 320302;
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
    public function edit(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'name' => 'required',
            'city' => 'required',
            'projectAmount' => 'nullable|numeric',
            'projectAccount' => 'nullable|numeric',
            'order' => 'integer',
        ];
        $message = [
            'id.required' => '获取项目参数失败',
            'id.integer' => '项目参数类型错误',
            'name.required' => '请填写项目名称',
            'city.required' => '请填写城市',
            'projectAmount.numeric' => '项目工程量类型错误',
            'projectAccount.numeric' => '项目工程款类型错误',
            'order.integer' => '排序参数类型错误',
        ];
        $input = $request->only(['id', 'name', 'city', 'projectAmount', 'projectAccount', 'order']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectModel = new ProjectModel();
            $info = $projectModel->info(['id' => $input['id']]);
            if ($info['status'] == 2) {
                $this->code = 320408;
                $this->msg = '该项目已完工，项目信息不能修改';
            } else {
                $projectModel->update($input);
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 320401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 320402;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 320403;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'city') {
                if (key($failed['city']) == 'Required') {
                    $this->code = 320404;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'projectAmount') {
                if (key($failed['projectAmount']) == 'Numeric') {
                    $this->code = 320405;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'projectAccount') {
                if (key($failed['projectAccount']) == 'Numeric') {
                    $this->code = 320406;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'order') {
                if (key($failed['order']) == 'Integer') {
                    $this->code = 320407;
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
    public function editStatus(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
//            'status' => 'required|integer|in:2',
        ];
        $message = [
            'id.required' => '获取项目参数失败',
            'id.integer' => '项目参数类型错误',
//            'status.required' => '获取项目状态参数失败',
//            'status.integer' => '项目状态参数类型错误',
//            'status.in' => '项目状态参数不正确',
        ];
        $input = $request->only(['id', 'status']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectModel = new ProjectModel();
            $input['status'] = 2;
            $projectModel->updateStatus($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 320501;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 320502;
                    $this->msg = $validator->errors()->first();
                }
            }
//            elseif (key($failed) == 'status') {
//                if (key($failed['status']) == 'Required') {
//                    $this->code = 320503;
//                    $this->msg = $validator->errors()->first();
//                }
//                if (key($failed['status']) == 'Integer') {
//                    $this->code = 320504;
//                    $this->msg = $validator->errors()->first();
//                }
//                if (key($failed['status']) == 'In') {
//                    $this->code = 320505;
//                    $this->msg = $validator->errors()->first();
//                }
//            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function editBudget(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'areaId' => 'required|integer',
            'sectionId' => 'required|integer',
            'data' => 'required|array'
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'areaId.required' => '获取施工区参数失败',
            'areaId.integer' => '施工区参数类型错误',
            'sectionId.required' => '获取施工段参数失败',
            'sectionId.integer' => '施工段参数类型错误',
            'data.required' => '获取预算数据失败',
            'data.array' => '数据类型错误',
        ];
        $input = $request->only(['projectId', 'areaId', 'sectionId', 'data']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $rules1 = [
                'professionId' => 'required|integer',
                'amount' => 'required|numeric',
                'price' => 'required|numeric',
                'totalPrice' => 'required|numeric'
            ];
            $message1 = [
                'professionId.required' => '获取工种参数失败',
                'professionId.integer' => '工种参数类型错误',
                'amount.required' => '获取施工面积参数失败',
                'amount.numeric' => '施工面积参数类型错误',
                'price.required' => '获取单价参数失败',
                'price.numeric' => '单价参数类型错误',
                'totalPrice.required' => '获取工种预算总价数据失败',
                'totalPrice.numeric' => '工种预算总价类型错误',
            ];
            $continue = true;
            foreach ($input['data'] as $d) {
                $validator1 = Validator::make($d, $rules1, $message1);
                if ($validator1->fails()) {
                    $failed = $validator1->failed();
                    if (key($failed) == 'professionId') {
                        if (key($failed['professionId']) == 'Required') {
                            $this->code = 320609;
                            $this->msg = $validator1->errors()->first();
                        }
                        if (key($failed['professionId']) == 'Integer') {
                            $this->code = 320610;
                            $this->msg = $validator1->errors()->first();
                        }
                    } elseif (key($failed) == 'amount') {
                        if (key($failed['amount']) == 'Required') {
                            $this->code = 320611;
                            $this->msg = $validator1->errors()->first();
                        }
                        if (key($failed['amount']) == 'Numeric') {
                            $this->code = 320612;
                            $this->msg = $validator1->errors()->first();
                        }
                    } elseif (key($failed) == 'price') {
                        if (key($failed['price']) == 'Required') {
                            $this->code = 320613;
                            $this->msg = $validator1->errors()->first();
                        }
                        if (key($failed['price']) == 'Numeric') {
                            $this->code = 320614;
                            $this->msg = $validator1->errors()->first();
                        }
                    } elseif (key($failed) == 'totalPrice') {
                        if (key($failed['totalPrice']) == 'Required') {
                            $this->code = 320615;
                            $this->msg = $validator1->errors()->first();
                        }
                        if (key($failed['totalPrice']) == 'Numeric') {
                            $this->code = 320616;
                            $this->msg = $validator1->errors()->first();
                        }
                    }
                    $continue = false;
                    break;
                }
            }

            if ($continue) {
                $projectBudgetModel = new ProjectBudgetModel();
                $projectBudgetModel->editBudget($input);
                //更新施工段预算
                $sumSectionBudget = $projectBudgetModel->sumBudget(['sectionId' => $input['sectionId']]);
                $projectSectionModel = new ProjectSectionModel();
                $projectSectionModel->update(['id' => $input['sectionId'], 'budget' => $sumSectionBudget]);
                //更新施工区预算
                $sumSectionBudget = $projectBudgetModel->sumBudget(['areaId' => $input['areaId']]);
                $projectAreaModel = new ProjectAreaModel();
                $projectAreaModel->update(['id' => $input['areaId'], 'budget' => $sumSectionBudget]);
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 320601;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 320602;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'areaId') {
                if (key($failed['areaId']) == 'Required') {
                    $this->code = 320603;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['areaId']) == 'Integer') {
                    $this->code = 320604;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'sectionId') {
                if (key($failed['sectionId']) == 'Required') {
                    $this->code = 320605;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['sectionId']) == 'Integer') {
                    $this->code = 320606;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'data') {
                if (key($failed['data']) == 'Required') {
                    $this->code = 320607;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['data']) == 'Array') {
                    $this->code = 320608;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    public function delete(Request $request)
    {
        $rules = [
            'id' => 'required|integer',

        ];
        $message = [
            'id.required' => '获取项目参数失败',
            'id.integer' => '项目参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectModel = new ProjectModel();
            $result = $projectModel->delete($input);
            if (!$result) {
                $this->code = 320703;
                $this->msg = '该项目下已经导入工人，或已设置楼层等信息，不能被删除';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 320701;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 320702;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}