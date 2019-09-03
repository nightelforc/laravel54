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
        return DB::table('profession')
            ->leftJoin($this->table,'profession.id','=',$this->table.'.professionId')
            ->where(function ($query) use ($data){
                $query->where($this->table.'.sectionId', $data['sectionId'])->orWhere($this->table.'.sectionId', null);
            })
            ->get([$this->table.'.*','profession.name as professionName','profession.id as professionid'])->toArray();
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
                DB::table($this->table)->where([
                    'projectId'=>$data['projectId'],
                    'areaId'=>$data['areaId'],
                    'sectionId'=>$data['sectionId'],
                    'professionId'=>$d['professionId'],
                ])->delete();
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

    /**
     * @param array $data
     * @return mixed
     */
    public function sumBudget(array $data)
    {
        return DB::table($this->table)->where($data)->sum('totalPrice');
    }
}