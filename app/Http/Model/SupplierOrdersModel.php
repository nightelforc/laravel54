<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/29
 * Time: 23:00
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class SupplierOrdersModel
{
    private $table = 'supplier_orders';

    /**
     * @param array $input
     * @return mixed
     */
    public function lists(array $input)
    {
        $limit = config('yucheng.limit');
        $start = is_null($input['start']) ? 0 : $input['start'];

        if (isset($input['limit']) && !is_null($input['limit'])) {
            $limit = $input['limit'];
        }

        return DB::table($this->table)
            ->leftJoin('project as p','p.id','=',$this->table.'.projectId')
            ->leftJoin('supplier as s','s.id','=',$this->table.'.supplierId')
            ->where(function ($query) use ($input) {
                if (isset($input['supplierId']) && !is_null($input['supplierId'])) {
                    $query->where('supplierId', $input['supplierId']);
                }
                if (isset($input['projectId']) && !is_null($input['projectId'])) {
                    $query->where('projectId', $input['projectId']);
                }
                if (isset($input['time']) && !is_null($input['time'])) {
                    $startTime = (new \DateTime($input['time']))->format('Y-m-01 00:00:00');
                    $endTime = (new \DateTime($input['time']))->format('Y-m-t 23:59:59');
                }else{
                    $findYear = (new YearModel())->findYear(date('Y-m-d H:i:s', time()));
                    if (empty($findYear)) {
                        $findYear['startTime'] = date('Y-01-01 00:00:00');
                        $findYear['endTime'] = date('Y-12-31 23:59:59');
                    } else {
                        $findYear = get_object_vars($findYear);
                    }
                    $startTime = $findYear['startTime'];
                    $endTime = $findYear['endTime'];
                }
                $query->where('deliveryTime', '>=',$startTime)->where('deliveryTime', '<=',$endTime);

                if (isset($input['isPay']) && !is_null($input['isPay'])) {
                    $query->where('isPay', $input['isPay']);
                }
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where('ordersn', 'like', '%' . $input['search'] . '%');
                }
            })
            ->offset($start)->limit($limit)
            ->select($this->table.'.*','p.name as projectName','s.name as supplierName')
            ->get()->toArray();
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function countLists(array $input)
    {
        return DB::table($this->table)
            ->where(function ($query) use ($input) {
                if (isset($input['supplierId']) && !is_null($input['supplierId'])) {
                    $query->where('supplierId', $input['supplierId']);
                }
                if (isset($input['projectId']) && !is_null($input['projectId'])) {
                    $query->where('projectId', $input['projectId']);
                }
                if (isset($input['time']) && !is_null($input['time'])) {
                    $startTime = (new \DateTime($input['time']))->format('Y-m-01 00:00:00');
                    $endTime = (new \DateTime($input['time']))->format('Y-m-t 23:59:59');
                }else{
                    $findYear = (new YearModel())->findYear(date('Y-m-d H:i:s', time()));
                    if (empty($findYear)) {
                        $findYear['startTime'] = date('Y-01-01 00:00:00');
                        $findYear['endTime'] = date('Y-12-31 23:59:59');
                    } else {
                        $findYear = get_object_vars($findYear);
                    }
                    $startTime = $findYear['startTime'];
                    $endTime = $findYear['endTime'];
                }
                $query->where('deliveryTime', '>=',$startTime)->where('deliveryTime', '<=',$endTime);

                if (isset($input['isPay']) && !is_null($input['isPay'])) {
                    $query->where('isPay', $input['isPay']);
                }
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where('ordersn', 'like', '%' . $input['search'] . '%');
                }
            })
            ->count();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        $data['ordersn'] = time().mt_rand(100,999);
        if ($data['payType'] == 1){
            $data['isPay'] = 1;
        }else{
            $data['isPay'] = 0;
        }
        $data['createTime'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->insertGetId($data);
    }

    /**
     * @param $pk
     * @param $data
     * @return mixed
     */
    public function update($pk,$data){
        return DB::table($this->table)->where('id',$pk)->update($data);
    }

    /**
     * @param $supplierId
     * @return mixed
     */
    public function countSupplierOrders($supplierId){
        return DB::table($this->table)->where('supplierId',$supplierId)->count();
    }


}