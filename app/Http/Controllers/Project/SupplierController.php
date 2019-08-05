<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/27
 * Time: 10:28
 */

namespace App\Http\Controllers\Project;


use App\Http\Controllers\Controller;
use App\Http\Model\SupplierModel;
use App\Http\Model\SupplierOrdersInfoModel;
use App\Http\Model\SupplierOrdersModel;
use App\Http\Model\SupplierRepaymentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request)
    {
        $input = $request->only('search');
        $supplierModel = new SupplierModel();
        $this->data = $supplierModel->lists($input);
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
            'liaison' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'accountName' => 'required',
            'accountBank' => 'required',
            'accountNumber' => 'required',
            'corporation' => 'required',
        ];
        $message = [
            'name.required' => '请输入供应商名称',
            'liaison.required' => '请输入联系人',
            'phone.required' => '请输入联系人电话',
            'address.required' => '请输入供应商地址',
            'accountName.required' => '请输入开户名称',
            'accountBank.required' => '请输入开户银行',
            'accountNumber.required' => '请输入开户账号',
            'corporation.required' => '请输入法定代表人',
        ];
        $input = $request->only(['name', 'liaison', 'phone', 'address', 'accountName', 'accountBank', 'accountNumber', 'corporation']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $supplierModel = new SupplierModel();
            $supplierModel->insert($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 440101;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'liaison') {
                if (key($failed['liaison']) == 'Required') {
                    $this->code = 440102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'phone') {
                if (key($failed['phone']) == 'Required') {
                    $this->code = 440103;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'address') {
                if (key($failed['address']) == 'Required') {
                    $this->code = 440104;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'accountName') {
                if (key($failed['accountName']) == 'Required') {
                    $this->code = 440105;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'accountBank') {
                if (key($failed['accountBank']) == 'Required') {
                    $this->code = 440106;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'accountNumber') {
                if (key($failed['accountNumber']) == 'Required') {
                    $this->code = 440107;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'corporation') {
                if (key($failed['corporation']) == 'Required') {
                    $this->code = 440108;
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
            'id.required' => '获取供应商参数失败',
            'id.integer' => '供应商参数类型错误',
        ];
        $input = $request->only('id');
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $supplierModel = new SupplierModel();
            $this->data = $supplierModel->info($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 440201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 440202;
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
            'liaison' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'accountName' => 'required',
            'accountBank' => 'required',
            'accountNumber' => 'required',
            'corporation' => 'required',
        ];
        $message = [
            'id.required' => '获取供应商参数失败',
            'id.integer' => '供应商参数类型不正确',
            'name.required' => '请输入供应商名称',
            'liaison.required' => '请输入联系人',
            'phone.required' => '请输入联系人电话',
            'address.required' => '请输入供应商地址',
            'accountName.required' => '请输入开户名称',
            'accountBank.required' => '请输入开户银行',
            'accountNumber.required' => '请输入开户账号',
            'corporation.required' => '请输入法定代表人',
        ];
        $input = $request->only(['id', 'name', 'liaison', 'phone', 'address', 'accountName', 'accountBank', 'accountNumber', 'corporation']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $supplierModel = new SupplierModel();
            $supplierModel->update($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 440301;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'liaison') {
                if (key($failed['liaison']) == 'Required') {
                    $this->code = 440302;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'phone') {
                if (key($failed['phone']) == 'Required') {
                    $this->code = 440303;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'address') {
                if (key($failed['address']) == 'Required') {
                    $this->code = 440304;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'accountName') {
                if (key($failed['accountName']) == 'Required') {
                    $this->code = 440305;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'accountBank') {
                if (key($failed['accountBank']) == 'Required') {
                    $this->code = 440306;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'accountNumber') {
                if (key($failed['accountNumber']) == 'Required') {
                    $this->code = 440307;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'corporation') {
                if (key($failed['corporation']) == 'Required') {
                    $this->code = 440308;
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
    public function orderLists(Request $request)
    {
        $rules = [
            'supplierId' => 'required|integer',
            'isPay' => 'nullable|integer|in:0,1',
            'limit' => 'nullable|integer|in:10,20,50',
            'page' => 'nullable|integer|min:1',
        ];
        $message = [
            'supplierId.required' => '获取供应商参数失败',
            'supplierId.integer' => '供应商参数类型不正确',
            'isPay.integer' => '付款状态参数类型错误',
            'isPay.in' => '付款状态参数值不正确',
            'limit.integer' => '记录条数参数类型错误',
            'limit.in' => '记录条数参数值不正确',
            'page.integer' => '页码参数类型错误',
            'page.min' => '页码参数值不小于:min',
        ];
        $input = $request->only(['supplierId', 'isPay', 'limit', 'page', 'search']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $supplierOrdersModel = new SupplierOrdersModel();
            $this->data = $supplierOrdersModel->lists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'supplierId') {
                if (key($failed['supplierId']) == 'Required') {
                    $this->code = 440401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['supplierId']) == 'Integer') {
                    $this->code = 440402;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'isPay') {
                if (key($failed['isPay']) == 'Integer') {
                    $this->code = 440403;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['isPay']) == 'In') {
                    $this->code = 440404;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'limit') {
                if (key($failed['limit']) == 'Integer') {
                    $this->code = 440405;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['limit']) == 'In') {
                    $this->code = 440406;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'page') {
                if (key($failed['page']) == 'Integer') {
                    $this->code = 440407;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['page']) == 'Min') {
                    $this->code = 440408;
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
    public function orderInfo(Request $request)
    {
        $rules = [
            'orderId' => 'required|integer',
        ];
        $message = [
            'orderId.required' => '获取订单参数失败',
            'orderId.integer' => '订单参数类型不正确',
        ];
        $input = $request->only(['orderId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $supplierOrdersInfoModel = new SupplierOrdersInfoModel();
            $session = $request->session()->get(parent::pasn);
            $input['projectId'] = $session['projectId'];
            $this->data = $supplierOrdersInfoModel->infoLists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'orderId') {
                if (key($failed['orderId']) == 'Required') {
                    $this->code = 440501;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['orderId']) == 'Integer') {
                    $this->code = 440502;
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
    public function addOrder(Request $request)
    {
        $rules = [
            'supplierId' => 'required|integer',
            'totalPrice' => 'required|numeric',
            'deliveryTIme' => 'required|date_format:Y-m-d',
            'payType' => 'required|integer|in:1,2',
            'data' => 'required|array'
        ];
        $message = [
            'supplierId.required' => '获取供应商参数失败',
            'supplierId.integer' => '供应商参数类型不正确',
            'totalPrice.required' => '请填写总价',
            'totalPrice.numeric' => '总价参数类型不正确',
            'deliveryTIme.required' => '请选择交货时间',
            'deliveryTIme.date_format' => '交货时间格式不正确',
            'payType.required' => '获取付款方式参数失败',
            'payType.integer' => '付款方式参数类型不正确',
            'data.required' => '请填写材料信息',
            'data.array' => '材料信息类型不正确',
        ];
        $input = $request->only(['supplierId', 'totalPrice', 'deliveryTIme', 'payType', 'data']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $data = $input['data'];
            unset($input['data']);
            $rules1 = [
                'materialId' => 'required|integer',
                'specId' => 'required|integer',
                'amount' => 'required|integer',
                'price' => 'required|numeric',
                'totalPrice' => 'required|numeric'
            ];
            $message1 = [
                'materialId.required' => '请选择材料',
                'materialId.integer' => '材料参数类型不正确',
                'specId.required' => '请选择材料规格',
                'specId.integer' => '材料规格参数类型不正确',
                'amount.required' => '请填写材料数量',
                'amount.integer' => '材料数量参数类型不正确',
                'price.required' => '请填写材料单价',
                'price.numeric' => '材料单价参数类型不正确',
                'totalPrice.required' => '请填写材料总价',
                'totalPrice.numeric' => '材料总价参数类型不正确',
            ];
            $option = true;
            foreach ($data as $d) {
                $validator1 = Validator::make($d, $rules1, $message1);
                $failed = $validator1->failed();
                if ($validator1->fails()) {
                    $option = false;
                    if (key($failed) == 'materialId') {
                        if (key($failed['materialId']) == 'Required') {
                            $this->code = 440610;
                            $this->msg = $validator->errors()->first();
                            break;
                        }
                        if (key($failed['materialId']) == 'Integer') {
                            $this->code = 440611;
                            $this->msg = $validator->errors()->first();
                            break;
                        }
                    } elseif (key($failed) == 'specId') {
                        if (key($failed['specId']) == 'Required') {
                            $this->code = 440612;
                            $this->msg = $validator->errors()->first();
                            break;
                        }
                        if (key($failed['specId']) == 'Integer') {
                            $this->code = 440613;
                            $this->msg = $validator->errors()->first();
                            break;
                        }
                    } elseif (key($failed) == 'amount') {
                        if (key($failed['amount']) == 'Required') {
                            $this->code = 440614;
                            $this->msg = $validator->errors()->first();
                            break;
                        }
                        if (key($failed['amount']) == 'Integer') {
                            $this->code = 440615;
                            $this->msg = $validator->errors()->first();
                            break;
                        }
                    } elseif (key($failed) == 'price') {
                        if (key($failed['price']) == 'Required') {
                            $this->code = 440616;
                            $this->msg = $validator->errors()->first();
                            break;
                        }
                        if (key($failed['price']) == 'Numeric') {
                            $this->code = 440617;
                            $this->msg = $validator->errors()->first();
                            break;
                        }
                    } elseif (key($failed) == 'totalPrice') {
                        if (key($failed['totalPrice']) == 'Required') {
                            $this->code = 440618;
                            $this->msg = $validator->errors()->first();
                            break;
                        }
                        if (key($failed['totalPrice']) == 'Numeric') {
                            $this->code = 440619;
                            $this->msg = $validator->errors()->first();
                            break;
                        }
                    }
                }
            }
            if ($option) {
                $supplierOrdersModel = new SupplierOrdersModel();
                $session = $request->session()->get(parent::pasn);
                $input['projectId'] = $session['projectId'];
                $orderId = $supplierOrdersModel->insert($input);
                if ($orderId > 0) {
                    foreach ($data as $key => $d) {
                        $data[$key]['supplierId'] = $input['supplierId'];
                        $data[$key]['orderId'] = $input['orderId'];
                    }
                    $supplierOrdersInfoModel = new SupplierOrdersInfoModel();
                    $supplierOrdersInfoModel->insert($data);
                } else {
                    $this->code = 440620;
                    $this->msg = '订单添加失败，请稍后重试';
                }
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'supplierId') {
                if (key($failed['supplierId']) == 'Required') {
                    $this->code = 440601;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['supplierId']) == 'Integer') {
                    $this->code = 440602;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'totalPrice') {
                if (key($failed['totalPrice']) == 'Required') {
                    $this->code = 440603;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['totalPrice']) == 'Numeric') {
                    $this->code = 440604;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'deliveryTIme') {
                if (key($failed['deliveryTIme']) == 'Required') {
                    $this->code = 440605;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['deliveryTIme']) == 'DateFormat') {
                    $this->code = 440606;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'payType') {
                if (key($failed['payType']) == 'Required') {
                    $this->code = 440607;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['payType']) == 'Integer') {
                    $this->code = 440608;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['payType']) == 'In') {
                    $this->code = 440609;
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
    public function batchRepay(Request $request)
    {
        $rules = [
            'orderIds' => 'required|array',
            'supplierId' => 'required|integer',
            'account'=>'required|numeric',
            'repayTime'=>'required|date_format:Y-m-d',
        ];
        $message = [
            'orderIds.required' => '获取订单参数失败',
            'orderIds.array' => '订单参数类型不正确',
            'supplierId.required' => '获取供应商参数失败',
            'supplierId.integer' => '供应商参数类型不正确',
            'account.required' => '请填写还款金额',
            'account.numeric' => '还款金额类型不正确',
            'repayTime.required' => '请选择还款时间',
            'repayTime.date_format' => '还款时间格式不正确',
        ];
        $input = $request->only(['orderIds','supplierId','account','repayTime','remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $orderIds= $input['orderIds'];
            unset($input['orderIds']);
            $supplierRepaymentModel = new SupplierRepaymentModel();
            $session = $request->session()->get(parent::pasn);
            $input['projectId'] = $session['projectId'];
            $supplierRepaymentModel->insert($input);
            $supplierOrdersModel = new SupplierOrdersModel();
            foreach ($orderIds as $orderId){
                $supplierOrdersModel->update($orderId,['isPay'=>1]);
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'orderIds') {
                if (key($failed['orderIds']) == 'Required') {
                    $this->code = 440701;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['orderIds']) == 'Array') {
                    $this->code = 440702;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'supplierId') {
                if (key($failed['supplierId']) == 'Required') {
                    $this->code = 440703;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['supplierId']) == 'Integer') {
                    $this->code = 440704;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'account') {
                if (key($failed['account']) == 'Required') {
                    $this->code = 440705;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['account']) == 'Numeric') {
                    $this->code = 440706;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'repayTime') {
                if (key($failed['repayTime']) == 'Required') {
                    $this->code = 440707;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['repayTime']) == 'DateFormat') {
                    $this->code = 440708;
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
    public function repaymentLists(Request $request){
        $rules = [
            'supplierId' => 'required|integer',
            'limit' => 'nullable|integer|in:10,20,50',
            'page' => 'nullable|integer|min:1',
        ];
        $message = [
            'supplierId.required' => '获取供应商参数失败',
            'supplierId.integer' => '供应商参数类型不正确',
            'limit.integer' => '记录条数参数类型错误',
            'limit.in' => '记录条数参数值不正确',
            'page.integer' => '页码参数类型错误',
            'page.min' => '页码参数值不小于:min',
        ];
        $input = $request->only(['supplierId','month','limit','page']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $supplierRepaymentModel = new SupplierRepaymentModel();
            $this->data = $supplierRepaymentModel->lists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'supplierId') {
                if (key($failed['supplierId']) == 'Required') {
                    $this->code = 440801;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['supplierId']) == 'Integer') {
                    $this->code = 440802;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'limit') {
                if (key($failed['limit']) == 'Integer') {
                    $this->code = 440803;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['limit']) == 'In') {
                    $this->code = 440804;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'page') {
                if (key($failed['page']) == 'Integer') {
                    $this->code = 440805;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['page']) == 'Min') {
                    $this->code = 440806;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}