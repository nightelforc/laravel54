<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/13
 * Time: 16:54
 */

namespace App\Http\Controllers\Excel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class ImportController extends Controller
{


    public function iEmployeeLists(Request $request){
        $file = $request->file('file');
        $result = (new ImportHandleController())->employeeLists($file);
        $count  = count($result);
        if ($count > 0){
            $filename = $this->storeExcel($result);
            $this->code = 510101;
            $this->msg = '共有 '.$count . ' 条数据导入失败，点击<a href="http://'.$_SERVER['HTTP_HOST'].'/excel/download?name='.$filename . '&token='.$request->input(self::$token).'">链接</a>查看失败的数据';
        }

        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * 输出excel文件，返回文件名
     *
     * @param $data
     * @return string
     */
    private function storeExcel($data)
    {
        $fileName = time().mt_rand(100,999);
        $extensions = 'xlsx';
        Excel::create($fileName,function($excel) use ($data){
            $excel->sheet('sheet1',function($sheet) use ($data){
//                $sheet->rows($data);
                $sheet->fromArray($data,null, 'A1', true, false);
                $count = count($data);
                $sheet->setColumnFormat(
                    array(
                        'A1:K'.$count=>'@',
                    )
                );
            });
        })->store($extensions);

        return $fileName.'.'.$extensions;
    }
}