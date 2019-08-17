<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/17
 * Time: 11:19
 */

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\AdminController;
use App\Http\Model\AdminPermissionModel;
use App\Http\Model\AdminSessionModel;
use App\Http\Model\RolePermissionModel;
use Closure;
use Illuminate\Http\Response;

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

//        $adminId = $this->tokenValidate($request);
//        if (!$adminId) {
//            return Response::create(['code' => 100001, 'msg' => '身份信息已过时，请重新登录'], 403);
//        }
//
//        $role = (new AdminController())->getRole($adminId);
//        if (empty($role)){
//            return Response::create(['code' => 100002, 'msg' => '当前账号没有分配角色'], 403);
//        }
//
//        $result = $this->authValidate($request,$adminId,$role['roleId']);
//        if(!$result){
//            return Response::create(['code' => 100003, 'msg' => '当前账号没有权限访问此接口'], 403);
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
        //验证token是否存在
        $adminSession = AdminSessionModel::get($tokenRequest);
        if (empty($adminSession)){
            return false;
        }
        //验证token是否超时
        $curTimestamp = intval(time());
        $sessionTimestamp = intval(strtotime($adminSession['tokenTime']));
        if ($curTimestamp-$sessionTimestamp >= config('yucheng.tokenExist')){
            return false;
        }
        AdminSessionModel::put($tokenRequest,$adminSession['adminId']);
        return $adminSession['adminId'];
    }

    /**
     * 对账号的权限进行验证
     *
     * @param $request
     * @param $adminId
     * @param $roleId
     * @return bool
     */
    public function authValidate($request,$adminId,$roleId){
        $uri = $request->getRequestUri();
        //检查用户权限
        $r1 = (new AdminPermissionModel())->checkAuth($adminId,$uri);
        //检查用户的角色权限
        $r2 = (new RolePermissionModel())->checkAuth($roleId,$uri);

        if ($r1 || $r2){
            return true;
        }else{
            return false;
        }
    }

}