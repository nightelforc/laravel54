<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/12
 * Time: 16:08
 */

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Http\Model\PermissionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request){
        $rules = [
            'type' => 'required|integer',
        ];
        $message = [
            'type.required' => '获取角色参数失败',
            'type.integer' => '角色参数类型错误',
        ];
        $input = $request->only(['type']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $permissionModel = new PermissionModel();
            $this->data = $permissionModel->lists($input);
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