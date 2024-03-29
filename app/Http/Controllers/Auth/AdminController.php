<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/30
 * Time: 11:59
 */

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Http\Model\AdminModel;
use App\Http\Model\AdminPermissionModel;
use App\Http\Model\AdminRoleModel;
use App\Http\Model\PermissionModel;
use App\Http\Model\RolePermissionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request)
    {
        $rules = [
            'projectId' => 'nullable|integer',
            'length' => 'nullable|integer|in:10,20,50',
            'start' => 'nullable|integer|min:0',
        ];
        $message = [
            'projectId.integer' => '项目参数类型错误',
            'length.integer' => '记录条数参数类型错误',
            'length.in' => '记录条数参数值不正确',
            'start.integer' => '页码参数类型错误',
            'start.min' => '页码参数值不小于:min',
        ];
        $input = $request->all();
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $adminModel = new AdminModel();
            $lists = $adminModel->lists($input);
            $countLists = $adminModel->countLists($input);
            $this->data = [
                "draw"=>$input['draw'],
                "data"=>$lists,
                "recordsFiltered"=>$countLists,
                "recordsTotal"=>$countLists,
            ];
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
//                if (key($failed['projectId']) == 'Required') {
//                    $this->code = 120101;
//                    $this->msg = $validator->errors()->first();
//                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 120102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Integer') {
                    $this->code = 120103;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 120104;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Integer') {
                    $this->code = 120105;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 120106;
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
            'username' => 'required',
            'roleId' => 'required|integer',
            'name' => 'required',
            'projectId' => 'required|integer',
            'professionId' => 'required|integer',
            'phone' => 'required',
        ];
        $message = [
            'username.required' => '请填写用户名',
            'roleId.required' => '请选择角色',
            'roleId.integer' => '角色参数类型错误',
            'name.required' => '请填写姓名',
            'projectId.required' => '请选择账号所属项目',
            'projectId.integer' => '项目参数类型错误',
            'professionId.required' => '请选择账号负责工种',
            'professionId.integer' => '工种参数类型错误',
            'phone.required' => '请填写联系方式',
        ];
        $input = $request->only(['username', 'roleId', 'name', 'projectId', 'phone','professionId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $adminModel = new AdminModel();
            $info = $adminModel->checkRepeat(['username'=>$input['username'],'name'=>$input['name']]);
            if (empty($info)){
                $result = $adminModel->addAdmin($input);
                if (!$result) {
                    $this->code = 120210;
                    $this->msg = json_encode($result);
                }
            }else{
                $this->code = 120211;
                $this->msg = '不能建立相同账号名或相同姓名的账号';
            }

        } else {
            $failed = $validator->failed();
            if (key($failed) == 'username') {
                if (key($failed['username']) == 'Required') {
                    $this->code = 120201;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'roleId') {
                if (key($failed['roleId']) == 'Required') {
                    $this->code = 120202;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['roleId']) == 'Integer') {
                    $this->code = 120203;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'name') {
                if (key($failed['name']) == 'Integer') {
                    $this->code = 120204;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 120205;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 120206;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Required') {
                    $this->code = 120207;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 120208;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'phone') {
                if (key($failed['phone']) == 'Required') {
                    $this->code = 120209;
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
            'id.required' => '获取管理员参数失败',
            'id.integer' => '管理员参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $adminModel = new AdminModel();
            $this->data = $adminModel->info($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 120301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 120302;
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
            'username' => 'required',
//            'roleId' => 'required|integer',
            'name' => 'required',
            'projectId' => 'required|integer',
            'professionId' => 'required|integer',
            'phone' => 'required',
        ];
        $message = [
            'id.required' => '获取用户参数失败',
            'id.integer' => '用户参数类型错误',
            'username.required' => '请填写用户名',
//            'roleId.required' => '请选择角色',
//            'roleId.integer' => '角色参数类型错误',
            'name.required' => '请填写姓名',
            'projectId.required' => '请选择账号所属项目',
            'projectId.integer' => '项目参数类型错误',
            'professionId.required' => '请选择账号负责工种',
            'professionId.integer' => '工种参数类型错误',
            'phone.required' => '请填写联系方式',
        ];
        $input = $request->only(['id', 'username', 'name', 'projectId', 'phone','professionId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $adminModel = new AdminModel();
            $result = $adminModel->editAdmin($input);
            if (!$result) {
                $this->code = 120412;
                $this->msg = $result;
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 120401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 120402;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'username') {
                if (key($failed['username']) == 'Required') {
                    $this->code = 120403;
                    $this->msg = $validator->errors()->first();
                }
            }
//            elseif (key($failed) == 'roleId') {
//                if (key($failed['roleId']) == 'Required') {
//                    $this->code = 120404;
//                    $this->msg = $validator->errors()->first();
//                }
//                if (key($failed['roleId']) == 'Integer') {
//                    $this->code = 120405;
//                    $this->msg = $validator->errors()->first();
//                }
//            }
            elseif (key($failed) == 'name') {
                if (key($failed['name']) == 'Integer') {
                    $this->code = 120406;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 120407;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 120408;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Required') {
                    $this->code = 120409;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 120410;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'phone') {
                if (key($failed['phone']) == 'Required') {
                    $this->code = 120411;
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
            'id.required' => '获取管理员参数失败',
            'id.integer' => '管理员参数类型错误',
            'status.required' => '获取管理员状态失败',
            'status.integer' => '管理员状态参数类型错误',
            'status.in' => '管理员状态参数不正确',
        ];
        $input = $request->only(['id', 'status']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $adminModel = new AdminModel();
            $adminModel->updateStatus($input['id'], ['status' => $input['status']]);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 120501;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 120502;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param $adminId
     * @return mixed
     */
    public function getRole($adminId)
    {
        $adminRoleModel = new AdminRoleModel();
        $adminRole = $adminRoleModel->getAdminRole($adminId);
        return $adminRole;
    }

    /**
     * @param $adminId
     * @param int $roleId
     * @param $isProject
     * @return array
     */
    public function getPermission($adminId, $roleId = 0,$isProject = 1)
    {
        $rolePermission = [];
        if ($roleId != 0) {
            $rolePermissionModel = new RolePermissionModel();
            $rolePermission = $rolePermissionModel->getPermission($roleId);
        }
        //获取用户特有权限

        if ($adminId == 1){
            $permissionModel = new PermissionModel();
            $adminPermission = $permissionModel->lists(['isProject'=>$isProject]);
        }else{
            $adminPermissionModel = new AdminPermissionModel();
            $adminPermission = $adminPermissionModel->getPermission($adminId);
        }


        return array_merge($rolePermission, $adminPermission);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function setRole(Request $request){
        $rules = [
            'adminId' => 'required|integer',
            'roleId' => 'required|integer',
        ];
        $message = [
            'adminId.required' => '获取管理员参数失败',
            'adminId.integer' => '管理员参数类型错误',
            'roleId.required' => '请选择角色',
            'roleId.integer' => '角色参数类型错误',
        ];
        $input = $request->only(['adminId','roleId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $adminRoleModel = new AdminRoleModel();
            $adminRoleModel->delete(['adminId'=>$input['adminId']]);
            $adminRoleModel->insert($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'adminId') {
                if (key($failed['adminId']) == 'Required') {
                    $this->code = 120601;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['adminId']) == 'Integer') {
                    $this->code = 120602;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'roleId') {
                if (key($failed['roleId']) == 'Required') {
                    $this->code = 120603;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['roleId']) == 'Integer') {
                    $this->code = 120604;
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
            'adminId' => 'required|integer',
            'permissions' => 'array',
        ];
        $message = [
            'adminId.required' => '获取管理员参数失败',
            'adminId.integer' => '管理员参数类型错误',
            'permissions.array' => '权限参数类型错误',
        ];
        $input = $request->only(['adminId','permissions']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $adminPermissionModel = new AdminPermissionModel();
            $result = $adminPermissionModel->insert($input);
            $this->msg = '成功设置 '.$result.' 条功能的权限';
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'adminId') {
                if (key($failed['adminId']) == 'Required') {
                    $this->code = 120701;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['adminId']) == 'Integer') {
                    $this->code = 120702;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'permissions') {
                if (key($failed['permissions']) == 'array') {
                    $this->code = 120703;
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
    public function selectLists(Request $request){
        $rules = [
            'projectId' => 'required|integer',
        ];
        $message = [
            'projectId.required' => '获取管理员参数失败',
            'projectId.integer' => '管理员参数类型错误',
        ];
        $input = $request->only(['projectId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $adminModel = new AdminModel();
            $this->data = $adminModel->selectLists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 120801;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 120802;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param array $permission
     * @return array
     */
    public function getMenu(array $permission)
    {
        $menuLists = [];
        foreach($permission as $p){
            if ($p->type == 1){
                $menuLists[] = $p;
            }
        }
        if (!empty($menuLists)){
            return $this->listToTree($menuLists);
        }else{
            return [];
        }

    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function systemInfo(){
        $laravel = app();
        $con=mysqli_connect(env('DB_HOST'),env("DB_USERNAME"),env("DB_PASSWORD"),env("DB_DATABASE"));
        $this->data = [
            'softVersion'=>env('version'),
            'server'=>php_uname('s').' '.php_uname('r').' '.php_uname('m'),
            'PHPVersion'=>PHP_VERSION,
            'laravelVersion'=>$laravel::VERSION,
            'time'=>date("Y-m-d H:i:s"),
            'env'=>$_SERVER["SERVER_SOFTWARE"],
            'host'=>$_SERVER["HTTP_HOST"],
            'cgi'=>php_sapi_name(),
            'mysqlVersion'=>mysqli_get_server_info($con),
        ];
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}