<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/24
 * Time: 14:44
 */

namespace App\Http\Controllers\Project;


use App\Http\Controllers\Controller;
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
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'professionId.required' => '获取工种参数失败',
            'professionId.integer' => '工种参数类型错误',
        ];
        $input = $request->only(['projectId','professionId','search']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupModel = new ProjectGroupModel();
            $this->data = $projectGroupModel->lists($input);
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
            }elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 430103;
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
    public function add(Request $request){
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
        $input = $request->only(['projectId','name','professionId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupModel = new ProjectGroupModel();
            $info = $projectGroupModel->checkRepeat(['projectId'=>$input['projectId'],'name'=>$input['name'],'professionId'=>$input['professionId']]);
            if (empty($info)){
                $projectGroupModel->insert($input);
            }else{
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
            }elseif(key($failed) == 'name'){
                if (key($failed['name']) == 'Required') {
                    $this->code = 430203;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'professionId') {
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
    public function info(Request $request){
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取项目参数失败',
            'id.integer' => '项目参数类型错误',
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
    public function edit(Request $request){
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
        $input = $request->only(['id','name','professionId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupModel = new ProjectGroupModel();
            $info = $projectGroupModel->info(['id'=>$input['id']]);
            $info = $projectGroupModel->checkRepeat(['projectId'=>$info['projectId'],'name'=>$input['name'],'professionId'=>$input['professionId'],$input['id']]);
            if (empty($info)){
                $projectGroupModel->update($input);
            }else{
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
            }elseif(key($failed) == 'name'){
                if (key($failed['name']) == 'Required') {
                    $this->code = 430403;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'professionId') {
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
    public function editStatus(Request $request){
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
        $input = $request->only(['id','status']);
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
            }elseif (key($failed) == 'status') {
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
    public function memberLists(Request $request){
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
    public function addMember(Request $request){
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
        $input = $request->only(['projectId','employeeId','groupId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupMembersModel = new ProjectGroupMembersModel();
            $result = $projectGroupMembersModel->isInGroup($input);
            if (!$result){
                $info = $projectGroupMembersModel->info($input);
                if (empty($info)){
                    $projectGroupMembersModel->insert($input);
                }else{
                    if ($info['isDel'] == 1){
                        $projectGroupMembersModel->update(['id'=>$info['id']],['isDel'=>0]);
                    }
                }
            }else{
                $this->code = 430707;
                $this->msg = '工人已经属于班组'.$result['name'];
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
            }elseif (key($failed) == 'employeeId') {
                if (key($failed['employeeId']) == 'Required') {
                    $this->code = 430703;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['employeeId']) == 'Integer') {
                    $this->code = 430704;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'groupId') {
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
    public function delMember(Request $request){
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
    public function setLeader(Request $request){
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
            $projectGroupMembersModel->update(['groupId'=>$info['groupId']],['isLeader'=>0]);
            //设置新班组长
            $projectGroupMembersModel->update(['id'=>$input['id']],['isLeader'=>1]);
            //更新班组中的班组长字段
            $projectGroupModel->updateIsLeader(['groupId'=>$info['groupId'],'groupLeader' => $info['employeeId']]);
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
}