<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/30
 * Time: 11:25
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class SupplierRepaymentModel
{
    private $table = 'supplier_repayment';

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        $data['createTime'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->insert($data);
    }

    /**
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function lists(array $data)
    {
        $limit = config('yucheng.limit');
        $start = is_null($data['start']) ? 0 : $data['start'];

        if (isset($input['length']) && !is_null($data['length'])) {
            $limit = $data['length'];
        }

        if (empty($data['month'])){
            $startTime = date('Y-01-01 00:00:00');
            $endTime = date('Y-12-31 23:59:59');
        }else{
            $startTime = $data['month'].'-01 00:00:00';
            $endTime = (new \DateTime($data['month']))->format('Y-m-t 23:59:59');
        }
        return DB::table($this->table)
            ->where('supplierId',$data['supplierId'])
            ->where('repayTime','>',$startTime)
            ->where('repayTime','<',$endTime)
            ->orderBy('createTime','desc')
            ->offset($start)->limit($limit)->get()->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function countLists(array $data)
    {
        if (empty($data['month'])){
            $startTime = date('Y-01-01 00:00:00');
            $endTime = date('Y-12-31 23:59:59');
        }else{
            $startTime = $data['month'].'-01 00:00:00';
            $endTime = (new \DateTime($data['month']))->format('Y-m-t 23:59:59');
        }
        return DB::table($this->table)
            ->where('supplierId',$data['supplierId'])
            ->where('repayTime','>',$startTime)
            ->where('repayTime','<',$endTime)
            ->count();
    }

    /**
     * @param array $input
     * @return bool
     */
    public function batchRepay(array $input)
    {
        $orderIds = $input['orderIds'];
        unset($input['orderIds']);
        $supplierOrdersModel = new SupplierOrdersModel();

        foreach ($orderIds as $orderId) {
            $info = $supplierOrdersModel->info(['id'=>$orderId]);
            if ($info['isPay'] == 1){
                return false;
            }
        }

        $this->insert($input);
        foreach ($orderIds as $orderId) {
            $supplierOrdersModel->update($orderId, ['isPay' => 1]);
        }
        return true;
    }

}