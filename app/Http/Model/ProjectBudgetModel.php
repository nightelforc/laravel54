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
}