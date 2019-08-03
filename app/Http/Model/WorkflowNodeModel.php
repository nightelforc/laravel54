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
        $field = [$this->table.'.*','a.name as adminName','p.name as projectName'];
        return DB::table($this->table)
            ->leftJoin('admin as a','a.id','=',$this->table.'.handler')
            ->leftJoin('project as p','a.projectId','=','p.id')
            ->orderBy('order','asc')
            ->where($this->table.'.workflowId',$data['workflowId'])
            ->where($this->table.'.projectId',$data['projectId'])
            ->get($field)->toArray();
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

    public function del(array $data)
    {
        return DB::table($this->table)->where($data)->delete();
    }
}