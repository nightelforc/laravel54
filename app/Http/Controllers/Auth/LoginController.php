<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Model\AdminModel;
use App\Http\Model\AdminSessionModel;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/login';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * 系统登录
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function loginProject(Request $request)
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];
        $message = [
            'username.required' => '请填写用户名',
            'password.required' => '请填写密码',
        ];
        $validator = Validator::make($request->all(), $rules, $message);
        if ($validator->passes()) {
            $adminModel = new AdminModel();
            $result = $adminModel->login($request->all());
            if (!empty($result)) {
                if (!is_null($result['projectId']) && $result['projectId'] != 1) {
                    if ($result['status'] == 1) {
                        $result['role'] = (new AdminController())->getRole($result['id']);
                        //获取用户权限
                        $roleId = isset($result['role']['roleId']) ? $result['role']['roleId'] : 0;
                        $result['permission'] = (new AdminController())->getPermission($result['id'], $roleId,2);
                        $result['menu'] = (new AdminController())->getMenu($result['permission']);
                        $token = $this->tokenGenerator();
                        $result['token'] = $token;
                        AdminSessionModel::put($token, $result['id'], $result['projectId']);
                        $this->data = $result;
                    } else {
                        $this->code = 110105;
                        $this->msg = '账号已经被停用';
                    }
                }else{
                    $this->code = 110104;
                    $this->msg = '此账号不能登录项目管理系统';
                }
            } else {
                return $this->ajaxResult(110103, '用户名或密码错误');
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'username') {
                if (key($failed['username']) == 'Required') {
                    $this->code = 110101;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'password') {
                if (key($failed['password']) == 'Required') {
                    $this->code = 110102;
                    $this->msg = $validator->errors()->first();
                }
            }

        }

        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * 系统登出
     *
     * @param Request $request
     * @return mixed
     */
    public function logout(Request $request)
    {
        AdminSessionModel::delete($request->input(config('yucheng.token')));
        return Response::create(['code' => $this->code, 'msg' => '退出成功'], 200);
    }

    /**
     * 修改密码
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function changePwd(Request $request)
    {
        $rules = [
            'adminId' => 'required|integer',
//            'oldPwd' => 'required',
            'password' => 'required|alpha_num|between:6,16',
            'confirmPwd' => 'required|alpha_num|between:6,16|same:password',
        ];
        $message = [
            'adminId.required' => '获取用户参数失败',
            'adminId.integer' => '用户参数类型错误',
//            'oldPwd.required' => '请填写原密码',
            'password.required' => '请输入新密码',
            'password.alpha_num' => '新密码只允许包含字母或数字',
            'password.between' => '新密码长度必须设置为 :min 到 :max 位',
            'confirmPwd.required' => '请输入确认密码',
            'confirmPwd.alpha_num' => '确认密码只允许包含字母或数字',
            'confirmPwd.between' => '确认密码长度必须设置为 :min 到 :max 位',
            'confirmPwd.same' => '新密码和确认密码不一致',
        ];
        $input = $request->except(config('yucheng.token'));
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $adminModel = new AdminModel();
            //验证原密码
            $result = $adminModel->login(['id' => $input['adminId']]);
            if ($result != null) {
                //保存新密码
                $data = [
                    'id' => $input['adminId'],
                    'password' => $input['password'],
                ];
                $result = $adminModel->changePwd($data);
                if ($result < 1) {
                    $this->code = 110212;
                    $this->msg = '修改密码失败';
                }
            } else {
                $this->code = 110211;
                $this->msg = '原密码错误';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'adminId') {
                if (key($failed['adminId']) == 'Required') {
                    $this->code = 110201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['adminId']) == 'Integer') {
                    $this->code = 110202;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'oldPwd') {
                if (key($failed['oldPwd']) == 'Required') {
                    $this->code = 110203;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'password') {
                if (key($failed['password']) == 'Required') {
                    $this->code = 110204;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['password']) == 'AlphaNum') {
                    $this->code = 110205;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['password']) == 'Between') {
                    $this->code = 110206;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'confirmPwd') {
                if (key($failed['confirmPwd']) == 'Required') {
                    $this->code = 110207;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['confirmPwd']) == 'AlphaNum') {
                    $this->code = 110208;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['confirmPwd']) == 'Between') {
                    $this->code = 110209;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['confirmPwd']) == 'Same') {
                    $this->code = 110210;
                    $this->msg = $validator->errors()->first();
                }
            }

        }

        return $this->ajaxResult($this->code, $this->msg);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function forgetPwd(Request $request)
    {
        $rules = [
            'phone' => 'required',
        ];
        $message = [
            'phone.required' => '请填写手机号',
        ];
        $input = $request->only(['phone']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $adminModel = new AdminModel();
            $adminInfo = $adminModel->info($input);
            if (!empty($adminInfo)) {
                $token = $this->tokenGenerator();
                AdminSessionModel::put($token, $adminInfo['id']);
                $this->data = [config('yucheng.token') => $token];
            } else {
                $this->code = 110302;
                $this->msg = '查不到相关账号';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'phone') {
                if (key($failed['phone']) == 'Required') {
                    $this->code = 110301;
                    $this->msg = $validator->errors()->first();
                }
            }

        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function resetPwd(Request $request)
    {
        $rules = [
            config('yucheng.token') => 'required',
            'password' => 'required|alpha_num|between:6,16',
            'confirmPassword' => 'required|alpha_num|between:6,16|same:password',
        ];
        $message = [
            config('yucheng.token') . '.required' => '获取参数失败',
            'password.required' => '请输入新密码',
            'password.alpha_num' => '新密码只允许包含字母或数字',
            'password.between' => '新密码长度必须设置为 :min 到 :max 位',
            'confirmPassword.required' => '请输入确认密码',
            'confirmPassword.alpha_num' => '确认密码只允许包含字母或数字',
            'confirmPassword.between' => '确认密码长度必须设置为 :min 到 :max 位',
            'confirmPassword.same' => '新密码和确认密码不一致',
        ];
        $input = $request->only([config('yucheng.token'), 'password', 'confirmPassword']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $adminModel = new AdminModel();
            $sessionInfo = AdminSessionModel::get($input[config('yucheng.token')]);
            $data = [
                'id' => $sessionInfo['adminId'],
                'password' => $input['password'],
            ];
            $adminModel->changePwd($data);
        } else {
            $failed = $validator->failed();
            if (key($failed) == config('yucheng.token')) {
                if (key($failed[config('yucheng.token')]) == 'Required') {
                    $this->code = 110401;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'password') {
                if (key($failed['password']) == 'Required') {
                    $this->code = 110402;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['password']) == 'AlphaNum') {
                    $this->code = 110403;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['password']) == 'Between') {
                    $this->code = 110404;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'confirmPassword') {
                if (key($failed['confirmPassword']) == 'Required') {
                    $this->code = 110405;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['confirmPassword']) == 'AlphaNum') {
                    $this->code = 110406;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['confirmPassword']) == 'Between') {
                    $this->code = 110407;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['confirmPassword']) == 'Same') {
                    $this->code = 110408;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg);
    }

    /**
     * 系统登录
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function loginCompany(Request $request)
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];
        $message = [
            'username.required' => '请填写用户名',
            'password.required' => '请填写密码',
        ];
        $validator = Validator::make($request->all(), $rules, $message);
        if ($validator->passes()) {
            $adminModel = new AdminModel();
            $result = $adminModel->login($request->all());
            if (!empty($result)) {
                if ($result['projectId'] == 1) {
                    if ($result['status'] == 1) {
                        $result['role'] = (new AdminController())->getRole($result['id']);
                        //获取用户权限
                        $roleId = isset($result['role']['roleId']) ? $result['role']['roleId'] : 0;
                        $result['permission'] = (new AdminController())->getPermission($result['id'], $roleId,1);
                        $result['menu'] = (new AdminController())->getMenu($result['permission']);
                        $token = $this->tokenGenerator();
                        $result['token'] = $token;
                        AdminSessionModel::put($token, $result['id'], $result['projectId']);
                        $this->data = $result;
                    } else {
                        $this->code = 110105;
                        $this->msg = '账号已经被停用';
                    }
                }else{
                    $this->code = 110104;
                    $this->msg = '此账号不能登录公司管理系统';
                }
            } else {
                return $this->ajaxResult(110103, '用户名或密码错误');
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'username') {
                if (key($failed['username']) == 'Required') {
                    $this->code = 110101;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'password') {
                if (key($failed['password']) == 'Required') {
                    $this->code = 110102;
                    $this->msg = $validator->errors()->first();
                }
            }

        }

        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}
