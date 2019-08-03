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
//            'projectId' => 'required|integer',
            'limit' => 'nullable|integer|in:10,20,50',
            'page' => 'nullable|integer|min:1',
        ];
        $message = [
//            'projectId.required' => '获取项目参数失败',
//            'projectId.integer' => '项目参数类型错误',
            'limit.integer' => '记录条数参数类型错误',
            'limit.in' => '记录条数参数值不正确',
            'page.integer' => '页码参数类型错误',
            'page.min' => '页码参数值不小于:min',
        ];
        $input = $request->all();
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $adminModel = new AdminModel();
            $this->data = $adminModel->lists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 120101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 120102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'limit') {
                if (key($failed['limit']) == 'Integer') {
                    $this->code = 120103;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['limit']) == 'In') {
                    $this->code = 120104;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'page') {
                if (key($failed['page']) == 'Integer') {
                    $this->code = 120105;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['page']) == 'Min') {
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
            'phone' => 'required',
        ];
        $message = [
            'username.required' => '请填写用户名',
            'roleId.required' => '请选择角色',
            'roleId.integer' => '角色参数类型错误',
            'name.required' => '请填写姓名',
            'projectId.required' => '请选择账号所属项目',
            'projectId.integer' => '项目参数类型错误',
            'phone.required' => '请填写联系方式',
        ];
        $input = $request->only(['username', 'roleId', 'name', 'projectId', 'phone']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $adminModel = new AdminModel();
            $result = $adminModel->addAdmin($input);
            if (!$result) {
                $this->code = 120208;
                $this->msg = json_encode($result);
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
            } elseif (key($failed) == 'phone') {
                if (key($failed['phone']) == 'Required') {
                    $this->code = 120207;
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
            'roleId' => 'required|integer',
            'name' => 'required',
            'projectId' => 'required|integer',
            'phone' => 'required',
        ];
        $message = [
            'id.required' => '获取用户参数失败',
            'id.integer' => '用户参数类型错误',
            'username.required' => '请填写用户名',
            'roleId.required' => '请选择角色',
            'roleId.integer' => '角色参数类型错误',
            'name.required' => '请填写姓名',
            'projectId.required' => '请选择账号所属项目',
            'projectId.integer' => '项目参数类型错误',
            'phone.required' => '请填写联系方式',
        ];
        $input = $request->only(['id', 'username', 'roleId', 'name', 'projectId', 'phone']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $adminModel = new AdminModel();
            $result = $adminModel->editAdmin($input);
            if (!$result) {
                $this->code = 120410;
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
            } elseif (key($failed) == 'roleId') {
                if (key($failed['roleId']) == 'Required') {
                    $this->code = 120404;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['roleId']) == 'Integer') {
                    $this->code = 120405;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'name') {
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
            } elseif (key($failed) == 'phone') {
                if (key($failed['phone']) == 'Required') {
                    $this->code = 120409;
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
        $input = $request->only(['id','status']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $adminModel = new AdminModel();
            $adminModel->updateStatus($input['id'],['status'=>$input['status']]);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 120501;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 12052;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}