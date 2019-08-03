<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/31
 * Time: 20:07
 */

namespace App\Http\Controllers\Project;


use App\Http\Controllers\Controller;
use App\Http\Model\MaterialModel;
use App\Http\Model\MaterialSpecModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaterialController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request){
        $rules = [
            'limit' => 'nullable|integer|in:10,20,50',
            'page' => 'nullable|integer|min:1',
        ];
        $message = [
            'limit.integer' => '记录条数参数类型错误',
            'limit.in' => '记录条数参数值不正确',
            'page.integer' => '页码参数类型错误',
            'page.min' => '页码参数值不小于:min',
        ];
        $input = $request->only(['search','limit','page']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $materialModel = new MaterialModel();
            $this->data = $materialModel->lists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'limit') {
                if (key($failed['limit']) == 'Integer') {
                    $this->code = 450101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['limit']) == 'In') {
                    $this->code = 450102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'page') {
                if (key($failed['page']) == 'Integer') {
                    $this->code = 450103;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['page']) == 'Min') {
                    $this->code = 450104;
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
            'name' => 'required',
        ];
        $message = [
            'name.required' => '请填写材料名称',
        ];
        $input = $request->only(['name']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $materialModel = new MaterialModel();
            $materialModel->add($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 450201;
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
            'id.required' => '获取材料参数失败',
            'id.integer' => '材料参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $materialModel = new MaterialModel();
            $materialModel->info($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 450301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 450302;
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
            'id'=>'required|integer',
            'name' => 'required',
        ];
        $message = [
            'id.required' => '获取材料参数类型失败',
            'id.integer' => '材料参数类型错误',
            'name.required' => '请填写材料名称',
        ];
        $input = $request->only(['id','name']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $materialModel = new MaterialModel();
            $id = $input['id'];
            unset($input['id']);
            $materialModel->update($id,$input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 450401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 450402;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 450403;
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
            'id'=>'required|integer',
            'status' => 'required|integer|in:0,1',
        ];
        $message = [
            'id.required' => '获取材料参数类型失败',
            'id.integer' => '材料参数类型错误',
            'status.required' => '获取材料状态参数失败',
            'status.integer' => '材料状态参数类型错误',
            'status.in' => '材料状态参数不正确',
        ];
        $input = $request->only(['id','status']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $materialModel = new MaterialModel();
            $id = $input['id'];
            unset($input['id']);
            $materialModel->update($id,$input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 450501;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 450502;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Required') {
                    $this->code = 450503;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'Integer') {
                    $this->code = 450504;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'In') {
                    $this->code = 450505;
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
    public function specLists(Request $request){
        $rules = [
            'materialId' => 'required|integer',
            'limit' => 'nullable|integer|in:10,20,50',
            'page' => 'nullable|integer|min:1',
        ];
        $message = [
            'materialId.required' => '获取材料参数失败',
            'materialId.integer' => '材料参数类型错误',
            'limit.integer' => '记录条数参数类型错误',
            'limit.in' => '记录条数参数值不正确',
            'page.integer' => '页码参数类型错误',
            'page.min' => '页码参数值不小于:min',
        ];
        $input = $request->only(['materialId','limit','page']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $materialSpecModel = new MaterialSpecModel();
            $this->data = $materialSpecModel->lists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 450601;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 450602;
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
    public function addSpec(Request $request){
        $rules = [
            'spec' => 'required',
            'brand' => 'required',
        ];
        $message = [
            'spec.required' => '请填写规格',
            'brand.integer' => '请填写品牌',
        ];
        $input = $request->only(['spec','brand']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $materialSpecModel = new MaterialSpecModel();
            $materialSpecModel->add($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'spec') {
                if (key($failed['spec']) == 'Required') {
                    $this->code = 450701;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'brand') {
                if (key($failed['brand']) == 'Required') {
                    $this->code = 450702;
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
    public function editSpec(Request $request){
        $rules = [
            'id'=>'required|integer',
            'spec' => 'required',
            'brand' => 'required',
        ];
        $message = [
            'id.required' => '获取规格参数失败',
            'id.integer' => '规格参数类型错误',
            'spec.required' => '请填写规格',
            'brand.integer' => '请填写品牌',
        ];
        $input = $request->only(['id','spec','brand']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $materialSpecModel = new MaterialSpecModel();
            $id = $input['id'];
            unset($input['id']);
            $materialSpecModel->update($id,$input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 450801;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 450802;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'spec') {
                if (key($failed['spec']) == 'Required') {
                    $this->code = 450803;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'brand') {
                if (key($failed['brand']) == 'Required') {
                    $this->code = 450804;
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
    public function delSpec(Request $request){
        $rules = [
            'id'=>'required|integer',
        ];
        $message = [
            'id.required' => '获取规格参数失败',
            'id.integer' => '规格参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $materialSpecModel = new MaterialSpecModel();
            $materialSpecModel->delete($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 450901;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 450902;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}