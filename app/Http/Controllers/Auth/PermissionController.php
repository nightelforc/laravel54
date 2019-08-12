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

class PermissionController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function lists(){
        $permissionModel = new PermissionModel();
        $this->data = $permissionModel->lists();
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}