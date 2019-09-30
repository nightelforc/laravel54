<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/15
 * Time: 9:45
 */

namespace App\Http\Controllers\Excel;


use App\Http\Controllers\Controller;
use App\Http\Model\EmployeeLivingModel;
use App\Http\Model\EmployeeLoanModel;
use App\Http\Model\EmployeeModel;
use App\Http\Model\ProjectAreaModel;
use App\Http\Model\ProjectGroupAssignmentModel;
use App\Http\Model\ProjectGroupModel;
use App\Http\Model\ProjectGroupSeparateAccountsModel;
use App\Http\Model\ProjectModel;
use App\Http\Model\ProjectSectionModel;
use App\Http\Model\SupplierOrdersModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    CONST PATH = '../storage/exports/';

    private function columns(array $data)
    {
        $num = count($data[0]);
//        if ($num <27){
        $ascii = $num + 64;
        return chr($ascii);
//        }
    }

    /**
     * @param $filename
     * @param $request
     * @return string
     */
    private function downloadURL($filename, $request)
    {
        return '点击<a target="_blank" href="http://' . $_SERVER['HTTP_HOST'] . '/excel/download?name=' . urlencode($filename) . '&token=' . $request->input(self::$token) . '">链接</a>开始下载';
    }

    /**
     *
     */
    private function fileName($name)
    {
        if (in_array(PHP_OS, ['WINNT', 'WIN32', 'Windows'])) {
            $fileName = iconv('utf-8', 'gbk', $name);
        } else {
            $fileName = $name;
        }
        return $fileName;
    }

    /**
     * @param Request $request
     * @return string|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(Request $request)
    {
        $input = $request->only(['name']);
        $filename = $this->fileName(urldecode($input['name']));
        $path = self::PATH . $filename;
        if (file_exists($path)) {
            return response()->download($path,$input['name']);
        } else {
            return "文件不存在,请尝试重新下载";
        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function eEmployeeLists(Request $request)
    {
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
            $data = $employeeModel->lists($input, $employeeModel->employeeStatus, true);

            $fileName = time() . mt_rand(100, 999);
            $extensions = 'xlsx';
            Excel::create($fileName, function ($excel) use ($data) {
                $excel->sheet('sheet1', function ($sheet) use ($data) {
                    $cellData = [["姓名", "项目", "工种", "工号", "身份证号", "性别", "年龄", "民族", "联系方式", "家庭住址", "银行卡号", "是否签订合同", "签合同时间", "是否入场教育", "入场教育时间"]];
                    foreach ($data as $d) {
                        switch ($d->gender) {
                            case 1:
                                $gender = '男';
                                break;
                            case 2:
                                $gender = '女';
                                break;
                            default:
                                $gender = '';
                        }
                        switch ($d->isContract) {
                            case 1:
                                $isContract = '是';
                                break;
                            case 0:
                                $isContract = '否';
                                break;
                            default:
                                $isContract = '';
                        }
                        switch ($d->isEdu) {
                            case 1:
                                $isEdu = '是';
                                break;
                            case 0:
                                $isEdu = '否';
                                break;
                            default:
                                $isEdu = '';
                        }
                        $rowData = [$d->name, $d->projectName, $d->professionName, $d->jobNumber, $d->idcard, $gender, $d->age, $d->nation, $d->phone, $d->homeAddress, $d->bankNumber, $isContract, $d->contractTime, $isEdu, $d->eduTime];
                        array_push($cellData, $rowData);

                    }
                    $sheet->fromArray($cellData, null, 'A1', true, false);
                    $count = count($cellData);
                    $column = $this->columns($cellData);
                    $sheet->setColumnFormat(
                        array(
                            'A1:' . $column . $count => '@',
                        )
                    );
                    $sheet->setAutoSize(true);
                });
            })->store($extensions);
            $filename = $fileName . '.' . $extensions;
            $this->msg = $this->downloadURL($filename, $request);
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
    public function eEmployeeWage(Request $request)
    {
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

            $fileName = $info['baseInfo']['name'] . '工资列表';
            $fileNameExcel = $this->fileName($fileName);
            $extensions = 'xlsx';
            Excel::create($fileNameExcel, function ($excel) use ($info) {
                $excel->sheet('sheet1', function ($sheet) use ($info) {
                    $data = $info['wages'];
                    $cellData = [["时间", "工资小计", "包工费", "杂工费", "考勤工资", "材料费", "借款", "生活费", "奖金", "罚款"]];
                    foreach ($data as $key => $d) {
                        $total = $d['separateAccounts'] + $d['otherSeparateAccounts'] + $info['baseInfo']['dayValue'] * $d['attendance'] - $d['materialOrder'] - $d['loan'] + $d['living'] + $d['bonus'] - $d['fine'];
                        $rowData = [$key, $total, $d['separateAccounts'], $d['otherSeparateAccounts'], $info['baseInfo']['dayValue'] * $d['attendance'], $d['materialOrder'], $d['loan'], $d['living'], $d['bonus'], $d['fine']];
                        array_push($cellData, $rowData);
                    }
                    $sheet->fromArray($cellData, null, 'A1', true, false);
                    $count = count($cellData);
                    $column = $this->columns($cellData);
                    $sheet->setColumnFormat(
                        array(
                            'A1:' . $column . $count => '@',
                        )
                    );
                    $sheet->setAutoSize(true);
                });
            })->store($extensions);
            $filename = $fileName . '.' . $extensions;
            $this->msg = $this->downloadURL($filename, $request);
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function eAssignment(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'sectionId' => 'required|integer',
            'professionId' => 'required|integer',
            'groupId' => 'required|integer',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'sectionId.required' => '获取施工区参数失败',
            'sectionId.integer' => '施工区参数类型错误',
            'professionId.required' => '获取工种参数失败',
            'professionId.integer' => '工种参数类型错误',
            'groupId.required' => '获取班组参数失败',
            'groupId.integer' => '班组参数类型错误',
        ];
        $input = $request->only(['projectId', 'sectionId', 'professionId', 'groupId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupAssignmentModel = new ProjectGroupAssignmentModel();
            $projectGroupSeparateAccountsModel = new ProjectGroupSeparateAccountsModel();
            $data = [
                'projectId' => $input['projectId'],
                'sectionId' => $input['sectionId'],
                'groupId' => $input['groupId'],
            ];
            //施工项分账记录
            $assignment = $projectGroupAssignmentModel->lists($data);
            //班组成员分账记录
            $separate = $projectGroupSeparateAccountsModel->lists($data);

            $projectInfo = (new ProjectModel())->info(['id' => $input['projectId']]);
            $sectionInfo = (new ProjectSectionModel())->info(['id' => $input['sectionId']]);
            $areaInfo = (new ProjectAreaModel())->info(['id' => $sectionInfo['areaId']]);
            $groupInfo = (new ProjectGroupModel())->info(['id' => $input['groupId']]);
            $fileName = $projectInfo['name'] . '-' . $areaInfo['name'] . '-' . $sectionInfo['name'] . '-' . $groupInfo['name'] . '分账表';
            $fileNameExcel = $this->fileName($fileName);
            $extensions = 'xlsx';
            Excel::create($fileNameExcel, function ($excel) use ($assignment, $separate, $fileName, $projectInfo, $sectionInfo, $areaInfo, $groupInfo) {
                $excel->sheet('sheet1', function ($sheet) use ($assignment, $separate, $fileName, $projectInfo, $sectionInfo, $areaInfo, $groupInfo) {
                    $sheet->setWidth([
                        "A" => "10",
                        "B" => "15",
                        "C" => "15",
                        "D" => "15",
                        "E" => "15",
                        "F" => "15",
                        "G" => "15",
                        "H" => "25",
                    ]);
                    $sheet->setCellValue('A1', $fileName);
                    $sheet->mergeCells('A1:H1');
                    $sheet->setCellValue('A2', '项目');
                    $sheet->setCellValue('B2', $projectInfo['name']);
                    $sheet->setCellValue('C2', '楼栋/施工区');
                    $sheet->setCellValue('D2', $areaInfo['name']);
                    $sheet->setCellValue('E2', '楼层/施工段');
                    $sheet->setCellValue('F2', $sectionInfo['name']);
                    $sheet->setCellValue('G2', '班组');
                    $sheet->setCellValue('H2', $groupInfo['name']);

                    $cellData = [["编号", "施工项", "工程量", "单位", "单价", "总价", "记录时间", "签字"]];
                    foreach ($assignment as $key => $d) {
                        $rowData = [$key + 1, $d->assignmentName, $d->amount, '-', $d->price, $d->totalPrice, $d->completeTime, ''];
                        array_push($cellData, $rowData);
                    }
                    $sheet->fromArray($cellData, null, 'A3', true, false);
                });
            })->store($extensions);
            $filename = $fileName . '.' . $extensions;
            $this->msg = $this->downloadURL($filename, $request);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 410201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 410202;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'sectionId') {
                if (key($failed['sectionId']) == 'Required') {
                    $this->code = 410201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['sectionId']) == 'Integer') {
                    $this->code = 410202;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'groupId') {
                if (key($failed['groupId']) == 'Required') {
                    $this->code = 410201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['groupId']) == 'Integer') {
                    $this->code = 410202;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Required') {
                    $this->code = 410201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 410202;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function eAccounts(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'sectionId' => 'required|integer',
            'professionId' => 'required|integer',
            'groupId' => 'required|integer',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'sectionId.required' => '获取施工区参数失败',
            'sectionId.integer' => '施工区参数类型错误',
            'professionId.required' => '获取工种参数失败',
            'professionId.integer' => '工种参数类型错误',
            'groupId.required' => '获取班组参数失败',
            'groupId.integer' => '班组参数类型错误',
        ];
        $input = $request->only(['projectId', 'sectionId', 'professionId', 'groupId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupAssignmentModel = new ProjectGroupAssignmentModel();
            $projectGroupSeparateAccountsModel = new ProjectGroupSeparateAccountsModel();
            $data = [
                'projectId' => $input['projectId'],
                'sectionId' => $input['sectionId'],
                'groupId' => $input['groupId'],
            ];
            //施工项分账记录
            $assignment = $projectGroupAssignmentModel->lists($data);
            //班组成员分账记录
            $separate = $projectGroupSeparateAccountsModel->lists($data);

            $projectInfo = (new ProjectModel())->info(['id' => $input['projectId']]);
            $sectionInfo = (new ProjectSectionModel())->info(['id' => $input['sectionId']]);
            $areaInfo = (new ProjectAreaModel())->info(['id' => $sectionInfo['areaId']]);
            $groupInfo = (new ProjectGroupModel())->info(['id' => $input['groupId']]);
            $fileName = $projectInfo['name'] . '-' . $areaInfo['name'] . '-' . $sectionInfo['name'] . '-' . $groupInfo['name'] . '班组成员分账表';
            $fileNameExcel = $this->fileName($fileName);
            $extensions = 'xlsx';
            Excel::create($fileNameExcel, function ($excel) use ($assignment, $separate, $fileName, $projectInfo, $sectionInfo, $areaInfo, $groupInfo) {
                $excel->sheet('sheet1', function ($sheet) use ($assignment, $separate, $fileName, $projectInfo, $sectionInfo, $areaInfo, $groupInfo) {
                    $sheet->setWidth([
                        "A" => "10",
                        "B" => "15",
                        "C" => "15",
                        "D" => "15",
                        "E" => "15",
                        "F" => "15",
                        "G" => "15",
                        "H" => "25",
                    ]);
                    $sheet->setCellValue('A1', $fileName);
                    $sheet->mergeCells('A1:H1');
                    $sheet->setCellValue('A2', '项目');
                    $sheet->setCellValue('B2', $projectInfo['name']);
                    $sheet->setCellValue('C2', '楼栋/施工区');
                    $sheet->setCellValue('D2', $areaInfo['name']);
                    $sheet->setCellValue('E2', '楼层/施工段');
                    $sheet->setCellValue('F2', $sectionInfo['name']);
                    $sheet->setCellValue('G2', '班组');
                    $sheet->setCellValue('H2', $groupInfo['name']);

                    $cellData_1 = [["编号", "姓名", "工号", "角色", "分账金额", "记录时间", "备注", "签字"]];
                    foreach ($separate as $key => $d) {
                        $leader = $d->isLeader ? '组长' : '组员';
                        $rowData = [$key + 1, $d->employeeName, $d->jobNumber, $leader, $d->account, $d->separateTime, $d->remark, ''];
                        array_push($cellData_1, $rowData);
                    }

                    $sheet->fromArray($cellData_1, null, 'A3', true, false);
                });
            })->store($extensions);
            $filename = $fileName . '.' . $extensions;
            $this->msg = $this->downloadURL($filename, $request);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 410201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 410202;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'sectionId') {
                if (key($failed['sectionId']) == 'Required') {
                    $this->code = 410201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['sectionId']) == 'Integer') {
                    $this->code = 410202;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'groupId') {
                if (key($failed['groupId']) == 'Required') {
                    $this->code = 410201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['groupId']) == 'Integer') {
                    $this->code = 410202;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Required') {
                    $this->code = 410201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 410202;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    public function eWageLists(Request $request){
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
            $lists = $employeeModel->lists($input,$employeeModel->employeeStatus,true);
            foreach ($lists as $key => $l) {
                $wages = $employeeModel->wages($l->id);
                $wagesTotal = [
                    'bonus' => 0,
                    'fine' => 0,
                    'living' => 0,
                    'loan' => 0,
                    'materialOrder' => 0,
                    'attendance' => 0,
                    'separateAccounts' => 0,
                    'otherSeparateAccounts' => 0,
                ];
                foreach ($wages as $w){
                    $wagesTotal['bonus'] += $w['bonus'];
                    $wagesTotal['fine'] += $w['fine'];
                    $wagesTotal['living'] += $w['living'];
                    $wagesTotal['loan'] += $w['loan'];
                    $wagesTotal['materialOrder'] += $w['materialOrder'];
                    $wagesTotal['attendance'] += $w['attendance'];
                    $wagesTotal['separateAccounts'] += $w['separateAccounts'];
                    $wagesTotal['otherSeparateAccounts'] += $w['otherSeparateAccounts'];
                }
                $lists[$key]->wage = $wagesTotal;
            }

            $projectInfo = (new ProjectModel())->info(['id' => $input['projectId']]);
            $projectName = (isset($projectInfo['name'])&&!empty($projectInfo['name']))?$projectInfo['name']:'';
            $fileName = date('Y').'年度'.$projectName . '工人工资年度汇总表'.date('Ymd');
            $fileNameExcel = $this->fileName($fileName);
            $extensions = 'xlsx';
            Excel::create($fileNameExcel, function ($excel) use ($lists,$fileName) {
                $excel->sheet('sheet1', function ($sheet) use ($lists,$fileName) {
                    $sheet->setWidth([
                        "A" => "6",
                        "B" => "15",
                        "C" => "8",
                        "D" => "6",
                        "E" => "6",
                        "F" => "8",
                        "G" => "8",
                        "H" => "8",
                        "J" => "8",
                        "K" => "8",
                        "L" => "8",
                        "M" => "8",
                        "N" => "8",
                        "O" => "8",
                        "P" => "12",
                        "Q" => "50",
                    ]);
                    $sheet->setCellValue('A1', $fileName);
                    $sheet->mergeCells('A1:Q1');

                    $cellData = [["编号", "项目", "姓名", "工号", "工种", "在职状态", "总计", "包工费", "杂工费",
                        "考勤工资", "材料费", "生活费", "借款","奖金","罚款","签字","备注"]];
                    foreach ($lists as $key => $d) {
                        switch ($d->status){
                            case 1:
                                $status = '在岗';
                                break;
                            case 2:
                                $status = '待岗';
                                break;
                            case 3:
                                $status = '离职';
                                break;
                            case 4:
                                $status = '请假';
                                break;
                            default:
                                $status = '-';
                                break;
                        }
                        $total = $d->wage['separateAccounts']+$d->wage['otherSeparateAccounts']+$d->wage['attendance']*$d->dayValue-
                            $d->wage['materialOrder']-$d->wage['living']-$d->wage['loan']+$d->wage['bonus']-$d->wage['fine'];
                        $rowData = [$key+1,$d->projectName,$d->name,$d->jobNumber,$d->professionName,$status,$total,
                            $d->wage['separateAccounts'],$d->wage['otherSeparateAccounts'],$d->wage['attendance']*$d->dayValue,
                            $d->wage['materialOrder'],$d->wage['living'],$d->wage['loan'],$d->wage['bonus'],$d->wage['fine']];
                        array_push($cellData, $rowData);
                    }
                    $sheet->fromArray($cellData, null, 'A2', true, false);
                });
            })->store($extensions);
            $filename = $fileName . '.' . $extensions;
            $this->msg = $this->downloadURL($filename, $request);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 410301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 410302;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 410303;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Integer') {
                    $this->code = 410304;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    public function eSupplierOrder(Request $request){
        $rules = [
            'projectId' => 'required|integer',
            'time' => 'nullable|date_format:Y-m',
            'isPay' => 'nullable|integer|in:0,1',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型不正确',
            'time.date_format' => '月份格式不正确',
            'isPay.integer' => '付款状态参数类型错误',
            'isPay.in' => '付款状态参数值不正确',

        ];
        $input = $request->only(['projectId', 'isPay', 'search', 'time']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $supplierOrdersModel = new SupplierOrdersModel();
            $lists = $supplierOrdersModel->lists($input,true);

            $projectInfo = (new ProjectModel())->info(['id' => $input['projectId']]);
            $projectName = (isset($projectInfo['name'])&&!empty($projectInfo['name']))?$projectInfo['name']:'';
            $fileName = date('Y').'年度'.$projectName . '项目采购费用年度汇总表'.date('Ymd');
            $fileNameExcel = $this->fileName($fileName);
            $extensions = 'xlsx';
            Excel::create($fileNameExcel, function ($excel) use ($lists,$fileName) {
                $excel->sheet('sheet1', function ($sheet) use ($lists,$fileName) {
                    $sheet->setWidth([
                        "A" => "10",
                        "B" => "15",
                        "C" => "15",
                        "D" => "15",
                        "E" => "15",
                        "F" => "15",
                        "G" => "15",
                        "H" => "25",
                        "I" => "25",
                    ]);
                    $sheet->setCellValue('A1', $fileName);
                    $sheet->mergeCells('A1:I1');

                    $cellData = [["编号", "货单号", "供应商", "提货项目", "总价（元）", "送货日期", "付款方式", "是否还款", "记录时间"]];
                    foreach ($lists as $key => $d) {var_dump($d);
                        switch ($d->payType){
                            case 1:
                                $payType = '现金';
                                break;
                            case 2:
                                $payType = '记账';
                                break;
                            default:
                                $payType = '-';
                                break;
                        }
                        switch ($d->isPay){
                            case 1:
                                $isPay = '已付款';
                                break;
                            case 2:
                                $isPay = '未付款';
                                break;
                            default:
                                $isPay = '-';
                                break;
                        }
                        $rowData = [$key+1,$d->ordersn,$d->supplierName,$d->projectName,$d->totalPrice,$d->deliveryTime,$payType,$isPay,$d->createTime];
                        array_push($cellData, $rowData);
                    }
                    $sheet->fromArray($cellData, null, 'A2', true, false);
                });
            })->store($extensions);
            $filename = $fileName . '.' . $extensions;
            $this->msg = $this->downloadURL($filename, $request);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 410401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 410402;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'time') {
                if (key($failed['time']) == 'DateFormat') {
                    $this->code = 410403;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'isPay') {
                if (key($failed['isPay']) == 'Integer') {
                    $this->code = 410404;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['isPay']) == 'In') {
                    $this->code = 410405;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    public function eLoanLists(Request $request){
        $rules = [
            'projectId' => 'required|integer',
            'startTime' => 'nullable|date_format:Y-m-d',
            'endTime' => 'nullable|date_format:Y-m-d',
            'status' => 'nullable|integer|in:0,1',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型不正确',
            'startTime.date_format' => '日期格式不正确',
            'endTime.date_format' => '日期格式不正确',
            'status.integer' => '付款状态参数类型错误',
            'status.in' => '付款状态参数值不正确',
        ];
        $input = $request->only(['projectId', 'startTime', 'endTime', 'status', 'search']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeLoanModel = new EmployeeLoanModel();
            $lists = $employeeLoanModel->lists($input,true);

            $projectInfo = (new ProjectModel())->info(['id' => $input['projectId']]);
            $projectName = (isset($projectInfo['name'])&&!empty($projectInfo['name']))?$projectInfo['name']:'';
            $fileName = date('Y').'年度'.$projectName . '项目借款费用记录表'.date('Ymd');
            $fileNameExcel = $this->fileName($fileName);
            $extensions = 'xlsx';
            Excel::create($fileNameExcel, function ($excel) use ($lists,$fileName) {
                $excel->sheet('sheet1', function ($sheet) use ($lists,$fileName) {
                    $sheet->setWidth([
                        "A" => "10",
                        "B" => "15",
                        "C" => "15",
                        "D" => "15",
                        "E" => "15",
                        "F" => "15",
                        "G" => "15",
                        "H" => "25",
                    ]);
                    $sheet->setCellValue('A1', $fileName);
                    $sheet->mergeCells('A1:H1');

                    $cellData = [["编号", "姓名", "工号", "借款金额", "借款时间", "记录时间", "审核状态", "备注"]];
                    foreach ($lists as $key => $d) {
                        switch ($d->status){
                            case 1:
                                $status = '审核通过';
                                break;
                            case 2:
                                $status = '审核驳回';
                                break;
                            default:
                                $status = '待审核';
                                break;
                        }
                        $rowData = [$key+1,$d->employeeName,$d->jobNumber,$d->account,$d->loanTime,$d->createTime,$status,''];
                        array_push($cellData, $rowData);
                    }
                    $sheet->fromArray($cellData, null, 'A2', true, false);
                });
            })->store($extensions);
            $filename = $fileName . '.' . $extensions;
            $this->msg = $this->downloadURL($filename, $request);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 410501;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 410502;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'startTime') {
                if (key($failed['startTime']) == 'DateFormat') {
                    $this->code = 410503;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'endTime') {
                if (key($failed['endTime']) == 'DateFormat') {
                    $this->code = 410504;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Integer') {
                    $this->code = 410505;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'In') {
                    $this->code = 410506;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    public function eLivingLists(Request $request){
        $rules = [
            'projectId' => 'required|integer',
            'startTime' => 'nullable|date_format:Y-m-d',
            'endTime' => 'nullable|date_format:Y-m-d',
            'status' => 'nullable|integer|in:0,1,2',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型不正确',
            'startTime.date_format' => '日期格式不正确',
            'endTime.date_format' => '日期格式不正确',
            'status.integer' => '审批状态参数类型错误',
            'status.in' => '审批状态参数值不正确',
        ];
        $input = $request->only(['projectId', 'startTime', 'endTime', 'status', 'search']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeLivingModel = new EmployeeLivingModel();
            $lists = $employeeLivingModel->lists($input,true);

            $projectInfo = (new ProjectModel())->info(['id' => $input['projectId']]);
            $projectName = (isset($projectInfo['name'])&&!empty($projectInfo['name']))?$projectInfo['name']:'';
            $fileName = date('Y').'年度'.$projectName . '项目生活费用记录表'.date('Ymd');
            $fileNameExcel = $this->fileName($fileName);
            $extensions = 'xlsx';
            Excel::create($fileNameExcel, function ($excel) use ($lists,$fileName) {
                $excel->sheet('sheet1', function ($sheet) use ($lists,$fileName) {
                    $sheet->setWidth([
                        "A" => "10",
                        "B" => "15",
                        "C" => "15",
                        "D" => "15",
                        "E" => "15",
                        "F" => "15",
                        "G" => "15",
                        "H" => "25",
                        "I" => "25",
                    ]);
                    $sheet->setCellValue('A1', $fileName);
                    $sheet->mergeCells('A1:I1');

                    $cellData = [["编号", "姓名", "工号", "类型", "金额", "操作时间", "记录时间", "审核状态","备注"]];
                    foreach ($lists as $key => $d) {
                        switch ($d->status){
                            case 1:
                                $status = '审核通过';
                                break;
                            case 2:
                                $status = '审核驳回';
                                break;
                            default:
                                $status = '待审核';
                                break;
                        }
                        switch ($d->type){
                            case 1:
                                $type = '充值';
                                break;
                            case 2:
                                $type = '退费';
                                break;
                            default:
                                $type = '-';
                                break;
                        }
                        $rowData = [$key+1,$d->employeeName,$d->jobNumber,$type,$d->account,$d->livingTime,$d->createTime,$status,''];
                        array_push($cellData, $rowData);
                    }
                    $sheet->fromArray($cellData, null, 'A2', true, false);
                });
            })->store($extensions);
            $filename = $fileName . '.' . $extensions;
            $this->msg = $this->downloadURL($filename, $request);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 410601;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 410602;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'startTime') {
                if (key($failed['startTime']) == 'DateFormat') {
                    $this->code = 410603;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'endTime') {
                if (key($failed['endTime']) == 'DateFormat') {
                    $this->code = 410604;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Integer') {
                    $this->code = 410605;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'In') {
                    $this->code = 410606;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}