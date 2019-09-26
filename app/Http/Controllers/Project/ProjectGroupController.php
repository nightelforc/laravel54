<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/24
 * Time: 14:44
 */

namespace App\Http\Controllers\Project;


use App\Http\Controllers\Auth\ApprovalController;
use App\Http\Controllers\Controller;
use App\Http\Model\EmployeeModel;
use App\Http\Model\ProjectGroupMembersModel;
use App\Http\Model\ProjectGroupModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('yucheng');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'professionId' => 'nullable|integer',
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'professionId.required' => '获取工种参数失败',
            'professionId.integer' => '工种参数类型错误',
            'length.required' => '获取记录条数失败',
            'length.integer' => '记录条数参数类型错误',
            'length.in' => '记录条数参数值不正确',
            'start.required' => '获取起始记录位置失败',
            'start.integer' => '页码参数类型错误',
            'start.min' => '页码参数值不小于:min',
        ];
        $input = $request->only(['projectId', 'professionId', 'search', 'draw', 'length', 'start']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupModel = new ProjectGroupModel();
            $lists = $projectGroupModel->lists($input);
            $countLists = $projectGroupModel->countLists($input);
            $this->data = [
                "draw" => $input['draw'],
                "data" => $lists,
                "recordsFiltered" => $countLists,
                "recordsTotal" => $countLists,
            ];
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 430101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 430102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 430103;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 430104;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 430105;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 430106;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 430107;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 430108;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 430109;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 430110;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 430111;
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
    public function add(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'name' => 'required',
            'professionId' => 'required|integer',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'name.required' => '请填写班组名称',
            'professionId.required' => '请选择工种',
            'professionId.integer' => '工种参数类型错误',
        ];
        $input = $request->only(['projectId', 'name', 'professionId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupModel = new ProjectGroupModel();
            $info = $projectGroupModel->checkRepeat(['projectId' => $input['projectId'], 'name' => $input['name'], 'professionId' => $input['professionId']]);
            if (empty($info)) {
                $projectGroupModel->insert($input);
            } else {
                $this->code = 430206;
                $this->msg = '相同工种下，班组名称不能重复命名';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 430201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 430202;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 430203;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Required') {
                    $this->code = 430204;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 430205;
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
    public function info(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取班组参数失败',
            'id.integer' => '班组参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupModel = new ProjectGroupModel();
            $this->data = $projectGroupModel->info($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 430301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 430302;
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
    public function edit(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'name' => 'required',
            'professionId' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取项目参数失败',
            'id.integer' => '项目参数类型错误',
            'name.required' => '请填写班组名称',
            'professionId.required' => '请选择工种',
            'professionId.integer' => '工种参数类型错误',
        ];
        $input = $request->only(['id', 'name', 'professionId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupModel = new ProjectGroupModel();
            $info = $projectGroupModel->info(['id' => $input['id']]);
            $info = $projectGroupModel->checkRepeat(['projectId' => $info['projectId'], 'name' => $input['name'], 'professionId' => $input['professionId']], $input['id']);
            if (empty($info)) {
                $projectGroupModel->update($input);
            } else {
                $this->code = 430406;
                $this->msg = '相同工种下，班组名称不能重复命名';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 430401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 430402;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 430403;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Required') {
                    $this->code = 430404;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 430405;
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
    public function editStatus(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'status' => 'required|integer|in:1,2',
        ];
        $message = [
            'id.required' => '获取班组参数失败',
            'id.integer' => '班组参数类型错误',
            'status.required' => '获取班组状态参数失败',
            'status.integer' => '班组状态参数类型错误',
            'status.in' => '班组状态参数不正确',
        ];
        $input = $request->only(['id', 'status']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupModel = new ProjectGroupModel();
            $projectGroupModel->updateStatus($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 430501;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 430502;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Required') {
                    $this->code = 430503;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'Integer') {
                    $this->code = 430504;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'In') {
                    $this->code = 430505;
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
    public function memberLists(Request $request)
    {
        $rules = [
            'groupId' => 'required|integer',
        ];
        $message = [
            'groupId.required' => '获取班组参数失败',
            'groupId.integer' => '班组参数类型错误',
        ];
        $input = $request->only(['groupId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupMembersModel = new ProjectGroupMembersModel();
            $this->data = $projectGroupMembersModel->lists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'groupId') {
                if (key($failed['groupId']) == 'Required') {
                    $this->code = 430601;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['groupId']) == 'Integer') {
                    $this->code = 430602;
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
    public function addMember(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'employeeId' => 'required|integer',
            'groupId' => 'required|integer',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'employeeId.required' => '获取工人参数失败',
            'employeeId.integer' => '工人参数类型错误',
            'groupId.required' => '获取班组参数失败',
            'groupId.integer' => '班组参数类型错误',
        ];
        $input = $request->only(['projectId', 'employeeId', 'groupId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupMembersModel = new ProjectGroupMembersModel();
            $result = $projectGroupMembersModel->isInGroup($input);
            if (!$result) {
                $info = $projectGroupMembersModel->info($input);
                if (empty($info)) {
                    $projectGroupMembersModel->insert($input);
                } else {
                    if ($info['isDel'] == 1) {
                        $projectGroupMembersModel->update(['id' => $info['id']], ['isDel' => 0]);
                    }
                }
                $employeeModel = new EmployeeModel();
                $employeeModel->update($input['employeeId'], ['groupId' => $input['groupId']]);
            } else {
                $this->code = 430707;
                $this->msg = '工人已经属于班组' . $result['name'];
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 430701;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 430702;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'employeeId') {
                if (key($failed['employeeId']) == 'Required') {
                    $this->code = 430703;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['employeeId']) == 'Integer') {
                    $this->code = 430704;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'groupId') {
                if (key($failed['groupId']) == 'Required') {
                    $this->code = 430705;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['groupId']) == 'Integer') {
                    $this->code = 430706;
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
    public function delMember(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取班组成员参数失败',
            'id.integer' => '班组成员参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupMembersModel = new ProjectGroupMembersModel();
            $projectGroupMembersModel->delMember($input);
            $info = $projectGroupMembersModel->info(['id' => $input['id']]);
            if (!empty($info)) {
                $employeeModel = new EmployeeModel();
                $employeeModel->update($info['employeeId'], ['groupId' => null]);
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 430801;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 430802;
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
    public function setLeader(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取班组成员参数失败',
            'id.integer' => '班组成员参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupModel = new ProjectGroupModel();
            $projectGroupMembersModel = new ProjectGroupMembersModel();
            //获取组员信息
            $info = $projectGroupMembersModel->info($input);
            //清空原有组员的班组长状态
            $projectGroupMembersModel->update(['groupId' => $info['groupId']], ['isLeader' => 0]);
            //设置新班组长
            $projectGroupMembersModel->update(['id' => $input['id']], ['isLeader' => 1]);
            //更新班组中的班组长字段
            $projectGroupModel->updateIsLeader(['groupId' => $info['groupId'], 'groupLeader' => $info['employeeId']]);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 430901;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 430902;
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
    public function delete(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取班组参数失败',
            'id.integer' => '班组参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupModel = new ProjectGroupModel();
            $result = $projectGroupModel->delete($input);
            if (!is_bool($result)){
                switch ($result) {
                    case 'separateAccounts':
                        $this->code = 431003;
                        $this->msg = '该班组已有班组人员记录分账';
                        break;
                    case 'assignment':
                        $this->code = 431004;
                        $this->msg = '该班组已记录施工项分账';
                        break;
                    case 'members':
                        $this->code = 431005;
                        $this->msg = '该班组已有班组人员';
                        break;
                    default:
                        break;
                }
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 431001;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 431002;
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
    public function changeProject(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'projectId' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取班组参数失败',
            'id.integer' => '班组参数类型错误',
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
        ];
        $input = $request->only(['id', 'projectId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupModel = new ProjectGroupModel();
            $projectGroupModel->changeProject($input);

        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 431101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 431102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 431103;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 431104;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}