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
            'length.integer' => '记录条数参数类型错误',
            'length.in' => '记录条数参数值不正确',
            'start.integer' => '页码参数类型错误',
            'start.min' => '页码参数值不小于:min',
        ];
        $input = $request->all();
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $roleModel = new RoleModel();
            $lists = $roleModel->lists($input);
            $countLists = $roleModel->countLists($input);
            $this->data = [
                "draw"=>$input['draw'],
                "data"=>$lists,
                "recordsFiltered"=>$countLists,
                "recordsTotal"=>$countLists,
            ];
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 130701;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 130702;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 130703;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 130704;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 130705;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 130706;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 130707;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 130708;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function selectLists()
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
            'type' => 'required|integer',
        ];
        $message = [
            'name.required' => '请填写角色名称',
            'type.required' => '请选择角色类型',
            'type.integer' => '角色类型参数类型错误',
        ];
        $input = $request->only(['name', 'type', 'remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $roleModel = new RoleModel();
            $result = $roleModel->checkRepeat($input);
            if ($result == 0){
                $roleModel->insert($input);
            }else{
                $this->code = 130104;
                $this->msg = '相同类型下已经存在相同名称的角色，请勿重复设置';
            }

        } else {
            $failed = $validator->failed();
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 130101;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'type') {
                if (key($failed['code']) == 'Required') {
                    $this->code = 130102;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['code']) == 'Integer') {
                    $this->code = 130103;
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
            'type' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取角色参数失败',
            'id.integer' => '角色参数类型错误',
            'name.required' => '请填写角色名称',
            'type.required' => '请选择角色类型',
            'type.integer' => '角色类型参数类型错误',
        ];
        $input = $request->only(['id', 'name', 'type', 'remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $roleModel = new RoleModel();
            $id = $input['id'];
            unset($input['id']);
            $roleModel->update($id, $input);
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
            } elseif (key($failed) == 'type') {
                if (key($failed['code']) == 'Required') {
                    $this->code = 130303;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['code']) == 'Integer') {
                    $this->code = 130304;
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
            $roleModel->update($id, $input);
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