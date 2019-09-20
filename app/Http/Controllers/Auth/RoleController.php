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
            'isProject' => 'nullable|integer',
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'isProject.integer' => '角色类型参数类型错误',
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
            }elseif (key($failed) == 'isProject') {
                if (key($failed['isProject']) == 'Integer') {
                    $this->code = 130709;
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
    public function selectLists(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
        ];
        $input = $request->only(['projectId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            if ($input['projectId'] <= 1){
                $isProject = 1;
            }else{
                $isProject = 2;
            }
            $roleModel = new RoleModel();
            $this->data = $roleModel->selectLists(['isProject'=>$isProject]);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 130801;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 130802;
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
            'isProject' => 'required|integer',
            'professionId' => 'required|integer'
        ];
        $message = [
            'name.required' => '请填写角色名称',
            'isProject.required' => '请选择角色类型',
            'isProject.integer' => '角色类型参数类型错误',
            'professionId.required' => '请选择工种',
            'professionId.integer' => '工种数据类型错误',
        ];
        $input = $request->only(['name', 'isProject','professionId', 'remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $roleModel = new RoleModel();
            $result = $roleModel->checkRepeat(['name'=>$input['name'],'isProject'=>$input['isProject']]);
            if (empty($result)){
                $roleModel->insert($input);
            }else{
                $this->code = 130106;
                $this->msg = '相同类型下已经存在相同名称的角色，请勿重复设置';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 130101;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'isProject') {
                if (key($failed['isProject']) == 'Required') {
                    $this->code = 130102;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['isProject']) == 'Integer') {
                    $this->code = 130103;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Required') {
                    $this->code = 130104;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 130105;
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
            'isProject' => 'required|integer',
            'professionId' => 'required|integer'
        ];
        $message = [
            'id.required' => '获取角色参数失败',
            'id.integer' => '角色参数类型错误',
            'name.required' => '请填写角色名称',
            'isProject.required' => '请选择角色类型',
            'isProject.integer' => '角色类型参数类型错误',
            'professionId.required' => '请选择工种',
            'professionId.integer' => '工种数据类型错误',
        ];
        $input = $request->only(['id', 'name', 'isProject','professionId', 'remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $roleModel = new RoleModel();
            $id = $input['id'];
            unset($input['id']);
            if ($id != 1){
                $result = $roleModel->checkRepeat(['name'=>$input['name'],'isProject'=>$input['isProject']],$id);
                if (empty($result)){
                    $roleModel->update($id, $input);
                }else{
                    $this->code = 130307;
                    $this->msg = '相同类型下已经存在相同名称的角色，请勿重复设置';
                }
            }else{
                $this->code = 130306;
                $this->msg = '不能修改超级管理员角色';
            }
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
            } elseif (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 130303;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'isProject') {
                if (key($failed['isProject']) == 'Required') {
                    $this->code = 130304;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['isProject']) == 'Integer') {
                    $this->code = 130305;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Required') {
                    $this->code = 130306;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 130307;
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
            if($id != 1){
                $roleModel->update($id, $input);
            }else{
                $this->code = 130406;
                $this->msg = '不能停用超级管理员角色';
            }
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
                    $this->code = 130403;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 130404;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'In') {
                    $this->code = 130405;
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
            if($input['id'] != 1){
                $result = $roleModel->delete($input);
                if (!$result){
                    $this->code = 130503;
                    $this->msg = '删除失败';
                }
            }else{
                $this->code = 130504;
                $this->msg = '不能删除超级管理员角色';
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
            'permission' => 'array',
        ];
        $message = [
            'roleId.required' => '获取角色参数失败',
            'roleId.integer' => '角色参数类型错误',
            'permission.array' => '权限参数类型错误',
        ];
        $input = $request->only(['roleId','permission']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            if($input['roleId'] != 1){
                $rolePermissionModel = new RolePermissionModel();
                $result = $rolePermissionModel->insert($input);
                $this->msg = '成功设置 '.$result.' 条功能的权限';
            }else{
                $this->code = 130604;
                $this->msg = '不能设置超级管理员角色权限';
            }
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
            }elseif (key($failed) == 'permission') {
                if (key($failed['permission']) == 'array') {
                    $this->code = 130603;
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
    public function getPermission(Request $request){
        $rules = [
            'roleId' => 'required|integer',
        ];
        $message = [
            'roleId.required' => '获取角色参数失败',
            'roleId.integer' => '角色参数类型错误',
        ];
        $input = $request->only(['roleId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $rolePermissionModel = new RolePermissionModel();
            $this->data = $rolePermissionModel->lists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'roleId') {
                if (key($failed['roleId']) == 'Required') {
                    $this->code = 130901;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['roleId']) == 'Integer') {
                    $this->code = 130902;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}