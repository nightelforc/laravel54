<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/25
 * Time: 15:39
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class ProjectBudgetModel
{
    private $table = 'project_budget';

    /**
     * @param array $data
     * @return mixed
     */
    public function budgetLists(array $data)
    {
        return DB::table($this->table)
            ->leftJoin('profession as p','p.id','=',$this->table.'.professionId')
            ->where('sectionId', $data['sectionId'])
            ->get([$this->table.'.*','p.name as professionName'])->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function editBudget(array $data)
    {
        $insertData = [];
        foreach ($data['data'] as  $d){
            $insertData = [
                'projectId'=>$data['projectId'],
                'areaId'=>$data['areaId'],
                'sectionId'=>$data['sectionId'],
                'professionId'=>$d['professionId'],
                'amount'=>$d['amount'],
                'price'=>$d['price'],
                'totalPrice'=>$d['totalPrice'],
            ];
            $info = $this->info([
                'projectId'=>$data['projectId'],
                'areaId'=>$data['areaId'],
                'sectionId'=>$data['sectionId'],
                'professionId'=>$d['professionId'],
            ]);
            if (empty($info)){
                DB::table($this->table)->insert($insertData);
            }else{
                DB::table($this->table)->where('id',$info['id'])->update($insertData);
            }
        }
        return true;
    }

    /**
     * @param array $data
     * @return array
     */
    public function info(array $data){
        $result = DB::table($this->table)->where($data)->first();
        return empty($result) ? [] : get_object_vars($result);
    }
}