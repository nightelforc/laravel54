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
use App\Http\Model\ProjectAreaModel;
use App\Http\Model\ProjectGroupAssignmentModel;
use App\Http\Model\ProjectGroupModel;
use App\Http\Model\ProjectGroupSeparateAccountsModel;
use App\Http\Model\ProjectModel;
use App\Http\Model\ProjectSectionModel;
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
}