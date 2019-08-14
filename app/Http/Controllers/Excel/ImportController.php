<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/13
 * Time: 16:54
 */

namespace App\Http\Controllers\Excel;


use Maatwebsite\Excel\Facades\Excel;

class ImportController
{
    public function test(){
        var_dump(Excel::load('./public/t.xlsx', function($reader) {
        })->get());
    }
}