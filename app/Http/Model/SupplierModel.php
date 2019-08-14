<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/27
 * Time: 10:28
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class SupplierModel
{
    private $table = 'supplier';

    /**
     * @param array $data
     * @return mixed
     */
    public function lists(array $data)
    {
        $limit = config('yucheng.limit');
        $start = is_null($data['start']) ? 0 : $data['start'];

        if (isset($data['length']) && !is_null($data['length'])) {
            $limit = $data['length'];
        }
        return DB::table($this->table)
            ->where(function($query) use ($data){
                if (isset($data['search']) && !is_null($data['search'])) {
                    $query->where('name', 'like', '%' . $data['search'] . '%');
                }
            })
            ->offset($start)->limit($limit)
            ->get()->toArray();
    }

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
     * @return array
     */
    public function info(array $data)
    {
        $result = DB::table($this->table)->where($data)->first();
        return empty($result) ? [] : get_object_vars($result);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function update(array $data)
    {
        $id = $data['id'];
        unset($data['id']);
        return DB::table($this->table)->where('id',$id)->update($data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    private function delete(array $data)
    {
        return DB::table($this->table)->where($data)->delete();
    }

    /**
     * @param array $data
     * @return bool
     */
    public function deleteSupplier(array $data)
    {
        $supplierOrdersModel = new SupplierOrdersModel();
        $count = $supplierOrdersModel->countSupplierOrders($data['id']);
        if ($count== 0){
            $this->delete($data);
            return true;
        }else{
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function selectLists()
    {
        return DB::table($this->table)
            ->get()->toArray();
    }


}