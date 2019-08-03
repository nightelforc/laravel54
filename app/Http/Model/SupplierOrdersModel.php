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
        $start = 0;

        if (isset($input['limit']) && !is_null($input['limit'])) {
            $limit = $input['limit'];
        }

        if (isset($input['page']) && !is_null($input['page'])) {
            $start = ($input['page'] - 1) * $limit;
        }

        return DB::table($this->table)
            ->leftJoin('project as p','p.id','=',$this->table.'.projectId')
            ->where(function ($query) use ($input) {
                $query->where('supplierId', $input['supplierId']);
                if (isset($input['isPay']) && !is_null($input['isPay'])) {
                    $query->where('isPay', $input['isPay']);
                }
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where('ordersn', 'like', '%' . $input['search'] . '%');
                }
            })
            ->offset($start)->limit($limit)
            ->select($this->table.'.*','p.name as projectName')
            ->get()->toArray();
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
        $data['createTime'] = date('Y-m-d');
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
}