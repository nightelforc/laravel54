<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/15
 * Time: 9:45
 */

namespace App\Http\Controllers\Excel;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    CONST PATH = '../storage/exports/';

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(Request $request){
        $input = $request->only(['name']);
        $path = self::PATH . $input['name'];
        //TODO  做一个资源请求失败的页面
        return response()->download($path);
    }
}