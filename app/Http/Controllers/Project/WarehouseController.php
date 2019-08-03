<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/1
 * Time: 9:29
 */

namespace App\Http\Controllers\Project;


use App\Http\Controllers\Auth\ApprovalController;
use App\Http\Controllers\Controller;
use App\Http\Model\WarehouseLogModel;
use App\Http\Model\WarehouseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    const CONSUME = 4;
    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'limit' => 'nullable|integer|in:10,20,50',
            'page' => 'nullable|integer|min:1',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型不正确',
            'limit.integer' => '记录条数参数类型错误',
            'limit.in' => '记录条数参数值不正确',
            'page.integer' => '页码参数类型错误',
            'page.min' => '页码参数值不小于:min',
        ];
        $input = $request->only(['projectId', 'search', 'limit', 'page']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $warehouseModel = new WarehouseModel();
            $warehouseModel->lists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 460101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 460102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'limit') {
                if (key($failed['limit']) == 'Integer') {
                    $this->code = 460103;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['limit']) == 'In') {
                    $this->code = 460104;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'page') {
                if (key($failed['page']) == 'Integer') {
                    $this->code = 460105;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['page']) == 'Min') {
                    $this->code = 460106;
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
            'id.required' => '获取仓库材料参数失败',
            'id.integer' => '仓库材料参数类型不正确',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $warehouseModel = new WarehouseModel();
            $warehouseModel->info($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 460201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 460202;
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
    public function setSalePrice(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'salePrice'=>'required|numeric',
        ];
        $message = [
            'id.required' => '获取仓库材料参数失败',
            'id.integer' => '仓库材料参数类型不正确',
            'salePrice.required' => '请填写售价',
            'salePrice.numeric' => '售价参数类型不正确',
        ];
        $input = $request->only(['id','salePrice']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $warehouseModel = new WarehouseModel();
            $id = $input['id'];
            unset($input['id']);
            $warehouseModel->update($id,$input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 460301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 460302;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'salePrice') {
                if (key($failed['salePrice']) == 'Required') {
                    $this->code = 460303;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['salePrice']) == 'Numeric') {
                    $this->code = 460304;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    public function consume(Request $request){
        $rules = [
            'projectId' => 'required|integer',
            'price'=>'required|numeric',
            'sourceEmployeeId' => 'required|integer',
            'time'=>'required|date_format:Y-m-d',
            'data'=>'required|array',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型不正确',
            'price.required' => '请填写总价',
            'price.numeric' => '总价类型不正确',
            'employeeId.required' => '请选择工人',
            'employeeId.integer' => '工人参数类型不正确',
            'time.required' => '请选择消费时间',
            'time.date_format' => '消费时间格式不正确',
            'data.required' => '请填写材料具体信息',
            'data.array' => '材料具体信息类型不正确',
        ];
        $input = $request->only(['projectId','sourceEmployeeId','time','data','price']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $rules1 = [
                'materialId' => 'required|integer',
                'specId' => 'required|integer',
                'supplierId' => 'required|integer',
                'amount' => 'required|integer',
                'price' => 'required|numeric',
            ];
            $message1 = [
                'materialId.required' => '请选择材料',
                'materialId.integer' => '材料参数类型错误',
                'specId.required' => '请选择材料规格',
                'specId.integer' => '材料规格参数类型错误',
                'supplierId.required' => '请选择供应商',
                'supplierId.integer' => '供应商参数类型错误',
                'amount.required' => '请填写材料数量',
                'amount.integer' => '材料数量类型错误',
                'price.required' => '请填写材料价格',
                'price.numeric' => '材料价格类型错误',
            ];
            $input1 = $input['data'];
            $continue = true;
            $warehouseModel = new WarehouseModel();
            foreach ($input1 as $key => $i) {
                $validator1 = Validator::make($i, $rules1, $message1);
                if ($validator1->fails()) {
                    $continue = false;
                    $failed1 = $validator1->failed();
                    if (key($failed1) == 'materialId') {
                        if (key($failed1['materialId']) == 'Required') {
                            $this->code = 460411;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['materialId']) == 'Integer') {
                            $this->code = 460412;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'specId') {
                        if (key($failed1['specId']) == 'Required') {
                            $this->code = 460413;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['specId']) == 'Integer') {
                            $this->code = 460414;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'supplierId') {
                        if (key($failed1['supplierId']) == 'Required') {
                            $this->code = 460415;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['supplierId']) == 'Integer') {
                            $this->code = 460416;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'amount') {
                        if (key($failed1['amount']) == 'Required') {
                            $this->code = 460417;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['amount']) == 'Integer') {
                            $this->code = 460418;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    }elseif (key($failed1) == 'price') {
                        if (key($failed1['price']) == 'Required') {
                            $this->code = 460419;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['price']) == 'Numeric') {
                            $this->code = 460420;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    }
                }
                //检查库存
                $warehouseInfo = $warehouseModel->info([
                    'projectId'=>$input['projectId'],
                    'materialId'=>$i['materialId'],
                    'specId'=>$i['specId'],
                    'supplierId'=>$i['supplierId'],
                ]);
                if ($warehouseInfo['amount'] < $i['amount']){
                    $continue = false;
                    $this->code = 460421;
                    $this->msg = '库存不足';
                    break;
                }
            }
            if ($continue) {
                $materialLogModel = new WarehouseLogModel();
                $input['type'] = self::CONSUME;
                $result = $materialLogModel->addLog($input);
                if (is_int($result)){
                    $input['id'] = $result;
                    $approval = ApprovalController::approval('consume', $input);
                    if ($approval['status']) {
                        if ($approval['result']) {
                            $this->msg = '申请提交成功，请等待审批结果';
                        } else {
                            $this->code = 460422;
                            $this->msg = '保存失败，请稍后重试';
                        }
                    }
                }else{
                    $this->code = 460421;
                    $this->msg = $result;
                }

            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 460401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 460402;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'price') {
                if (key($failed['price']) == 'Required') {
                    $this->code = 460403;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['price']) == 'Numeric') {
                    $this->code = 460404;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'employeeId') {
                if (key($failed['employeeId']) == 'Required') {
                    $this->code = 460405;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['employeeId']) == 'Integer') {
                    $this->code = 460406;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'time') {
                if (key($failed['time']) == 'Required') {
                    $this->code = 460407;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['time']) == 'DateFormat') {
                    $this->code = 460408;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'data') {
                if (key($failed['data']) == 'Required') {
                    $this->code = 460409;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['data']) == 'Array') {
                    $this->code = 460410;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}