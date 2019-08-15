<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/12
 * Time: 15:00
 */

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Http\Model\RoleModel;
use App\Http\Model\RolePermissionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function lists()
    {
        $roleModel = new RoleModel();
        $this->data = $roleModel->lists();

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
            'code' => 'required',
        ];
        $message = [
            'name.required' => '请填写角色名称',
            'code.required' => '请填写角色识别码',
        ];
        $input = $request->only(['name', 'code', 'remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $roleModel = new RoleModel();
            $this->data = $roleModel->insert($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 130101;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'code') {
                if (key($failed['code']) == 'Required') {
                    $this->code = 130102;
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
            'id.required' => '获取角色参数失败',
            'id.integer' => '角色参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $roleModel = new RoleModel();
            $this->data = $roleModel->info($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 130201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 130202;
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
            'code' => 'required',
        ];
        $message = [
            'id.required' => '获取角色参数失败',
            'id.integer' => '角色参数类型错误',
            'name.required' => '请填写角色名称',
            'code.required' => '请填写角色识别码',
        ];
        $input = $request->only(['id', 'name', 'code', 'remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $roleModel = new RoleModel();
            $id = $input['id'];
            unset($input['id']);
            $this->data = $roleModel->update($id, $input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 130301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 130302;
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
            'status' => 'required|integer|in:0,1',
        ];
        $message = [
            'id.required' => '获取角色参数失败',
            'id.integer' => '角色参数类型错误',
            'status.required' => '获取角色状态参数失败',
            'status.integer' => '角色状态参数类型错误',
            'status.in' => '角色状态参数不正确',
        ];
        $input = $request->only(['id', 'status']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $roleModel = new RoleModel();
            $id = $input['id'];
            unset($input['id']);
            $this->data = $roleModel->update($id, $input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 130401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 130402;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 130401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 130402;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'In') {
                    $this->code = 130402;
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
    public function delete(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取角色参数失败',
            'id.integer' => '角色参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $roleModel = new RoleModel();
            $result = $roleModel->delete($input);
            if (!$result){
                $this->code = 130503;
                $this->msg = '删除失败';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 130501;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 130502;
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
    public function setPermission(Request $request){
        $rules = [
            'roleId' => 'required|integer',
            'permissions' => 'array',
        ];
        $message = [
            'roleId.required' => '获取角色参数失败',
            'roleId.integer' => '角色参数类型错误',
            'permissions.array' => '权限参数类型错误',
        ];
        $input = $request->only(['roleId','permissions']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $rolePermissionModel = new RolePermissionModel();
            $result = $rolePermissionModel->insert($input);
            $this->msg = '成功设置 '.$result.' 条功能的权限';
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'roleId') {
                if (key($failed['roleId']) == 'Required') {
                    $this->code = 130601;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['roleId']) == 'Integer') {
                    $this->code = 130602;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'permissions') {
                if (key($failed['permissions']) == 'array') {
                    $this->code = 130603;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}