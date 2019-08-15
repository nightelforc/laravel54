<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/1
 * Time: 9:28
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class WarehouseModel
{
    private $table = 'warehouse';

    public function lists(array $input)
    {
        $limit = config('yucheng.limit');
        $start = is_null($input['start']) ? 0 : $input['start'];

        if (isset($input['limit']) && !is_null($input['limit'])) {
            $limit = $input['limit'];
        }

        return DB::table($this->table)
            ->leftJoin('material as m','m.id','=',$this->table.'.materialId')
            ->leftJoin('material_spec as ms','ms.id','=',$this->table.'.specId')
            ->leftJoin('supplier as s','s.id','=',$this->table.'.supplierId')
            ->leftJoin('unit as u','u.id','=',$this->table.'.unitId')
            ->where(function ($query) use ($input) {
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where($this->table.'.name', 'like', '%' . $input['search'] . '%');
                }
            })
            ->offset($start)->limit($limit)
            ->select($this->table.'.*','m.name as materialName','ms.spec','ms.brand','s.name as supplierName','u.name as unitName')
            ->get()->toArray();
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
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data)
    {
        return DB::table($this->table)->where('id',$id)->update($data);
    }

    public function insert(array $data){
        return DB::table($this->table)->insert($data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function search(array $data)
    {
        return DB::table($this->table)
            ->leftJoin('material as m','m.id','=',$this->table.'.materialId')
            ->where(function ($query) use($data){
                if (isset($data['projectId']) && !is_null($data['projectId'])){
                    $query->where('projectId',$data['projectId']);
                }
                if (isset($data['supplierId']) && !is_null($data['supplierId'])){
                    $query->where('supplierId',$data['supplierId']);
                }
                if (isset($data['materialId']) && !is_null($data['materialId'])){
                    $query->where('materialId',$data['materialId']);
                }
                if (isset($data['specId']) && !is_null($data['specId'])){
                    $query->where('specId',$data['specId']);
                }
                if (isset($data['search']) && !is_null($data['search'])){
                    $query->where('m.name','like','%'.$data['specId'].'%');
                }
            })
            ->select($this->table.'.*','m.name as materialName')
            ->get()->toArray();
    }
}