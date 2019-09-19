<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/15
 * Time: 9:38
 */

namespace App\Http\Controllers\Excel;

use App\Http\Model\EmployeeExperienceModel;
use App\Http\Model\EmployeeModel;
use App\Http\Model\ProfessionModel;
use App\Http\Model\ProjectModel;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ImportHandleController extends Controller
{
    private $template = [
        'employeeLists' => ['name', 'project', 'profession', 'jobNumber', 'idcard', 'gender', 'age', 'nation', 'phone', 'homeAddress', 'bankNumber','isContract','contractTime','isEdu','eduTime']
    ];

    /**
     * @param $methodName
     * @return mixed
     */
    private function getTemplate($methodName)
    {
        $name = $this->getMethodName($methodName);
        return $this->template[$name];
    }

    /**
     * 批量导入
     *
     * @param $file
     * @return array
     */
    public function employeeLists($file)
    {
        $template = $this->getTemplate(__METHOD__);
        $excel = Excel::load($file);
        //Excel 文件数据
        $excelData = $excel->getSheet()->toArray();

        //未成功导入的数据
        $errorData = [];

        $employeeModel = new EmployeeModel();
        $employeeExperienceModel = new EmployeeExperienceModel();
        //遍历excel的数据
        foreach ($excelData as $key => $d) {
            //跳过表头
            if ($key == 0) {
                continue;
            }

            $insertData = [];
            $insert = true;
            foreach ($template as $k => $t) {
                $$t = $d[$k];
                switch ($t) {
                    case 'gender':
                        if ($$t == 1 || preg_match('/\x{7537}/u', $$t)) {
                            $insertData[$t] = 1;
                        } elseif ($$t == 2 || preg_match('/\x{5973}/u', $$t)) {
                            $insertData[$t] = 2;
                        } else {
                            $insertData[$t] = 0;
                        }
                        break;
                    case 'age':
                        $insertData[$t] = intval($$t);
                        break;
                    case 'jobNumber':
                        $result = $this->checkJobNumber($$t);
                        if ($result) {
                            $insertData[$t] = $$t;
                        } else {
                            $insert = false;
                            break 2;
                        }
                        break;
                    case 'project':
                        $result = $this->checkProject($$t);
                        if ($result) {
                            $insertData[$t . 'Id'] = $result;
                        } else {
                            $insert = false;
                            break 2;
                        }
                        break;
                    case 'profession':
                        $result = $this->checkProfession($$t);
                        if ($result) {
                            $insertData[$t . 'Id'] = $result;
                        } else {
                            $insert = false;
                            break 2;
                        }
                        break;
                    case 'isContract':
                    case 'isEdu':
                        if ($$t == 1 || preg_match('/\x{662F}/u', $$t)) {
                            $insertData[$t] = 1;
                        } elseif ($$t == 0 || preg_match('/\x{5426}/u', $$t)) {
                            $insertData[$t] = 0;
                        }
                        break;
                    default:
                        $insertData[$t] = $$t;
                        break;
                }
            }

            if ($insert) {
                //插入员工数据
                $insertId = $employeeModel->insert($insertData);
                $data = [
                    'employeeId' => $insertId,
                    'projectId' => $insertData['projectId'],
                    'inTime' => date('Y-m-d H:i:s'),
                ];
                $employeeExperienceModel->insert($data);
            } else {
                //保存插入错误的数据
                $errorData[] = $d;
            }
        }

        return $errorData;
    }

    /**
     * 检查工人工号是否重复
     *
     * @param $param
     * @return bool
     */
    private function checkJobNumber($param)
    {
        $result = (new EmployeeModel())->checkJobNumber($param);
        if (count($result) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证项目名称，并获取id
     *
     * @param $param
     * @return bool
     */
    private function checkProject($param)
    {
        $result = (new ProjectModel())->info(['name' => $param]);
        if (!empty($result)) {
            return $result['id'];
        } else {
            return false;
        }
    }

    /**
     * 验证工种名称，并获取id
     *
     * @param $param
     * @return bool
     */
    private function checkProfession($param)
    {
        $result = (new ProfessionModel())->info(['name' => $param]);
        if (!empty($result)) {
            return $result['id'];
        } else {
            return false;
        }
    }
}