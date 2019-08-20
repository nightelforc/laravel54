<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/24
 * Time: 14:45
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class ProjectGroupModel
{
    private $table = 'project_group';
    private $startTime = '';
    private $endTime = '';
    public function __construct()
    {
        $yearModel = new YearModel();
        $info = get_object_vars($yearModel->findYear(date('Y-m-d H:i:s')));
        $this->startTime = $info['startTime'];
        $this->endTime = $info['endTime'];
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function lists(array $data)
    {
        return DB::table($this->table)
            ->leftJoin('profession as p','p.id','=',$this->table.'.professionId')
            ->leftJoin('project','project.id','=',$this->table.'.projectId')
            ->leftJoin('employee as e','e.id',$this->table.'.groupLeader')
            ->where(function ($query) use ($data){
                $query->where($this->table.'.projectId',$data['projectId'])
                    ->whereIn($this->table.'.status',[1,2])
                    ->where($this->table.'.createTime','>',$this->startTime)
                    ->where($this->table.'.createTime','<',$this->endTime);;
                if (isset($data['professionId']) && !is_null($data['professionId'])) {
                    $query->where($this->table.'.professionId', $data['professionId']);
                }
                if (isset($data['search']) && !is_null($data['search'])) {
                    $query->where($this->table.'.name','like','%'. $data['search'].'%');
                }
            })
            ->select($this->table.'.*','p.name as professionName','e.name as employeeName','project.name as projectName')
            ->get()->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        $data['createTime'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->insert($data);
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
        $updateData = [
            'name'=>$data['name'],
            'professionId'=>$data['professionId'],
        ];
        return DB::table($this->table)->where('id',$data['id'])->update($updateData);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function updateStatus($data)
    {
        return DB::table($this->table)->where('id', $data['id'])->update(['status' => $data['status']]);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function updateIsLeader($data)
    {
        return DB::table($this->table)->where('id', $data['groupId'])->update(['groupLeader' => $data['groupLeader']]);
    }
}