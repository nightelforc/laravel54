<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/12
 * Time: 9:20
 */

namespace App\Http\Controllers\Company;


use App\Http\Controllers\Controller;
use App\Http\Model\EmployeeExperienceModel;
use App\Http\Model\EmployeeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'professionId' => 'nullable|integer',
            'status' => 'nullable|integer',
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'professionId.integer' => '工种参数类型错误',
            'status.integer' => '工作状态参数类型错误',
            'length.required' => '获取记录条数失败',
            'length.integer' => '记录条数参数类型错误',
            'length.in' => '记录条数参数值不正确',
            'start.required' => '获取起始记录位置失败',
            'start.integer' => '页码参数类型错误',
            'start.min' => '页码参数值不小于:min',
        ];
        $input = $request->all();
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeModel = new EmployeeModel();
            $lists = $employeeModel->lists($input,$employeeModel->employeeStatus);
            $countLists = $employeeModel->countLists($input,$employeeModel->employeeStatus);
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
                    $this->code = 310101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 310102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 310103;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Integer') {
                    $this->code = 310104;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 310105;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 310106;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 310107;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 310108;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 310109;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 310110;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 310111;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 310112;
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
            'idcard'=>'required|size:18',
            'jobNumber' => 'required',
            'gender'=>'nullable|integer|in:1,2',
            'age'=>'nullable|integer',
            'professionId' => 'required|integer',
            'isContract'=>'nullable|integer|in:0,1',
            'contractTime'=>'nullable|date_format:Y-m-d',
            'isEdu'=>'nullable|integer|in:0,1',
            'eduTime'=>'nullable|date_format:Y-m-d',
        ];
        $message = [
            'projectId.required' => '获取工人参数失败',
            'projectId.integer' => '工人参数类型错误',
            'name.required' => '请填写姓名',
            'idcard.required' => '请填写身份证号',
            'idcard.size' => '身份证号位数不正确',
            'jobNumber.required'=>'请填写工号',
            'gender.integer'=>'性别参数类型错误',
            'gender.in'=>'性别参数不正确',
            'age.integer'=>'年龄参数类型错误',
            'professionId.required' => '请选择工种',
            'professionId.integer' => '工种参数类型错误',
            'isContract.integer'=>'是否签订合同数据类型错误',
            'isContract.in'=>'是否签订合同数据不正确',
            'contractTime.date_format'=>'签订合同时间格式不正确',
            'isEdu.integer'=>'是否签订合同数据类型错误',
            'isEdu.in'=>'是否签订合同数据不正确',
            'eduTime.date_format'=>'签订合同时间格式不正确',
        ];
        $input = $request->only(['projectId', 'name', 'idcard', 'jobNumber','gender','age','nation','phone','bankNumber','homeAddress','professionId','isContract','contractTime','isEdu','eduTime']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $imagePath = '';
            if ($request->hasFile('image')){
                $image = $request->file('image');
                $fileExtension = strtolower($request->image->extension());
                if (in_array($fileExtension,['jpg','jpeg','png','bmp'])){
                    $newName = time() . mt_rand(100,999);
                    if($image->move('./upload',$newName.'.'.$fileExtension)){
                        $imagePath =  '/upload/'.$newName.'.'.$fileExtension;   //返回一个地址
                    }
                }else{
                    $this->code = 310218;
                    $this->msg = '上传图片格式不正确';
                }
            }
            $input['image'] = $imagePath;
            $employeeModel = new EmployeeModel();
            $employeeExperienceModel = new EmployeeExperienceModel();
            if(!empty($input['contractTime'])){
                $input['isContract'] = 1;
            }
            if(!empty($input['eduTime'])){
                $input['isEdu'] = 1;
            }
            $insertId = $employeeModel->insert($input);
            if (is_int($insertId)){
                $data = [
                    'employeeId'=>$insertId,
                    'projectId'=>$input['projectId'],
                    'inTime'=>date('Y-m-d H:i:s'),
                ];
                $employeeExperienceModel->insert($data);
            }else{
                $this->code = 310219;
                $this->msg = '保存失败';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 310201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 310202;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 310203;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'idcard') {
                if (key($failed['idcard']) == 'Required') {
                    $this->code = 310204;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['idcard']) == 'Size') {
                    $this->code = 310205;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'jobNumber') {
                if (key($failed['jobNumber']) == 'Required') {
                    $this->code = 310206;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'gender') {
                if (key($failed['gender']) == 'Integer') {
                    $this->code = 310207;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['gender']) == 'In') {
                    $this->code = 310208;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'age') {
                if (key($failed['age']) == 'Integer') {
                    $this->code = 310209;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Required') {
                    $this->code = 310210;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 310211;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'isContract') {
                if (key($failed['isContract']) == 'Integer') {
                    $this->code = 310212;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['isContract']) == 'In') {
                    $this->code = 310213;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'contractTime') {
                if (key($failed['contractTime']) == 'DateFormat') {
                    $this->code = 310214;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'isEdu') {
                if (key($failed['isEdu']) == 'Integer') {
                    $this->code = 310215;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['isEdu']) == 'In') {
                    $this->code = 310216;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'eduTime') {
                if (key($failed['eduTime']) == 'DateFormat') {
                    $this->code = 310217;
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
            'projectId' => 'required|integer',
            'name' => 'required',
            'idcard'=>'required|size:18',
            'jobNumber' => 'required',
            'gender'=>'nullable|integer|in:0,1,2',
            'age'=>'nullable|integer',
            'professionId' => 'required|integer',
            'isContract'=>'nullable|integer|in:0,1',
            'contractTime'=>'nullable|date_format:Y-m-d',
            'isEdu'=>'nullable|integer|in:0,1',
            'eduTime'=>'nullable|date_format:Y-m-d',
        ];
        $message = [
            'id.required' => '获取工人参数失败',
            'id.integer' => '工人参数类型错误',
            'projectId.required' => '获取工人参数失败',
            'projectId.integer' => '工人参数类型错误',
            'name.required' => '请填写姓名',
            'idcard.required' => '请填写身份证号',
            'idcard.size' => '身份证号位数不正确',
            'jobNumber.required'=>'请填写工号',
            'gender.integer'=>'性别参数类型错误',
            'gender.in'=>'性别参数不正确',
            'age.integer'=>'年龄参数类型错误',
            'professionId.required' => '请选择工种',
            'professionId.integer' => '工种参数类型错误',
            'isContract.integer'=>'是否签订合同数据类型错误',
            'isContract.in'=>'是否签订合同数据不正确',
            'contractTime.date_format'=>'签订合同时间格式不正确',
            'isEdu.integer'=>'是否签订合同数据类型错误',
            'isEdu.in'=>'是否签订合同数据不正确',
            'eduTime.date_format'=>'签订合同时间格式不正确',
        ];
        $input = $request->only(['id','projectId', 'name', 'idcard', 'jobNumber','gender','age','nation','phone','bankNumber','homeAddress','professionId','isContract','contractTime','isEdu','eduTime']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $imagePath = '';
            if ($request->hasFile('image')){
                $image = $request->file('image');
                $fileExtension = strtolower($request->image->extension());
                if (in_array($fileExtension,['jpg','jpeg','png','bmp'])){
                    $newName = time() . mt_rand(100,999);
                    if($image->move('./upload',$newName.'.'.$fileExtension)){
                        $imagePath =  '/upload/'.$newName.'.'.$fileExtension;   //返回一个地址
                    }
                }else{
                    $this->code = 310312;
                    $this->msg = '上传图片格式不正确';
                }
            }
            $input['image'] = $imagePath;
            $employeeModel = new EmployeeModel();
            $id = $input['id'];
            $info = $employeeModel->info(['id'=>$id]);
            if (!empty($info['image']) &&  fileExists('.'.$info['image'])){
                unlink('.'.$info['image']);
            }

            if(!empty($input['contractTime'])){
                $input['isContract'] = 1;
            }
            if(!empty($input['eduTime'])){
                $input['isEdu'] = 1;
            }
            $employeeModel->update($id,$input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 310301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 310302;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 310301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 310302;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 310303;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'idcard') {
                if (key($failed['idcard']) == 'Required') {
                    $this->code = 310304;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['idcard']) == 'Size') {
                    $this->code = 310305;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'jobNumber') {
                if (key($failed['jobNumber']) == 'Required') {
                    $this->code = 310306;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'gender') {
                if (key($failed['gender']) == 'Integer') {
                    $this->code = 310307;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['gender']) == 'In') {
                    $this->code = 310308;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'age') {
                if (key($failed['age']) == 'Integer') {
                    $this->code = 310309;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Required') {
                    $this->code = 310310;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 310311;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'isContract') {
                if (key($failed['isContract']) == 'Integer') {
                    $this->code = 310312;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['isContract']) == 'In') {
                    $this->code = 310313;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'contractTime') {
                if (key($failed['contractTime']) == 'DateFormat') {
                    $this->code = 310314;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'isEdu') {
                if (key($failed['isEdu']) == 'Integer') {
                    $this->code = 310315;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['isEdu']) == 'In') {
                    $this->code = 310316;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'eduTime') {
                if (key($failed['eduTime']) == 'DateFormat') {
                    $this->code = 310317;
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
    public function dayValueLists(Request $request){
        $rules = [
            'projectId' => 'nullable|integer',
            'professionId' => 'nullable|integer',
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'projectId.integer' => '项目参数类型错误',
            'professionId.integer' => '工种参数类型错误',
            'length.required' => '获取记录条数失败',
            'length.integer' => '记录条数参数类型错误',
            'length.in' => '记录条数参数值不正确',
            'start.required' => '获取起始记录位置失败',
            'start.integer' => '页码参数类型错误',
            'start.min' => '页码参数值不小于:min',
        ];
        $input = $request->only(['projectId', 'professionId','search','draw','length','start']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeModel = new EmployeeModel();
            $lists = $employeeModel->dayValueLists($input);
            $countLists = $employeeModel->countDayValueLists($input);
            $this->data = [
                "draw"=>$input['draw'],
                "data"=>$lists,
                "recordsFiltered"=>$countLists,
                "recordsTotal"=>$countLists,
            ];
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 310401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 310402;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Required') {
                    $this->code = 310403;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 310404;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 310405;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 310406;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 310407;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 310408;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 310409;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 310410;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 310411;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 310412;
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
    public function editDayValue(Request $request){
        $rules = [
            'employeeId' => 'required|integer',
            'dayValue' => 'required|integer',
        ];
        $message = [
            'employeeId.required' => '获取工人参数失败',
            'employeeId.integer' => '工人参数类型错误',
            'dayValue.required' => '请填写日工值',
            'dayValue.integer' => '日工值参数类型错误',
        ];
        $input = $request->only(['employeeId', 'dayValue']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeModel = new EmployeeModel();
            $info = $employeeModel->info(['id'=>$input['employeeId']]);
            if ($info['hasAttendance'] == 1){
                $employeeModel->update($input['employeeId'],['dayValue'=>$input['dayValue']]);
            }else{
                $this->code = 310505;
                $this->msg = '该工人未录入考勤，不能修改日工值';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'employeeId') {
                if (key($failed['employeeId']) == 'Required') {
                    $this->code = 310501;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['employeeId']) == 'Integer') {
                    $this->code = 310502;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'dayValue') {
                if (key($failed['dayValue']) == 'Required') {
                    $this->code = 310503;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['dayValue']) == 'Integer') {
                    $this->code = 310504;
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
    public function batchEditDayValue(Request $request){
        $input = $request->only(['data']);
        $rules = [
            'employeeId' => 'required|integer',
            'dayValue' => 'required|integer',
        ];
        $i = 0;
        foreach ($input['data'] as $d){
            $validator = Validator::make($d, $rules);
            if ($validator->passes()) {
                $employeeModel = new EmployeeModel();
                $info = $employeeModel->info(['id'=>$d['employeeId']]);
                if ($info['hasAttendance'] == 1){
                    $employeeModel->update($d['employeeId'],['dayValue'=>$d['dayValue']]);
                    $i++;
                }else{
                    continue;
                }
            }else{
                continue;
            }
        }
        $this->msg = '共'.$i.'个日工值保存成功';
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request){
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取工人参数失败',
            'id.integer' => '工人参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $employeeModel = new EmployeeModel();
            $result = $employeeModel->delete($input['id']);
            if (is_string($result)){
                $this->code = 310703;
                $this->msg = '删除失败，该工人已经参与考勤或分账等';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 310701;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 310702;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}