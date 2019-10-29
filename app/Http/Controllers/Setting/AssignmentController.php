<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/19
 * Time: 14:02
 */

namespace App\Http\Controllers\Setting;


use App\Http\Controllers\Controller;
use App\Http\Model\AssignmentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request)
    {
        $rules = [
            'professionId' => 'required|integer',
        ];
        $message = [
            'professionId.required' => '获取工种参数失败',
            'professionId.integer' => '工种参数类型错误',
        ];
        $input = $request->only('professionId');
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $assignmentModel = new AssignmentModel();
            $this->data = $assignmentModel->lists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Required') {
                    $this->code = 250101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 250102;
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
            'id.required' => '获取施工项参数失败',
            'id.integer' => '施工项参数类型错误',
        ];
        $input = $request->only('id');
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $assignmentModel = new AssignmentModel();
            $this->data = $assignmentModel->info($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 250101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 250102;
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
            'professionId' => 'required|integer',
            'name' => 'required',
            'unitId' => 'required|integer',
            'order' => 'integer',
        ];
        $message = [
            'professionId.required' => '获取工种参数失败',
            'professionId.integer' => '工种参数类型错误',
            'name.required' => '请输入施工项名称',
            'unitId.required' => '获取工种参数失败',
            'unitId.integer' => '工种参数类型错误',
            'order.integer' => '排序参数类型错误',
        ];
        $input = $request->only(['professionId', 'name', 'unitId', 'remark', 'order']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $assignmentModel = new AssignmentModel();
            $info = $assignmentModel->checkRepeat(['professionId' => $input['professionId'], 'name' => $input['name']]);
            if (empty($info)) {
                $result = $assignmentModel->insert($input);
                if (!$result) {
                    $this->code = 250208;
                    $this->msg = '保存失败，请稍后重试';
                }
            } else {
                $this->code = 250207;
                $this->msg = '当前工种下，请勿重复建立相同名称的施工项';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Required') {
                    $this->code = 250201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 250202;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 250203;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'unitId') {
                if (key($failed['unitId']) == 'Required') {
                    $this->code = 250204;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['unitId']) == 'Integer') {
                    $this->code = 250205;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'order') {
                if (key($failed['order']) == 'Integer') {
                    $this->code = 250206;
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
            'unitId' => 'required|integer',
            'order' => 'integer',
        ];
        $message = [
            'id.required' => '获取施工项参数失败',
            'id.integer' => '施工项参数类型错误',
            'name.required' => '请输入施工项名称',
            'unitId.required' => '获取工种参数失败',
            'unitId.integer' => '工种参数类型错误',
            'order.integer' => '排序参数类型错误',
        ];
        $input = $request->only(['id','name','unitId','remark','order']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $assignmentModel = new AssignmentModel();
            $info = $assignmentModel->info(['id' => $input['id']]);
            $info = $assignmentModel->checkRepeat(['professionId' => $info['professionId'], 'name' => $input['name']], $input['id']);
            if (empty($info)) {
                $assignmentModel->update($input);
            } else {
                $this->code = 250307;
                $this->msg = '当前工种下，请勿重复建立相同名称的施工项';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 250301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 250302;
                    $this->msg = $validator->errors()->first();
                }
            }
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 250303;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'unitId') {
                if (key($failed['unitId']) == 'Required') {
                    $this->code = 250304;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['unitId']) == 'Integer') {
                    $this->code = 250305;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'order') {
                if (key($failed['order']) == 'Integer') {
                    $this->code = 250306;
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
    public
    function editStatus(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'status' => 'required|integer|in:0,1',
        ];
        $message = [
            'id.required' => '获取施工项参数失败',
            'id.integer' => '施工项参数类型错误',
            'status.required' => '未获取到状态',
            'status.integer' => '施工项状态参数类型错误',
            'status.in' => '施工项状态参数值不正确',
        ];
        $input = $request->all();
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $assignmentModel = new AssignmentModel();
            $assignmentModel->updateStatus($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 250401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 250402;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Required') {
                    $this->code = 250401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'Integer') {
                    $this->code = 250402;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'In') {
                    $this->code = 250405;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}