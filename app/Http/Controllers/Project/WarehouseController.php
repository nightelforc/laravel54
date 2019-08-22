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
use App\Http\Model\EmployeeMaterialOrderInfoModel;
use App\Http\Model\SettingModel;
use App\Http\Model\WarehouseLogInfoModel;
use App\Http\Model\WarehouseLogModel;
use App\Http\Model\WarehouseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    const PURCHASE = 1;
    const RECEIPT = 2;
    const EXPEND = 3;
    const CONSUME = 4;
    const ALLOT = 5;
    const BREAKDOWN = 6;

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型不正确',
            'length.required' => '获取每页记录参数失败',
            'length.integer' => '每页记录条数参数类型错误',
            'length.in' => '每页记录参数值不正确',
            'start.required' => '获取起始记录参数失败',
            'start.integer' => '起始记录参数类型错误',
            'start.min' => '起始记录参数值不小于:min',
        ];
        $input = $request->only(['projectId', 'search', 'draw', 'length', 'start']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $warehouseModel = new WarehouseModel();
            $lists = $warehouseModel->lists($input);
            $countLists = $warehouseModel->countLists($input);
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
                    $this->code = 460101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 460102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 460103;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 460104;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 460105;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 460106;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 460107;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 460108;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 460109;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 460110;
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
            $this->data = $warehouseModel->info($input);
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
            'salePrice' => 'required|numeric',
        ];
        $message = [
            'id.required' => '获取仓库材料参数失败',
            'id.integer' => '仓库材料参数类型不正确',
            'salePrice.required' => '请填写售价',
            'salePrice.numeric' => '售价参数类型不正确',
        ];
        $input = $request->only(['id', 'salePrice']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $warehouseModel = new WarehouseModel();
            $id = $input['id'];
            unset($input['id']);
            $info = $warehouseModel->info(['id'=>$id]);
            if (!empty($info)){
                $settingModel = new SettingModel();
                $saleRate = $settingModel->get('saleRate',$info['projectId']);
                $purchasePrice = $info['purchasePrice'];
                $result = $input['salePrice']-$purchasePrice*(1+$saleRate);
                if ($result > 0){
                    $warehouseModel->update($id, $input);
                }else{
                    $this->code = 460305;
                    $this->msg = '材料价格不能低于默认最低价格';
                }

            }
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
            } elseif (key($failed) == 'salePrice') {
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

    /**
     * 材料消费
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function consume(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'price' => 'required|numeric',
            'sourceEmployeeId' => 'required|integer',
            'time' => 'required|date_format:Y-m-d',
            'data' => 'required|array',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型不正确',
            'price.required' => '请填写总价',
            'price.numeric' => '总价类型不正确',
            'sourceEmployeeId.required' => '请选择工人',
            'sourceEmployeeId.integer' => '工人参数类型不正确',
            'time.required' => '请选择消费时间',
            'time.date_format' => '消费时间格式不正确',
            'data.required' => '请填写材料具体信息',
            'data.array' => '材料具体信息类型不正确',
        ];
        $input = $request->only(['projectId', 'sourceEmployeeId', 'time', 'data', 'price', 'remark',self::$token]);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $rules1 = [
                'materialId' => 'required|integer',
                'specId' => 'required|integer',
                'supplierId' => 'required|integer',
                'amount' => 'required|integer',
                'price' => 'required|numeric',
                'totalPrice' => 'required|numeric',
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
                'totalPrice.required' => '请填写材料总价',
                'totalPrice.numeric' => '材料总价类型错误',
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
                    } elseif (key($failed1) == 'price') {
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
                    } elseif (key($failed1) == 'totalPrice') {
                        if (key($failed1['totalPrice']) == 'Required') {
                            $this->code = 460421;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['totalPrice']) == 'Numeric') {
                            $this->code = 460422;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    }
                }
                //检查库存
                $warehouseInfo = $warehouseModel->info([
                    'projectId' => $input['projectId'],
                    'materialId' => $i['materialId'],
                    'specId' => $i['specId'],
                    'supplierId' => $i['supplierId'],
                ]);
                if (empty($warehouseInfo) || $warehouseInfo['amount'] < $i['amount']) {
                    $continue = false;
                    $this->code = 460423;
                    $this->msg = '库存不足';
                    break;
                }
            }
            if ($continue) {
                $materialLogModel = new WarehouseLogModel();
                $input['type'] = self::CONSUME;
                $result = $materialLogModel->addLog($input);
                if (is_int($result)) {
                    $input['id'] = $result;
                    $approval = ApprovalController::approval('consume', $input);
                    if ($approval['status']) {
                        if ($approval['result']) {
                            $this->msg = '申请提交成功，请等待审批结果';
                        } else {
                            $this->code = 460425;
                            $this->msg = '保存失败，请稍后重试';
                        }
                    }
                } else {
                    $this->code = 460424;
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
            } elseif (key($failed) == 'price') {
                if (key($failed['price']) == 'Required') {
                    $this->code = 460403;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['price']) == 'Numeric') {
                    $this->code = 460404;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'sourceEmployeeId') {
                if (key($failed['sourceEmployeeId']) == 'Required') {
                    $this->code = 460405;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['sourceEmployeeId']) == 'Integer') {
                    $this->code = 460406;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'time') {
                if (key($failed['time']) == 'Required') {
                    $this->code = 460407;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['time']) == 'DateFormat') {
                    $this->code = 460408;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'data') {
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

    /**
     * 材料消耗
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function expend(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'time' => 'required|date_format:Y-m-d',
            'data' => 'required|array',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型不正确',
            'time.required' => '请选择消费时间',
            'time.date_format' => '消费时间格式不正确',
            'data.required' => '请填写材料具体信息',
            'data.array' => '材料具体信息类型不正确',
        ];
        $input = $request->only(['projectId', 'time', 'data', 'remark',self::$token]);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $rules1 = [
                'materialId' => 'required|integer',
                'specId' => 'required|integer',
                'supplierId' => 'required|integer',
                'amount' => 'required|integer',
                'price' => 'required|numeric',
                'totalPrice' => 'required|numeric',
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
                'totalPrice.required' => '请填写材料总价',
                'totalPrice.numeric' => '材料总价类型错误',
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
                            $this->code = 460507;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['materialId']) == 'Integer') {
                            $this->code = 460508;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'specId') {
                        if (key($failed1['specId']) == 'Required') {
                            $this->code = 460509;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['specId']) == 'Integer') {
                            $this->code = 460510;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'supplierId') {
                        if (key($failed1['supplierId']) == 'Required') {
                            $this->code = 460511;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['supplierId']) == 'Integer') {
                            $this->code = 460512;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'amount') {
                        if (key($failed1['amount']) == 'Required') {
                            $this->code = 460513;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['amount']) == 'Integer') {
                            $this->code = 460514;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'price') {
                        if (key($failed1['price']) == 'Required') {
                            $this->code = 460515;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['price']) == 'Numeric') {
                            $this->code = 460516;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'totalPrice') {
                        if (key($failed1['totalPrice']) == 'Required') {
                            $this->code = 460517;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['totalPrice']) == 'Numeric') {
                            $this->code = 460518;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    }
                }
                //检查库存
                $warehouseInfo = $warehouseModel->info([
                    'projectId' => $input['projectId'],
                    'materialId' => $i['materialId'],
                    'specId' => $i['specId'],
                    'supplierId' => $i['supplierId'],
                ]);
                if (empty($warehouseInfo) || $warehouseInfo['amount'] < $i['amount']) {
                    $continue = false;
                    $this->code = 460519;
                    $this->msg = '库存不足';
                    break;
                }
            }
            if ($continue) {
                $materialLogModel = new WarehouseLogModel();
                $input['type'] = self::EXPEND;
                $result = $materialLogModel->addLog($input);
                if (is_int($result)) {
                    $input['id'] = $result;
                    $approval = ApprovalController::approval('expend', $input);
                    if ($approval['status']) {
                        if ($approval['result']) {
                            $this->msg = '申请提交成功，请等待审批结果';
                        } else {
                            $this->code = 460521;
                            $this->msg = '保存失败，请稍后重试';
                        }
                    }
                } else {
                    $this->code = 460520;
                    $this->msg = $result;
                }
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 460501;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 460502;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'time') {
                if (key($failed['time']) == 'Required') {
                    $this->code = 460503;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['time']) == 'DateFormat') {
                    $this->code = 460504;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'data') {
                if (key($failed['data']) == 'Required') {
                    $this->code = 460505;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['data']) == 'Array') {
                    $this->code = 460506;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * 报损
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function breakdown(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'time' => 'required|date_format:Y-m-d',
            'remark' => 'required',
            'materialId' => 'required|integer',
            'specId' => 'required|integer',
            'supplierId' => 'required|integer',
            'amount' => 'required|integer',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型不正确',
            'time.required' => '请选择消费时间',
            'time.date_format' => '消费时间格式不正确',
            'remark.required' => '请输入报损原因',
            'materialId.required' => '请选择材料',
            'materialId.integer' => '材料参数类型错误',
            'specId.required' => '请选择材料规格',
            'specId.integer' => '材料规格参数类型错误',
            'supplierId.required' => '请选择供应商',
            'supplierId.integer' => '供应商参数类型错误',
            'amount.required' => '请填写材料数量',
            'amount.integer' => '材料数量类型错误',
        ];
        $input = $request->only(['projectId', 'time', 'remark', 'materialId', 'specId', 'supplierId', 'amount',self::$token]);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $warehouseModel = new WarehouseModel();
            //检查库存
            $warehouseInfo = $warehouseModel->info([
                'projectId' => $input['projectId'],
                'materialId' => $input['materialId'],
                'specId' => $input['specId'],
                'supplierId' => $input['supplierId'],
            ]);
            if (empty($warehouseInfo) || $warehouseInfo['amount'] < $input['amount']) {
                $this->code = 460620;
                $this->msg = '库存不足';
            }

            $materialLogModel = new WarehouseLogModel();
            $input['type'] = self::BREAKDOWN;
            $result = $materialLogModel->addLog($input);
            if (is_int($result)) {
                $input['id'] = $result;
                $approval = ApprovalController::approval('breakdown', $input);
                if ($approval['status']) {
                    if ($approval['result']) {
                        $this->msg = '申请提交成功，请等待审批结果';
                    } else {
                        $this->code = 460622;
                        $this->msg = '保存失败，请稍后重试';
                    }
                }
            } else {
                $this->code = 460621;
                $this->msg = $result;
            }

        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 460601;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 460602;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'time') {
                if (key($failed['time']) == 'Required') {
                    $this->code = 460603;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['time']) == 'DateFormat') {
                    $this->code = 460604;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'remark') {
                if (key($failed['remark']) == 'Required') {
                    $this->code = 460605;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'data') {
                if (key($failed['data']) == 'Required') {
                    $this->code = 460606;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['data']) == 'Array') {
                    $this->code = 460607;
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
    public function allot(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
//            'price' => 'required|numeric',
            'sourceProjectId' => 'required|integer',
            'time' => 'required|date_format:Y-m-d',
            'data' => 'required|array',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型不正确',
//            'price.required' => '请填写总价',
//            'price.numeric' => '总价类型不正确',
            'sourceProjectId.required' => '请选择调拨项目',
            'sourceProjectId.integer' => '调拨项目参数类型不正确',
            'time.required' => '请选择消费时间',
            'time.date_format' => '消费时间格式不正确',
            'data.required' => '请填写材料具体信息',
            'data.array' => '材料具体信息类型不正确',
        ];
        $input = $request->only(['projectId', 'sourceProjectId', 'time', 'data', 'remark',self::$token]);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $rules1 = [
                'materialId' => 'required|integer',
                'specId' => 'required|integer',
                'supplierId' => 'required|integer',
                'amount' => 'required|integer',
                'price' => 'required|numeric',
                'totalPrice' => 'required|numeric',
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
                'totalPrice.required' => '请填写材料总价',
                'totalPrice.numeric' => '材料总价类型错误',
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
                            $this->code = 460711;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['materialId']) == 'Integer') {
                            $this->code = 460712;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'specId') {
                        if (key($failed1['specId']) == 'Required') {
                            $this->code = 460713;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['specId']) == 'Integer') {
                            $this->code = 460714;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'supplierId') {
                        if (key($failed1['supplierId']) == 'Required') {
                            $this->code = 460715;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['supplierId']) == 'Integer') {
                            $this->code = 460716;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'amount') {
                        if (key($failed1['amount']) == 'Required') {
                            $this->code = 460717;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['amount']) == 'Integer') {
                            $this->code = 460718;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'price') {
                        if (key($failed1['price']) == 'Required') {
                            $this->code = 460719;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['price']) == 'Numeric') {
                            $this->code = 460720;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'totalPrice') {
                        if (key($failed1['totalPrice']) == 'Required') {
                            $this->code = 460721;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['totalPrice']) == 'Numeric') {
                            $this->code = 460722;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    }
                }
                //检查库存
                $warehouseInfo = $warehouseModel->info([
                    'projectId' => $input['projectId'],
                    'materialId' => $i['materialId'],
                    'specId' => $i['specId'],
                    'supplierId' => $i['supplierId'],
                ]);
                if (empty($warehouseInfo) || $warehouseInfo['amount'] < $i['amount']) {
                    $continue = false;
                    $this->code = 460723;
                    $this->msg = '库存不足';
                    break;
                }
            }
            if ($continue) {
                $materialLogModel = new WarehouseLogModel();
                $input['type'] = self::ALLOT;
                $result = $materialLogModel->addLog($input);
                if (is_int($result)) {
                    $input['id'] = $result;
                    $approval = ApprovalController::approval('allot', $input);
                    if ($approval['status']) {
                        if ($approval['result']) {
                            $this->msg = '申请提交成功，请等待审批结果';
                        } else {
                            $this->code = 460725;
                            $this->msg = '保存失败，请稍后重试';
                        }
                    }
                } else {
                    $this->code = 460724;
                    $this->msg = $result;
                }
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 460701;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 460702;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'price') {
                if (key($failed['price']) == 'Required') {
                    $this->code = 460703;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['price']) == 'Numeric') {
                    $this->code = 460704;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'sourceProjectId') {
                if (key($failed['sourceProjectId']) == 'Required') {
                    $this->code = 460705;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['sourceProjectId']) == 'Integer') {
                    $this->code = 460706;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'time') {
                if (key($failed['time']) == 'Required') {
                    $this->code = 460707;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['time']) == 'DateFormat') {
                    $this->code = 460708;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'data') {
                if (key($failed['data']) == 'Required') {
                    $this->code = 460709;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['data']) == 'Array') {
                    $this->code = 460710;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * 材料采购入库
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function purchase(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'time' => 'required|date_format:Y-m-d',
            'price' => 'required|numeric',
            'data' => 'required|array',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型不正确',
            'time.required' => '请选择消费时间',
            'time.date_format' => '消费时间格式不正确',
            'price.required' => '获取总价失败',
            'price.integer' => '总价参数类型不正确',
            'data.required' => '请填写材料具体信息',
            'data.array' => '材料具体信息类型不正确',
        ];
        $input = $request->only(['projectId', 'time', 'price', 'data', 'remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $rules1 = [
                'materialId' => 'required|integer',
                'specId' => 'required|integer',
                'supplierId' => 'required|integer',
                'name' => 'required_if:supplierId,0',
                'phone' => 'required_if:supplierId,0',
                'address' => 'required_if:supplierId,0',
                'amount' => 'required|integer',
                'price' => 'required|numeric',
                'totalPrice' => 'required|numeric',
                'payType' => 'required|integer|in:1,2'
            ];
            $message1 = [
                'materialId.required' => '请选择材料',
                'materialId.integer' => '材料参数类型错误',
                'specId.required' => '请选择材料规格',
                'specId.integer' => '材料规格参数类型错误',
                'supplierId.required' => '请选择供应商',
                'supplierId.integer' => '供应商参数类型错误',
                'name.required_if' => '请填写供应商名称',
                'phone.required_if' => '请填写供应商电话',
                'address.required_if' => '请填写供应商地址',
                'amount.required' => '请填写材料数量',
                'amount.integer' => '材料数量类型错误',
                'price.required' => '请填写材料价格',
                'price.numeric' => '材料价格类型错误',
                'totalPrice.required' => '请填写材料总价',
                'totalPrice.numeric' => '材料总价类型错误',
                'payType.required' => '请选择付款方式',
                'payType.integer' => '付款方式参数类型错误',
                'payType.in' => '付款方式参数不正确',
            ];
            $input1 = $input['data'];
            $continue = true;
            foreach ($input1 as $key => $i) {
                $validator1 = Validator::make($i, $rules1, $message1);
                if ($validator1->fails()) {
                    $continue = false;
                    $failed1 = $validator1->failed();
                    if (key($failed1) == 'materialId') {
                        if (key($failed1['materialId']) == 'Required') {
                            $this->code = 460809;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['materialId']) == 'Integer') {
                            $this->code = 460810;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'specId') {
                        if (key($failed1['specId']) == 'Required') {
                            $this->code = 460811;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['specId']) == 'Integer') {
                            $this->code = 460812;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'supplierId') {
                        if (key($failed1['supplierId']) == 'Required') {
                            $this->code = 460813;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['supplierId']) == 'Integer') {
                            $this->code = 460814;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'name') {
                        if (key($failed1['name']) == 'RequiredIf') {
                            $this->code = 460815;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'phone') {
                        if (key($failed1['phone']) == 'RequiredIf') {
                            $this->code = 460816;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'address') {
                        if (key($failed1['supplierId']) == 'RequiredIf') {
                            $this->code = 460817;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'amount') {
                        if (key($failed1['amount']) == 'Required') {
                            $this->code = 460818;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['amount']) == 'Integer') {
                            $this->code = 460819;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'price') {
                        if (key($failed1['price']) == 'Required') {
                            $this->code = 460820;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['price']) == 'Numeric') {
                            $this->code = 460821;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'totalPrice') {
                        if (key($failed1['totalPrice']) == 'Required') {
                            $this->code = 460822;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['totalPrice']) == 'Numeric') {
                            $this->code = 460823;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'payType') {
                        if (key($failed1['payType']) == 'Required') {
                            $this->code = 460824;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['payType']) == 'Integer') {
                            $this->code = 460825;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['payType']) == 'In') {
                            $this->code = 460826;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    }
                }
            }
            if ($continue) {
                $input['type'] = self::PURCHASE;
                $warehouseLogModel = new WarehouseLogModel();
                $result = $warehouseLogModel->purchase($input);
                if (!$result) {
                    $this->code = 460827;
                    $this->msg = "保存失败，请稍后重试";
                }
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 460801;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 460802;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'price') {
                if (key($failed['price']) == 'Required') {
                    $this->code = 460803;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['price']) == 'Numeric') {
                    $this->code = 460804;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'time') {
                if (key($failed['time']) == 'Required') {
                    $this->code = 460805;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['time']) == 'DateFormat') {
                    $this->code = 460806;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'data') {
                if (key($failed['data']) == 'Required') {
                    $this->code = 460807;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['data']) == 'Array') {
                    $this->code = 460808;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * 接收调拨的材料
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function receipt(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'time' => 'required|date_format:Y-m-d',
            'price' => 'required|numeric',
            'data' => 'required|array',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型不正确',
            'time.required' => '请选择消费时间',
            'time.date_format' => '消费时间格式不正确',
            'price.required' => '获取总价失败',
            'price.integer' => '总价参数类型不正确',
            'data.required' => '请填写材料具体信息',
            'data.array' => '材料具体信息类型不正确',
        ];
        $input = $request->only(['projectId', 'price', 'time', 'data', 'remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $rules1 = [
                'materialId' => 'required|integer',
                'specId' => 'required|integer',
                'supplierId' => 'required|integer',
                'amount' => 'required|integer',
                'price' => 'required|numeric',
                'totalPrice' => 'required|numeric',
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
                'totalPrice.required' => '请填写材料总价',
                'totalPrice.numeric' => '材料总价类型错误',
            ];
            $input1 = $input['data'];
            $continue = true;
            foreach ($input1 as $key => $i) {
                $validator1 = Validator::make($i, $rules1, $message1);
                if ($validator1->fails()) {
                    $continue = false;
                    $failed1 = $validator1->failed();
                    if (key($failed1) == 'materialId') {
                        if (key($failed1['materialId']) == 'Required') {
                            $this->code = 460909;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['materialId']) == 'Integer') {
                            $this->code = 460910;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'specId') {
                        if (key($failed1['specId']) == 'Required') {
                            $this->code = 460911;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['specId']) == 'Integer') {
                            $this->code = 460912;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'supplierId') {
                        if (key($failed1['supplierId']) == 'Required') {
                            $this->code = 460913;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['supplierId']) == 'Integer') {
                            $this->code = 460914;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'amount') {
                        if (key($failed1['amount']) == 'Required') {
                            $this->code = 460915;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['amount']) == 'Integer') {
                            $this->code = 460916;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'price') {
                        if (key($failed1['price']) == 'Required') {
                            $this->code = 460917;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['price']) == 'Numeric') {
                            $this->code = 460918;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'totalPrice') {
                        if (key($failed1['totalPrice']) == 'Required') {
                            $this->code = 460919;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['totalPrice']) == 'Numeric') {
                            $this->code = 460920;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    }
                }
            }
            if ($continue) {
                $warehouseLogModel = new WarehouseLogModel();
                $result = $warehouseLogModel->receipt($input);
                if (!$result) {
                    $this->code = 460921;
                    $this->msg = "保存失败，请稍后重试";
                }
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 460901;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 460902;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'price') {
                if (key($failed['price']) == 'Required') {
                    $this->code = 460903;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['price']) == 'Numeric') {
                    $this->code = 460904;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'time') {
                if (key($failed['time']) == 'Required') {
                    $this->code = 460905;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['time']) == 'DateFormat') {
                    $this->code = 460906;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'data') {
                if (key($failed['data']) == 'Required') {
                    $this->code = 460907;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['data']) == 'Array') {
                    $this->code = 460908;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @param int $type
     * @return \Illuminate\Http\Response
     */
    public function logLists(Request $request, $type = 0)
    {
        $rules = [
            'projectId' => 'required|integer',
            'startTime' => 'nullable|date_format:Y-m-d',
            'endTime' => 'nullable|date_format:Y-m-d',
            'type' => 'nullable|integer|in:1,2,3,4,5,6',
            'status' => 'nullable|integer|in:0,1',
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型不正确',
            'startTime.date_format' => '起始时间格式不正确',
            'endTime.date_format' => '起始时间格式不正确',
            'type.integer' => '记录条数参数类型错误',
            'type.in' => '记录条数参数值不正确',
            'status.integer' => '记录条数参数类型错误',
            'status.in' => '记录条数参数值不正确',
            'length.required' => '获取记录条数失败',
            'length.integer' => '记录条数参数类型错误',
            'length.in' => '记录条数参数值不正确',
            'start.required' => '获取起始记录位置失败',
            'start.integer' => '页码参数类型错误',
            'start.min' => '页码参数值不小于:min',
        ];
        $input = $request->all();
        if ($type != 0) {
            $input['type'] = $type;
        }
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $warehouseLogInfoModel = new WarehouseLogInfoModel();
            $lists = $warehouseLogInfoModel->lists($input);
            $countLists = $warehouseLogInfoModel->countLists($input);
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
                    $this->code = 461001;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 461002;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'startTime') {
                if (key($failed['startTime']) == 'DateFormat') {
                    $this->code = 461003;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'endTime') {
                if (key($failed['endTime']) == 'DateFormat') {
                    $this->code = 461004;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'type') {
                if (key($failed['type']) == 'Integer') {
                    $this->code = 461005;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['type']) == 'In') {
                    $this->code = 461006;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Integer') {
                    $this->code = 461007;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'In') {
                    $this->code = 461008;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 461009;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 461010;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 461011;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 461012;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 461013;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 461014;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 461015;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 461016;
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
    public function breakdownLists(Request $request)
    {
        return $this->logLists($request, self::BREAKDOWN);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function consumeLists(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'startTime' => 'nullable|date_format:Y-m-d',
            'endTime' => 'nullable|date_format:Y-m-d',
            'status' => 'nullable|integer|in:0,1',
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型不正确',
            'startTime.date_format' => '起始时间格式不正确',
            'endTime.date_format' => '起始时间格式不正确',
            'status.integer' => '记录条数参数类型错误',
            'status.in' => '记录条数参数值不正确',
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
            $employeeMaterialOrderInfoModel = new EmployeeMaterialOrderInfoModel();
            $lists = $employeeMaterialOrderInfoModel->lists($input);
            $countLists = $employeeMaterialOrderInfoModel->countLists($input);
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
                    $this->code = 461101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 461102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'startTime') {
                if (key($failed['startTime']) == 'DateFormat') {
                    $this->code = 461103;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'endTime') {
                if (key($failed['endTime']) == 'DateFormat') {
                    $this->code = 461104;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'status') {
                if (key($failed['status']) == 'Integer') {
                    $this->code = 461105;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['status']) == 'In') {
                    $this->code = 461106;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 461107;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 461108;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 461109;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 461110;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 461111;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 461112;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 461113;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 461114;
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
    public function search(Request $request)
    {
        $rules = [
            'projectId' => 'nullable|integer',
            'supplierId' => 'nullable|integer',
            'materialId' => 'nullable|integer',
            'specId' => 'nullable|integer',
        ];
        $message = [
            'projectId.integer' => '项目参数类型错误',
            'supplierId.integer' => '供应商参数类型错误',
            'materialId.integer' => '材料参数类型错误',
            'specId.integer' => '规格参数类型错误',
        ];
        $input = $request->only(['projectId', 'supplierId', 'materialId', 'specId', 'search']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $warehouseModel = new WarehouseModel();
            $this->data = $warehouseModel->search($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 461201;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'supplierId') {
                if (key($failed['supplierId']) == 'Integer') {
                    $this->code = 461202;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'materialId') {
                if (key($failed['materialId']) == 'Integer') {
                    $this->code = 461203;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'specId') {
                if (key($failed['specId']) == 'Integer') {
                    $this->code = 461204;
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
    public function setRate(Request $request){
        $rules = [
            'projectId' => 'required|integer',
            'rate' => 'required|numeric|min:0',
        ];
        $message = [
            'projectId.required' => '获取项目参数错误',
            'projectId.integer' => '项目参数类型错误',
            'rate.required' => '请填写溢价率',
            'rate.numeric' => '溢价率类型错误',
            'rate.min' => '溢价率不能小于0',
        ];
        $input = $request->only(['projectId', 'rate']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $settingModel = new SettingModel();
            $settingModel->set('saleRate',$input['rate'],$input['projectId']);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 461201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 461202;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'rate') {
                if (key($failed['rate']) == 'Required') {
                    $this->code = 461203;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['rate']) == 'Numeric') {
                    $this->code = 461204;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['rate']) == 'Min') {
                    $this->code = 461205;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}