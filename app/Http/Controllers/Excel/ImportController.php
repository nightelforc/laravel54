<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/13
 * Time: 16:54
 */

namespace App\Http\Controllers\Excel;



use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;


class ImportController extends Controller
{


    public function iEmployeeLists(){
        $file = './public/t.xlsx';
        $result = (new ImportHandleController())->employeeLists($file);
        $count  = count($result);
        if ($count > 0){
            $filename = $this->storeExcel($result);
            $this->code = 510101;
            $this->msg = '共有 '.$count . ' 条数据导入失败，点击<a href="./excel/download?name='.$filename . '">链接</a>';
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
                $sheet->rows($data);
            });
        })->store($extensions);

        return $fileName.'.'.$extensions;
    }
}