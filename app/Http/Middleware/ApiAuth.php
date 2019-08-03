<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/17
 * Time: 11:19
 */

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Closure;

class ApiAuth
{
    /**
     * 处理api权限问题.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//        $token = $this->tokenValidate($request);
//        if (!$token) {
//            return Response::create(['code' => 100001, 'msg' => '身份信息已过时，请重新登录'], 403);
//        }
//        $session = $this->sessionValidate($request);
//        if (!$session) {
//            return Response::create(['code' => 100002, 'msg' => '登录信息已过时，请重新登录'], 403);
//        }
        return $next($request);
    }

    /**
     * 对接口进行token验证
     *
     * @param $request
     * @return bool
     */
    public function tokenValidate($request)
    {
        $tokenRequest = $request->input(config('yucheng.token'), '');
        $tokenSession = $request->session()->get(config('yucheng.token'), '123456');
        if ($tokenRequest == '') {
            return false;
        } elseif ($tokenSession == '') {
            return false;
        } elseif ($tokenSession != $tokenRequest) {
            return false;
        }
        return true;
    }


    private function sessionValidate($request)
    {
        if ($request->session()->has(Controller::pasn)) {
            return true;
        }
        return false;
    }
}