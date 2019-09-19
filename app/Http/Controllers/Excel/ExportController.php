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

    private function columns(array $data){
        $num = count($data[0]);
//        if ($num <27){
            $ascii = $num+64;
            return chr($ascii);
//        }
    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(Request $request){
        $input = $request->only(['name']);
        $path = self::PATH . $input['name'];
        return response()->download($path);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
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
                    $cellData = [["姓名","项目","工种","工号","身份证号","性别","年龄","民族","联系方式","家庭住址","银行卡号","是否签订合同","签合同时间","是否入场教育","入场教育时间"]];
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
                        switch ($d->isContract){
                            case 1:
                                $isContract = '是';
                                break;
                            case 0:
                                $isContract = '否';
                                break;
                            default:
                                $isContract = '';
                        }
                        switch ($d->isEdu){
                            case 1:
                                $isEdu = '是';
                                break;
                            case 0:
                                $isEdu = '否';
                                break;
                            default:
                                $isEdu = '';
                        }
                        $rowData = [$d->name,$d->projectName,$d->professionName,$d->jobNumber,$d->idcard,$gender,$d->age,$d->nation,$d->phone,$d->homeAddress,$d->bankNumber,$isContract,$d->contractTime,$isEdu,$d->eduTime];
                        array_push($cellData,$rowData);

                    }
                    $sheet->fromArray($cellData,null, 'A1', true, false);
                    $count = count($cellData);
                    $column = $this->columns($cellData);
                    $sheet->setColumnFormat(
                        array(
                            'A1:'.$column.$count=>'@',
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function eEmployeeWage(Request $request){
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取工人参数失败',
            'id.integer' => '工人参数类型错误',
        ];
        $input = $request->only('id');
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeModel = new EmployeeModel();
            $info = $employeeModel->employeeInfo($input);

            $fileName = $info['baseInfo']['name'].'工资列表';
//            $fileNameGBK = iconv('utf-8','gbk',$fileName);
            $extensions = 'xlsx';
            Excel::create($fileName,function($excel) use ($info){
                $excel->sheet('sheet1',function($sheet) use ($info){
                    $data = $info['wages'];
                    $cellData = [["时间","工资小计","包工费","杂工费","考勤工资","材料费","借款","生活费","奖金","罚款"]];
                    foreach ($data as $key=>$d){
                        $total = $d['separateAccounts']+$d['otherSeparateAccounts']+$info['baseInfo']['dayValue']*$d['attendance']-$d['materialOrder']-$d['loan']+$d['living']+$d['bonus']-$d['fine'];
                        $rowData = [$key,$total,$d['separateAccounts'],$d['otherSeparateAccounts'],$info['baseInfo']['dayValue']*$d['attendance'],$d['materialOrder'],$d['loan'],$d['living'],$d['bonus'],$d['fine']];
                        array_push($cellData,$rowData);
                    }
                    $sheet->fromArray($cellData,null, 'A1', true, false);
                    $count = count($cellData);
                    $column = $this->columns($cellData);
                    $sheet->setColumnFormat(
                        array(
                            'A1:'.$column.$count=>'@',
                        )
                    );
                    $sheet->setAutoSize(true);
                });
            })->store($extensions);
            $filename = $fileName.'.'.$extensions;
            $this->msg = '点击<a href="http://'.$_SERVER['HTTP_HOST'].'/excel/download?name='.$filename . '&token='.$request->input(self::$token).'">链接</a>开始下载';
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 410201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 410202;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}