<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Model\AdminModel;
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
    public function login(Request $request)
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
            if ($result != null) {
                if ($result['status'] == 1){
                    unset($result->password);
                    $request->session()->put(parent::pasn, $result);
                    $this->data = $result;
                }else{
                    $this->code = 110104;
                    $this->msg = '账号已经被停用';
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

        return $this->ajaxResult($this->code, $this->msg,$this->data);
    }

    /**
     * 系统登出
     *
     * @param Request $request
     * @return mixed
     */
    public function logout(Request $request)
    {
        $request->session()->forget(parent::pasn);
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
            'oldPwd' => 'required',
            'password' => 'required|alpha_num|between:6,16',
            'confirmPwd' => 'required|alpha_num|between:6,16|same:password',
        ];
        $message = [
            'adminId.required' => '获取用户参数失败',
            'adminId.integer' => '用户参数类型错误',
            'oldPwd.required' => '请填写原密码',
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
            $result = $adminModel->login(['id' => $input['adminId'], 'password' => $input['oldPwd']]);
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
}
