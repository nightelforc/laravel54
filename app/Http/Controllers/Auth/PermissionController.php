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
            'isProject' => 'required|integer',
        ];
        $message = [
            'isProject.required' => '获取角色参数失败',
            'isProject.integer' => '角色参数类型错误',
        ];
        $input = $request->only(['isProject']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $permissionModel = new PermissionModel();
            $lists = $permissionModel->lists($input);
            $this->data = $this->listToTree($lists);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'isProject') {
                if (key($failed['isProject']) == 'Required') {
                    $this->code = 140101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['isProject']) == 'Integer') {
                    $this->code = 140102;
                    $this->msg = $validator->errors()->first();
                }
            }
        }

        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}