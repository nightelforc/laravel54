<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/20
 * Time: 10:03
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class EmployeeLivingModel
{
    private $table = 'employee_living';

    /**
     * @param $id
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    public function getLiving($id, $startTime, $endTime)
    {
        return DB::table($this->table)
            ->where('employeeId',$id)
            ->where('livingTime','>',$startTime)
            ->where('livingTime','<',$endTime)
            ->where('status',1)
            ->get()->toArray();
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function insert(array $input)
    {
        $input['createTime'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->insertGetId($input);
    }

    /**
     * @param $pk
     * @param $data
     * @param $approvalResult
     */
    public function livingApproval($pk,$data,$approvalResult){
        DB::table($this->table)->where('id',$pk)->update(['status'=>$approvalResult]);
    }
}