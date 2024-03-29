<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/19
 * Time: 13:41
 */

namespace App\Http\Controllers\Setting;


use App\Http\Controllers\Controller;
use App\Http\Model\ProfessionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfessionController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function lists()
    {
        $professionModel = new ProfessionModel();
        $this->data = $professionModel->lists();
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
            'id.required' => '获取工种参数失败',
            'id.integer' => '工种参数类型错误',
        ];
        $input = $request->only('id');
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $professionModel = new ProfessionModel();
            $this->data = $professionModel->info($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 240101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 240102;
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
            'name' => 'required',
            'order' => 'integer',
        ];
        $message = [
            'name.required' => '请输入工种名称',
            'order.integer' => '工种排序数据类型不正确',
        ];
        $input = $request->only(['name','order']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $professionModel = new ProfessionModel();
            $info = $professionModel->checkRepeat(['name'=>$input['name']]);
            if (empty($info)){
                $result = $professionModel->insert($input);
                if (!$result) {
                    $this->code = 240204;
                    $this->msg = '保存失败，请稍后重试';
                }
            }else{
                $this->code = 240203;
                $this->msg = '请勿重复建立相同名称的工种';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 240201;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'order') {
                if (key($failed['order']) == 'Integer') {
                    $this->code = 240202;
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
            'order' => 'integer',
        ];
        $message = [
            'id.required' => '获取工种参数失败',
            'id.integer' => '工种参数类型错误',
            'name.required' => '请输入工种名称',
            'order.integer' => '工种排序数据类型不正确',
        ];
        $input = $request->only(['id','name','order']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $professionModel = new ProfessionModel();
            $info = $professionModel->checkRepeat(['name'=>$input['name']],$input['id']);
            if (empty($info)){
                $professionModel->update($input);
            }else{
                $this->code = 240305;
                $this->msg = '请勿重复建立相同名称的工种';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 240301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 240302;
                    $this->msg = $validator->errors()->first();
                }
            }
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 240303;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'order') {
                if (key($failed['order']) == 'Integer') {
                    $this->code = 240304;
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
            'status' => 'required|integer|in:0,1',
        ];
        $message = [
            'id.required' => '获取工种参数失败',
            'id.integer' => '工种参数类型错误',
            'status.required' => '未获取到状态',
            'status.integer' => '工种状态参数类型错误',
            'status.in' => '工种状态参数值不正确',
        ];
        $input = $request->all();
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $professionModel = new ProfessionModel();
            $professionModel->updateStatus($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 240401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 240402;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Required') {
                    $this->code = 240401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'Integer') {
                    $this->code = 240402;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'In') {
                    $this->code = 240405;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}