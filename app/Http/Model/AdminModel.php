<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/18
 * Time: 9:48
 */

namespace App\Http\Model;

use Illuminate\Support\Facades\DB;

class AdminModel
{
    private $table = 'admin';

    /**
     * 登录
     *
     * @param mixed $data
     * @return mixed
     */
    public function login($data)
    {
        $where = $this->encrypt($data);
        $info = $this->info($where);
        if (!empty($info)){
            $this->update($info['id'],['lastLoginTime'=>date('Y-m-d H:i:s')]);
        }
        return $info;
    }

    /**
     * 修改密码
     *
     * @param mixed $data
     * @return mixed
     */
    public function changePwd($data){
        $data = $this->encrypt($data);
        return DB::table($this->table)->where('id',$data['id'])->update(['password'=>$data['password']]);
    }

    /**
     * 获取管理员信息
     *
     * @param array $where
     * @return array
     */
    public function info($where=[]){
        $id = '';
        if(isset($where['id'])){
            $id = $where['id'];
            unset($where['id']);
        }
        $result = DB::table($this->table)
            ->leftJoin('project as p','p.id','=',$this->table.'.projectId')
            ->where($where)
            ->where(function ($query) use ($id){
                if (!empty($id)){
                    $query->where($this->table.'.id',$id);
                }
            })
            ->select([$this->table.'.id','username',$this->table.'.name','projectId','phone','lastLoginTime',$this->table.'.status','p.name as projectName'])
            ->first();
        return empty($result) ? [] : get_object_vars($result);
    }

    /**
     * 处理数据加密
     *
     * @param $data
     * @return string
     */
    private function encrypt($data)
    {
        $variableType = gettype($data);
        if ($variableType === 'string') {
            $data = $this->encryptStr($data);
        }elseif($variableType === 'array'){
            if (isset($data['password'])){
                $data['password'] = $this->encryptStr($data['password']);
            }
        }elseif($variableType === 'object'){
            if (isset($data->password)){
                $data->password = $this->encryptStr($data->password);
            }
        }
        return $data;
    }

    /**
     * 加密方式
     *
     * @param string $string
     * @param string $salt
     * @return string
     */
    private function encryptStr($string, $salt = '')
    {
        if ($salt == ''){
            return md5($string);
        }else{
            return md5($string.$salt);
        }
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function lists(array $input)
    {
        $limit = config('yucheng.limit');
        $start = is_null($input['start']) ? 0 : $input['start'];

        if (isset($input['length']) && !is_null($input['length'])) {
            $limit = $input['length'];
        }

        return DB::table($this->table)
            ->leftJoin('admin_role as ar','ar.adminId','=',$this->table.'.id')
            ->leftJoin('project as p','p.id','=',$this->table.'.projectId')
            ->leftJoin('role as r','r.id','=','ar.roleId')
            ->where(function($query) use ($input){
                if (isset($input['projectId']) && !is_null($input['projectId'])){
                    $query->where('projectId',$input['projectId']);
                }
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where($this->table.'.username', 'like', '%' . $input['search'] . '%');
                }
            })
            ->offset($start)->limit($limit)
            ->select($this->table.'.*','p.name as projectName','r.name as roleName')
            ->get()->toArray();
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function countLists(array $input)
    {
        return DB::table($this->table)
            ->leftJoin('admin_role as ar','ar.adminId','=',$this->table.'.id')
            ->leftJoin('project as p','p.id','=',$this->table.'.projectId')
            ->leftJoin('role as r','r.id','=','ar.roleId')
            ->where(function($query) use ($input){
                if (isset($input['projectId']) && !is_null($input['projectId'])){
                    $query->where('projectId',$input['projectId']);
                }
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where($this->table.'.username', 'like', '%' . $input['search'] . '%');
                }
            })
            ->select($this->table.'.*','p.name as projectName','r.name as roleName')
            ->count();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data){
        $data['password'] = $this->encrypt('123456');
        return DB::table($this->table)->insertGetId($data);
    }

    /**
     * @param $pk
     * @param $data
     * @return mixed
     */
    public function update($pk,$data){
        return DB::table($this->table)->where('id',$pk)->update($data);
    }

    /**
     * @param array $data
     * @return string
     */
    public function addAdmin(array $data)
    {
        try{
            DB::transaction(function () use($data){
                $roleId = $data['roleId'];
                unset($data['roleId']);
                $insertId = $this->insert($data);
                $adminRoleModel = new AdminRoleModel();
                $adminRoleModel->insert(['adminId'=>$insertId,'roleId'=>$roleId]);

            });
            return true;
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * @param array $data
     * @return bool|string
     */
    public function editAdmin(array $data)
    {
        try{
            DB::transaction(function () use($data){
                $id = $data['id'];
                $roleId = $data['roleId'];
                unset($data['id']);
                unset($data['roleId']);
                $this->update($id,$data);
                $adminRoleModel = new AdminRoleModel();
                $info = $adminRoleModel->info(['adminId'=>$id,'roleId'=>$roleId]);
                if (empty($info)){
                    $adminRoleModel->delete(['adminId'=>$id]);
                    $adminRoleModel->insert(['adminId'=>$id,'roleId'=>$roleId]);
                }else{
                    $adminRoleModel->update($id,['roleId'=>$roleId]);
                }
            });
        }catch (\Exception $e){
            return $e->getMessage();
        }
        return true;
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function updateStatus($id,array $data)
    {
        return DB::table($this->table)->where('id',$id)->update($data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function checkRepeat(array $data)
    {
        return DB::table($this->table)
            ->where('username',$data['username'])
            ->orWhere('name',$data['name'])
            ->get()->toArray();
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function selectLists(array $input)
    {
        return DB::table($this->table)
            ->where(function ($query) use ($input){
                $query->where('projectId',$input['projectId'])->where('status',1);
            })
            ->get()->toArray();
    }

}