<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/24
 * Time: 13:52
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class ProjectModel
{
    private $table = 'project';
    const TABLE = 'project';

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
            ->where(function ($query) use ($data){
                $query->where('id','!=',1);
                if (isset($data['search']) && !is_null($data['search'])) {
                    $query->where(function ($query1) use ($data) {
                        $query1->where($this->table . '.name', 'like', '%' . $data['search'] . '%');
                    });
                }
            })
            ->orderBy('createTime','desc')
            ->offset($start)->limit($limit)
            ->get()->toArray();
    }

    public function insert(array $data)
    {
        $data['createTime'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->insertGetId($data);
    }

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
        $updateData = [
            'name'=>$data['name'],
            'city'=>$data['city'],
            'projectAmount'=>$data['projectAmount'],
            'projectAccount'=>$data['projectAccount'],
        ];
        return DB::table($this->table)->where('id',$data['id'])->update($updateData);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function updateStatus($data)
    {
        return DB::table($this->table)->where('id', $data['id'])->update(['status' => 2]);
    }

    /**
     * @return mixed
     */
    public function selectLists()
    {
        return DB::table($this->table)->where('id','>',1)->get()->toArray();
    }


    /**
     * @param array $data
     * @return mixed
     */
    public function countLists(array $data)
    {
        return DB::table($this->table)
            ->where(function ($query) use ($data){
                $query->where('id','!=',1);
                if (isset($data['search']) && !is_null($data['search'])) {
                    $query->where(function ($query1) use ($data) {
                        $query1->where($this->table . '.name', 'like', '%' . $data['search'] . '%');
                    });
                }
            })
            ->count();
    }

    /**
     * @param array $input
     * @return bool
     */
    public function delete(array $input)
    {
        //检查该项目下是否导入人员
        $employeeModel = new EmployeeModel();
        $counts = $employeeModel->countLists(['projectId'=>$input['id']],$employeeModel->employeeStatus);
        if ($counts != 0){
            return false;
        }
        //检查该项目下是否设置施工区
        $projectAreaModel = new ProjectAreaModel();
        $counts = $projectAreaModel->countLists(['projectId'=>$input['id']]);
        if ($counts != 0){
            return false;
        }
        //检查该项目下是否有供应商货单
        $supplierOrderModel = new SupplierOrdersModel();
        $counts = $supplierOrderModel->countLists(['projectId'=>$input['id']]);
        if ($counts != 0){
            return false;
        }

        DB::table($this->table)->where($input)->delete();
        return true;
    }

    /**
     * @param array $data
     * @param $string
     * @return mixed
     */
    public static function getValue(array $data, $string)
    {
        return DB::table(self::TABLE)->where($data)->value($string);
    }
}