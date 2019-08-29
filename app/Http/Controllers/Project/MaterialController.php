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
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'length.required'=>'获取每页记录数失败',
            'length.integer' => '每页记录参数类型错误',
            'length.in' => '每页记录参数值不正确',
            'start.required'=>'获取起始记录失败',
            'start.integer' => '起始记录类型错误',
            'start.min' => '起始记录参数值不小于:min',
        ];
        $input = $request->only(['search','draw','length','start']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $materialModel = new MaterialModel();
            $lists = $materialModel->lists($input);
            $countLists = $materialModel->countLists($input);
            $this->data = [
                "draw"=>$input['draw'],
                "data"=>$lists,
                "recordsFiltered"=>$countLists,
                "recordsTotal"=>$countLists,
            ];
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 450101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 450102;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 450103;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 450104;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 450105;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 450106;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 450107;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 450108;
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
            $this->data = $materialModel->info($input);
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
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'materialId.required' => '获取材料参数失败',
            'materialId.integer' => '材料参数类型错误',
            'length.required' => '获取每页记录参数失败',
            'length.integer' => '每页记录参数类型错误',
            'length.in' => '每页记录参数值不正确',
            'start.required' => '获取起始记录参数失败',
            'start.integer' => '起始记录参数类型错误',
            'start.min' => '起始记录参数值不小于:min',
        ];
        $input = $request->only(['materialId','draw','length','start']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $materialSpecModel = new MaterialSpecModel();
            $lists = $materialSpecModel->lists($input);
            $countLists = $materialSpecModel->countLists($input);
            $this->data = [
                "draw"=>$input['draw'],
                "data"=>$lists,
                "recordsFiltered"=>$countLists,
                "recordsTotal"=>$countLists,
            ];
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'materialId') {
                if (key($failed['materialId']) == 'Required') {
                    $this->code = 450601;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['materialId']) == 'Integer') {
                    $this->code = 450602;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 450603;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 450604;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 450605;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 450606;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 450607;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 450608;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 450608;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'In') {
                    $this->code = 450610;
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
            'materialId' => 'required|integer',
            'spec' => 'required',
            'brand' => 'nullable',
        ];
        $message = [
            'materialId.required' => '获取材料参数失败',
            'materialId.integer' => '材料参数类型错误',
            'spec.required' => '请填写规格',
            'brand.required' => '请填写品牌',
        ];
        $input = $request->only(['materialId','spec','brand']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $materialSpecModel = new MaterialSpecModel();
            $materialSpecModel->add($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'materialId') {
                if (key($failed['materialId']) == 'Required') {
                    $this->code = 450701;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['materialId']) == 'Integer') {
                    $this->code = 450702;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'spec') {
                if (key($failed['spec']) == 'Required') {
                    $this->code = 450703;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'brand') {
                if (key($failed['brand']) == 'Required') {
                    $this->code = 450704;
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
    public function specInfo(Request $request){
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
            $this->data = $materialSpecModel->info($input);
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
                    $this->code = 451001;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 451002;
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
    public function specSelectLists(Request $request){
        $rules = [
            'materialId' => 'required|integer',
        ];
        $message = [
            'materialId.required' => '获取材料参数失败',
            'materialId.integer' => '材料参数类型错误',
        ];
        $input = $request->only(['materialId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $materialSpecModel = new MaterialSpecModel();
            $this->data = $materialSpecModel->selectLists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'materialId') {
                if (key($failed['materialId']) == 'Required') {
                    $this->code = 451101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['materialId']) == 'Integer') {
                    $this->code = 451102;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    public function search(Request $request){
        $rules = [
            'search' => 'required',
        ];
        $message = [
            'search.required' => '未获取到搜索内容',
        ];
        $input = $request->only(['search']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $materialModel = new MaterialModel();
            $this->data = $materialModel->search($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'search') {
                if (key($failed['search']) == 'Required') {
                    $this->code = 451101;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}