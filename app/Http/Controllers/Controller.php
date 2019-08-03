<?php

namespace App\Http\Controllers;

use App\Http\Model\YearModel;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $code = 100000;
    protected $msg = '保存成功';
    protected $data = [];
    //pasn is short for projectAdminSessionName
    const pasn = 'pasn';

    private $moduleCode = [
        'auth' => [
            'login' => 11,
            'admin' => 12,
            'role' => 13,
            'permission' => 14,
            'workflow' => 15,
            'approval' => 16,
        ],
        'setting' => [
            'setting' => 21,
            'year' => 22,
            'unit' => 23,
            'profession' => 24,
            'assignment' => 25,
        ],
        'company' => [
            'employee' => 31,
            'project' => 32,
        ],
        'project' => [
            'employee' => 41,
            'project' => 42,
            'projectGroup' => 43,
            'supplier' => 44,
            'material' => 45,
            'warehouse' => 46,
        ],
    ];

    /**
     * 自定义ajax返回格式，并生成response
     *
     * @param $code
     * @param $msg
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public function ajaxResult($code, $msg, Array $data = [], $status = 200, Array $headers = ['content-type' => 'application/json;charset=UTF-8'])
    {
        $token = $this->tokenGenerator();
        return Response::create(['code' => $code, 'msg' => $msg, 'data' => $data, config('yucheng.token') => $token], $status, $headers);
    }

    /**
     * token生成器
     *
     * @return string
     */
    public function tokenGenerator()
    {
        $time = time();
        $timeStr = strval($time);
        $timeStr1 = substr($timeStr, 0, 3);
        $timeStr2 = substr($timeStr, 3, 3);
        $timeStr3 = substr($timeStr, 6, 4);
        $timeMD5 = md5($time);
        $token = $timeStr1 . substr_replace($timeMD5, $timeStr2, 16, 0) . $timeStr3;
        request()->session()->put(config('yucheng.token'), $token);
        return $token;
    }

}
