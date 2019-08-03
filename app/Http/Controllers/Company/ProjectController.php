<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/24
 * Time: 11:57
 */

namespace App\Http\Controllers\Company;


use App\Http\Controllers\Controller;
use App\Http\Model\ProjectModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function lists(){
        $projectModel = new ProjectModel();
        $this->data = $projectModel->lists();
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request){
        $rules = [
            'name' => 'required',
            'city' => 'required',
            'projectAmount'=>'nullable|numeric',
            'projectAccount'=>'nullable|numeric',
        ];
        $message = [
            'name.required' => '请填写项目名称',
            'city.required' => '请填写城市',
            'projectAmount.numeric' => '项目工程量类型错误',
            'projectAccount.numeric' => '项目工程款类型错误',
        ];
        $input = $request->only(['name', 'city','projectAmount', 'projectAccount']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectModel = new ProjectModel();
            $result = $projectModel->insert($input);
            if (!$result){
                $this->code = 320105;
                $this->msg = '保存失败，请稍后重试';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 320101;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'city') {
                if (key($failed['city']) == 'Required') {
                    $this->code = 320102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'projectAmount') {
                if (key($failed['projectAmount']) == 'Numeric') {
                    $this->code = 320103;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'projectAccount') {
                if (key($failed['projectAccount']) == 'Numeric') {
                    $this->code = 320104;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    public function info(Request $request){
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
                    $this->code = 320201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 320202;
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
    public function edit(Request $request){
        $rules = [
            'id'=>'required|integer',
            'name' => 'required',
            'city' => 'required',
            'projectAmount'=>'nullable|numeric',
            'projectAccount'=>'nullable|numeric',
        ];
        $message = [
            'id.required' => '获取项目参数失败',
            'id.integer' => '项目参数类型错误',
            'name.required' => '请填写项目名称',
            'city.required' => '请填写城市',
            'projectAmount.numeric' => '项目工程量类型错误',
            'projectAccount.numeric' => '项目工程款类型错误',
        ];
        $input = $request->only(['id','name', 'city','projectAmount', 'projectAccount']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectModel = new ProjectModel();
            $info = $projectModel->info(['id'=>$input['id']]);
            if ($info['status'] == 2){
                $this->code = 320307;
                $this->msg = '该项目已完工，项目信息不能修改';
            }else{
                $projectModel->update($input);
            }
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
            }elseif (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 320303;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'city') {
                if (key($failed['city']) == 'Required') {
                    $this->code = 320304;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'projectAmount') {
                if (key($failed['projectAmount']) == 'Numeric') {
                    $this->code = 320305;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'projectAccount') {
                if (key($failed['projectAccount']) == 'Numeric') {
                    $this->code = 320306;
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
    public function editStatus(Request $request){
        $rules = [
            'id'=>'required|integer',
            'status' => 'required|integer|in:2',
        ];
        $message = [
            'id.required' => '获取项目参数失败',
            'id.integer' => '项目参数类型错误',
            'status.required' => '获取项目状态参数失败',
            'status.integer' => '项目状态参数类型错误',
            'status.in' => '项目状态参数不正确',
        ];
        $input = $request->only(['id','status']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectModel = new ProjectModel();
            $projectModel->updateStatus($input);
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
            }elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Required') {
                    $this->code = 320403;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'Integer') {
                    $this->code = 320404;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'In') {
                    $this->code = 320405;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}