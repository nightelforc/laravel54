<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/22
 * Time: 9:24
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class WorkflowNodeModel extends Model
{
    private $table = 'workflow_node';

    /**
     * @param array $data
     * @return mixed
     */
    public function showLists(array $data)
    {
        return DB::table($this->table)
            ->leftJoin('admin as a','a.id','=',$this->table.'.handler')
            ->leftJoin('project as p','a.projectId','=','p.id')
            ->where($this->table.'.workflowId',$data['workflowId'])
            ->where(function ($query) use ($data){
                if (!empty($data['search'])){
                    $query->where('p.name','like','%'.$data['search'].'%');
                }
            })
            ->groupBy($this->table.'.projectId')
            ->select(DB::raw($this->table.".*,p.name as projectName, GROUP_CONCAT(a.username order by `order` SEPARATOR '->') as handlerNameList,GROUP_CONCAT(handler order by `order`) as handlerList"))
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function handlerLists(array $data){
        return DB::table($this->table)
            ->orderBy('order','asc')
            ->where($this->table.'.workflowId',$data['workflowId'])
            ->where($this->table.'.projectId',$data['projectId'])
            ->pluck('handler')->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        $i = 0;
        foreach ($data as $key=>$d){
            $data[$key]['order'] = $i++;
        }
        return DB::table($this->table)->insert($data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function checkNodeOrder(array $data)
    {
        $where = [
            'projectId'=>$data['projectId'],
            'workflowId'=>$data['workflowId'],
            'order'=>$data['order'],
        ];
        return DB::table($this->table)->where($where)->count();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function checkNodeExist(array $data)
    {
        $where = [
            'projectId'=>$data['projectId'],
            'workflowId'=>$data['workflowId'],
            'handler'=>$data['handler'],
        ];
        return DB::table($this->table)->where($where)->count();
    }

    /**
     * @param array $input
     * @return bool
     */
    public function delete(array $input)
    {
        $handlerList = explode(',',$input['handlerList']);
        if (!empty($handlerList)){
            foreach ($handlerList as $h){
                $data = [
                    'projectId'=>$input['projectId'],
                    'workflowId'=>$input['workflowId'],
                    'handler'=>$h,
                ];
                DB::table($this->table)->where($data)->delete();
            }
        }
        return true;
    }
}