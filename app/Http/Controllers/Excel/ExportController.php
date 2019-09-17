<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/15
 * Time: 9:45
 */

namespace App\Http\Controllers\Excel;


use App\Http\Controllers\Controller;
use App\Http\Model\EmployeeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

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
        return response()->download($path);
    }

    public function eEmployeeLists(Request $request){
        $rules = [
            'projectId' => 'required|integer',
            'professionId' => 'nullable|integer',
            'status' => 'nullable|integer',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'professionId.integer' => '工种参数类型错误',
            'status.integer' => '工作状态参数类型错误',
        ];
        $input = $request->all();
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeModel = new EmployeeModel();
            $data = $employeeModel->lists($input,$employeeModel->employeeStatus,true);

            $fileName = time().mt_rand(100,999);
            $extensions = 'xlsx';
            Excel::create($fileName,function($excel) use ($data){
                $excel->sheet('sheet1',function($sheet) use ($data){
                    $cellData = [["姓名","项目","工种","工号","身份证号","性别","年龄","民族","联系方式","家庭住址","银行卡号"]];
                    foreach ($data as $d){
                        switch ($d->gender){
                            case 1:
                                $gender = '男';
                                break;
                            case 2:
                                $gender = '女';
                                break;
                            default:
                                $gender = '';
                        }
                        $rowData = [$d->name,$d->projectName,$d->professionName,$d->jobNumber,$d->idcard,$gender,$d->age,$d->nation,$d->phone,$d->homeAddress,$d->bankNumber];
                        array_push($cellData,$rowData);

                    }
                    $sheet->fromArray($cellData,null, 'A1', true, false);
                    $count = count($cellData);
                    $sheet->setColumnFormat(
                        array(
                            'A1:K'.$count=>'@',
                        )
                    );
                    $sheet->setAutoSize(true);
                });
            })->store($extensions);
            $filename = $fileName.'.'.$extensions;
            $this->msg = '点击<a href="http://'.$_SERVER['HTTP_HOST'].'/excel/download?name='.$filename . '&token='.$request->input(self::$token).'">链接</a>开始下载';
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 520101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 520102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 520103;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Integer') {
                    $this->code = 520104;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}