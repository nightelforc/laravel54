<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/1
 * Time: 12:03
 */

namespace App\Http\Model;

use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;

class WarehouseLogModel
{
    private $table = 'warehouse_log';

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        $data['createTime'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->insertGetId($data);
    }

    /**
     * @param array $data
     * @return string
     */
    public function addLog(array $data)
    {
        $insertId = '';
        try {
            $insertId = DB::transaction(function () use ($data, $insertId) {
                $warehouseLog = [
                    'projectId' => $data['projectId'],
                    'time' => $data['time'],
                    'remark' => $data['remark'],
                    'type' => $data['type'],
                ];
                if (isset($data['sourceEmployeeId'])){
                    $warehouseLog['sourceEmployeeId'] = $data['sourceEmployeeId'];
                }

                if (isset($data['sourceProjectId'])){
                    $warehouseLog['sourceProjectId'] = $data['sourceProjectId'];
                }

                $insertId = $this->insert($warehouseLog);
                $warehouseLogInfoModel = new WarehouseLogInfoModel();
                foreach ($data['data'] as $d){
                    $warehouseLogInfo = [
                        'logId' => $insertId,
                        'materialId' => $d['materialId'],
                        'specId' => $d['specId'],
                        'supplierId' => $d['supplierId'],
                        'amount' => $d['amount'],
                        'price' => $d['price'],
                        'totalPrice' => $d['totalPrice'],
                    ];
                    $warehouseLogInfoModel->insert($warehouseLogInfo);
                }

                return $insertId;
            });
            return $insertId;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id, $data)
    {
        return DB::table($this->table)->where('id', $id)->update($data);
    }

    /**
     * @param $pk
     * @param $data
     * @param $approvalResult
     */
    public function consumeApproval($pk, $data, $approvalResult)
    {
        if ($approvalResult != 1) {
            $this->update($pk, ['status' => $approvalResult]);
        } else {
            try {
                $warehouseModel = new WarehouseModel();
                //检查库存
                foreach ($data['data'] as $d) {
                    $warehouseInfo = $warehouseModel->info([
                        'projectId' => $data['projectId'],
                        'materialId' => $d['materialId'],
                        'specId' => $d['specId'],
                        'supplierId' => $d['supplierId'],
                    ]);
                    if (empty($warehouseInfo) || $warehouseInfo['amount'] < $d['amount']) {
                        throw new \Exception('库存不足', 'consume1');
                    }
                }

                DB::transaction(function () use ($data, $warehouseModel, $pk, $approvalResult) {

                    //扣除库存
                    foreach ($data['data'] as $key => $d) {
                        $warehouseInfo = $warehouseModel->info([
                            'projectId' => $data['projectId'],
                            'materialId' => $d['materialId'],
                            'specId' => $d['specId'],
                            'supplierId' => $d['supplierId'],
                        ]);
                        $newAmount = $warehouseInfo['amount'] - $d['amount'];
                        $data['data'][$key]['purchasePrice'] = $warehouseInfo['purchasePrice'];
                        $data['data'][$key]['salePrice'] = $warehouseInfo['salePrice'];
                        $warehouseModel->update($warehouseInfo['id'], ['amount' => $newAmount, 'updateTime' => date('Y-m-d H:i:s')]);
                    }
                    //修改出入库记录审批状态
                    $this->update($pk, ['status' => $approvalResult]);
                    //添加员工材料费
                    $materialOrder = [
                        'employeeId' => $data['sourceEmployeeId'],
                        'projectId' => $data['projectId'],
                        'account' => $data['price'],
                        'logId' => $pk
                    ];
                    $insertId = (new EmployeeMaterialOrderModel())->insert($materialOrder);

                    $materialOrderInfo = [];
                    foreach ($data['data'] as $key => $d) {
                        $materialOrderInfo[] = [
                            'projectId' => $data['projectId'],
                            'orderId' => $insertId,
                            'materialId' => $d['materialId'],
                            'specId' => $d['specId'],
                            'supplierId' => $d['supplierId'],
                            'amount' => $d['amount'],
                            'purchasePrice' => $d['purchasePrice'],
                            'salePrice' => $d['salePrice'],
                            'totalPrice' => $d['price'],
                            'saleTime' => $data['time'],
                        ];
                    }

                    (new EmployeeMaterialOrderInfoModel())->insert($materialOrderInfo);
                });
            } catch (\Exception $e) {
                $logFile = fopen(
                    storage_path('warehouse_consume.log'),
                    'a+'
                );
                fwrite($logFile, 'approval ' . $pk . ' [' . $e->getCode() . ']' . $e->getMessage());
                fclose($logFile);
            }
        }

    }

    /**
     * @param $pk
     * @param $data
     * @param $approvalResult
     */
    public function expendApproval($pk, $data, $approvalResult)
    {
        if ($approvalResult != 1) {
            $this->update($pk, ['status' => $approvalResult]);
        } else {
            try {
                $warehouseModel = new WarehouseModel();
                //检查库存
                foreach ($data['data'] as $d) {
                    $warehouseInfo = $warehouseModel->info([
                        'projectId' => $data['projectId'],
                        'materialId' => $d['materialId'],
                        'specId' => $d['specId'],
                        'supplierId' => $d['supplierId'],
                    ]);
                    if (empty($warehouseInfo) || $warehouseInfo['amount'] < $d['amount']) {
                        throw new \Exception('库存不足', 'consume1');
                    }
                }

                DB::transaction(function () use ($data, $warehouseModel, $pk, $approvalResult) {

                    //扣除库存
                    foreach ($data['data'] as $key => $d) {
                        $warehouseInfo = $warehouseModel->info([
                            'projectId' => $data['projectId'],
                            'materialId' => $d['materialId'],
                            'specId' => $d['specId'],
                            'supplierId' => $d['supplierId'],
                        ]);
                        $newAmount = $warehouseInfo['amount'] - $d['amount'];
                        $data['data'][$key]['purchasePrice'] = $warehouseInfo['purchasePrice'];
                        $data['data'][$key]['salePrice'] = $warehouseInfo['salePrice'];
                        $warehouseModel->update($warehouseInfo['id'], ['amount' => $newAmount, 'updateTime' => date('Y-m-d H:i:s')]);
                    }
                    //修改出入库记录审批状态
                    $this->update($pk, ['status' => $approvalResult]);

                });
            } catch (\Exception $e) {
                $logFile = fopen(
                    storage_path('warehouse_consume.log'),
                    'a+'
                );
                fwrite($logFile, 'approval ' . $pk . ' [' . $e->getCode() . ']' . $e->getMessage());
                fclose($logFile);
            }
        }
    }

    /**
     * @param $pk
     * @param $data
     * @param $approvalResult
     */
    public function breakdownApproval($pk, $data, $approvalResult)
    {
        if ($approvalResult != 1) {
            $this->update($pk, ['status' => $approvalResult]);
        } else {
            try {
                $warehouseModel = new WarehouseModel();
                //检查库存
                $warehouseInfo = $warehouseModel->info([
                    'projectId' => $data['projectId'],
                    'materialId' => $data['materialId'],
                    'specId' => $data['specId'],
                    'supplierId' => $data['supplierId'],
                ]);
                if (empty($warehouseInfo) || $warehouseInfo['amount'] < $data['amount']) {
                    throw new \Exception('库存不足', 'consume1');
                }

                DB::transaction(function () use ($data, $warehouseModel, $pk, $approvalResult) {
                    //扣除库存
                    $warehouseInfo = $warehouseModel->info([
                        'projectId' => $data['projectId'],
                        'materialId' => $data['materialId'],
                        'specId' => $data['specId'],
                        'supplierId' => $data['supplierId'],
                    ]);
                    $newAmount = $warehouseInfo['amount'] - $data['amount'];

                    $warehouseModel->update($warehouseInfo['id'], ['amount' => $newAmount, 'updateTime' => date('Y-m-d H:i:s')]);
                    //修改出入库记录审批状态
                    $this->update($pk, ['status' => $approvalResult]);

                });
            } catch (\Exception $e) {
                $logFile = fopen(
                    storage_path('warehouse_consume.log'),
                    'a+'
                );
                fwrite($logFile, 'approval ' . $pk . ' [' . $e->getCode() . ']' . $e->getMessage());
                fclose($logFile);
            }
        }
    }

    /**
     * @param $pk
     * @param $data
     * @param $approvalResult
     */
    public function allotApproval($pk, $data, $approvalResult)
    {
        if ($approvalResult != 1) {
            $this->update($pk, ['status' => $approvalResult]);
        } else {
            try {
                $warehouseModel = new WarehouseModel();
                //检查库存
                foreach ($data['data'] as $d) {
                    $warehouseInfo = $warehouseModel->info([
                        'projectId' => $data['projectId'],
                        'materialId' => $d['materialId'],
                        'specId' => $d['specId'],
                        'supplierId' => $d['supplierId'],
                    ]);
                    if (empty($warehouseInfo) || $warehouseInfo['amount'] < $d['amount']) {
                        throw new \Exception('库存不足', 'consume1');
                    }
                }

                DB::transaction(function () use ($data, $warehouseModel, $pk, $approvalResult) {

                    //扣除库存
                    foreach ($data['data'] as $key => $d) {
                        $warehouseInfo = $warehouseModel->info([
                            'projectId' => $data['projectId'],
                            'materialId' => $d['materialId'],
                            'specId' => $d['specId'],
                            'supplierId' => $d['supplierId'],
                        ]);
                        $newAmount = $warehouseInfo['amount'] - $d['amount'];
                        $data['data'][$key]['purchasePrice'] = $warehouseInfo['purchasePrice'];
                        $data['data'][$key]['salePrice'] = $warehouseInfo['salePrice'];
                        $warehouseModel->update($warehouseInfo['id'], ['amount' => $newAmount, 'updateTime' => date('Y-m-d H:i:s')]);
                    }
                    //修改出入库记录审批状态
                    $this->update($pk, ['status' => $approvalResult]);
                });
            } catch (\Exception $e) {
                $logFile = fopen(
                    storage_path('warehouse_consume.log'),
                    'a+'
                );
                fwrite($logFile, 'approval ' . $pk . ' [' . $e->getCode() . ']' . $e->getMessage());
                fclose($logFile);
            }
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    public function purchase(array $data)
    {
        try {
            $result = DB::transaction(function () use ($data) {
                $supplierModel = new SupplierModel();
                $supplierOrdersModel = new SupplierOrdersModel();
                $supplierOrdersInfoModel = new SupplierOrdersInfoModel();
                $warehouseModel = new WarehouseModel();
                foreach ($data['data'] as $key => $d) {
                    //检查供应商
                    if ($d['supplierId'] == 0) {
                        $newSupplier = [
                            'name' => $d['name'],
                            'phone' => $d['phone'],
                            'address' => $d['address'],
                        ];
                        $supplierId = $supplierModel->insert($newSupplier);
                        $data['data'][$key]['supplierId'] = $supplierId;
                    }
                    //增加供应商货单记录
                    $supplierOrder = [
                        'supplierId' => $d['supplierId'],
                        'projectId' => $data['projectId'],
                        'totalPrice' => $d['totalPrice'],
                        'deliveryTime' => $data['time'],
                        'payType' => $d['payType'],
                    ];
                    $supplierOrderId = $supplierOrdersModel->insert($supplierOrder);
                    $supplierOrdersInfoData = [
                        'orderId' => $supplierOrderId,
                        'materialId' => $d['materialId'],
                        'specId' => $d['specId'],
                        'supplierId' => $d['supplierId'],
                        'amount' => $d['amount'],
                        'price' => $d['price'],
                        'totalPrice' => $d['totalPrice'],
                    ];
                    $supplierOrdersInfoModel->insert($supplierOrdersInfoData);
                    //增加库存，检查仓库中是否有材料，增加库存
                    $warehouseData = [
                        'projectId' => $data['projectId'],
                        'materialId' => $d['materialId'],
                        'specId' => $d['specId'],
                        'supplierId' => $d['supplierId'],
                    ];
                    $materialInfo = $warehouseModel->info($warehouseData);
                    if (empty($materialInfo)) {
                        //创建仓库新的材料库存
                        $warehouseData = [
                            'projectId' => $data['projectId'],
                            'materialId' => $d['materialId'],
                            'specId' => $d['specId'],
                            'supplierId' => $d['supplierId'],
                            'amount' => $d['amount'],
                            'purchasePrice' => $d['price'],
                            'updateTime' => date('Y-m-d H:i:s')
                        ];
                        $warehouseModel->insert($warehouseData);
                    } else {
                        //更新库存
                        $warehouseModel->update($materialInfo['id'], ['amount' => $d['amount'], 'updateTime' => date('Y-m-d H:i:s')]);
                    }

                    unset($data['data'][$key]['name']);
                    unset($data['data'][$key]['phone']);
                    unset($data['data'][$key]['address']);
                    unset($data['data'][$key]['payType']);
                }
                //增加入库记录
                $r = $this->addLog($data);
                if (!is_int($r)){
                    return $r;
                }
                return true;
            });
            if ($result){
                return true;
            }else{
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    public function receipt(array $data)
    {
        try {
            $result = DB::transaction(function () use ($data) {
                $warehouseModel = new WarehouseModel();
                foreach ($data['data'] as $key => $d) {
                    //增加库存，检查仓库中是否有材料，增加库存
                    $warehouseData = [
                        'projectId' => $data['projectId'],
                        'materialId' => $d['materialId'],
                        'specId' => $d['specId'],
                        'supplierId' => $d['supplierId'],
                    ];
                    $materialInfo = $warehouseModel->info($warehouseData);
                    if (empty($materialInfo)) {
                        //创建仓库新的材料库存
                        $warehouseData = [
                            'projectId' => $data['projectId'],
                            'materialId' => $d['materialId'],
                            'specId' => $d['specId'],
                            'supplierId' => $d['supplierId'],
                            'amount' => $d['amount'],
                            'purchasePrice' => $d['price'],
                            'updateTime' => date('Y-m-d H:i:s')
                        ];
                        $warehouseModel->insert($warehouseData);
                    } else {
                        //更新库存
                        $warehouseModel->update($materialInfo['id'], ['amount' => $d['amount'], 'updateTime' => date('Y-m-d H:i:s')]);
                    }
                }
                //增加入库记录
                $this->addLog($data);
                return true;
            });
            if ($result){
                return true;
            }else{
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }


}