<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/19
 * Time: 11:07
 */

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Auth\ApprovalController;
use App\Http\Controllers\Controller;
use App\Http\Model\UnitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function lists()
    {
        $unitModel = new UnitModel();
        $this->data = $unitModel->lists();
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
            'id.required' => '获取计量单位参数失败',
            'id.integer' => '计量单位参数类型错误',
        ];
        $input = $request->only('id');
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $unitModel = new UnitModel();
            $this->data = $unitModel->info($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 230101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 230102;
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
            'shortname' => 'required',
        ];
        $message = [
            'name.required' => '请输入计量单位名称',
            'shortname.required' => '请输入计量单位简称',
        ];
        $input = $request->only(['name','shortname',parent::$token]);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $unitModel = new UnitModel();
            $insertId = $unitModel->insert($input);
            if ($insertId) {
                $input['id'] = $insertId;
                $approval = ApprovalController::approval('unit',$input);
                if ($approval['status']){
                    if ($approval['result']){
                        $this->msg = '申请提交成功，请等待审批结果';
                    }else{
                        $this->code = 230204;
                        $this->msg = '保存失败，请稍后重试';
                    }
                }
            }else{
                $this->code = 230203;
                $this->msg = '保存失败，请稍后重试';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 230201;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'shortname') {
                if (key($failed['shortname']) == 'Required') {
                    $this->code = 230202;
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
            'shortname' => 'required',
        ];
        $message = [
            'id.required' => '获取计量单位参数失败',
            'id.integer' => '计量单位参数类型错误',
            'name.required' => '请输入计量单位名称',
            'shortname.required' => '请输入计量单位简称',
        ];
        $input = $request->all();
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $unitModel = new UnitModel();
            $unitModel->update($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 230301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 230302;
                    $this->msg = $validator->errors()->first();
                }
            }
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 230303;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'shortname') {
                if (key($failed['shortname']) == 'Required') {
                    $this->code = 230304;
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
            'useStatus' => 'required|integer|in:0,1',
        ];
        $message = [
            'id.required' => '获取计量单位参数失败',
            'id.integer' => '计量单位参数类型错误',
            'useStatus.required' => '未获取到状态',
            'useStatus.integer' => '计量单位状态参数类型错误',
            'useStatus.in' => '计量单位状态参数值不正确',
        ];
        $input = $request->all();
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $unitModel = new UnitModel();
            $unitModel->updateStatus($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 230401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 230402;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'useStatus') {
                if (key($failed['useStatus']) == 'Required') {
                    $this->code = 230401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['useStatus']) == 'Integer') {
                    $this->code = 230402;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['useStatus']) == 'In') {
                    $this->code = 230405;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}