<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/22
 * Time: 9:54
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class SettingModel
{
    private $table = 'setting';

    private $setting = [
        'saleRate' => '售价加价率',
    ];

    /**
     * @param array $data
     * @return array
     */
    public function info(array $data){
        $result = DB::table($this->table)->where($data)->first();
        return empty($result) ? [] : get_object_vars($result);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function insert($data){
        return DB::table($this->table)->insert($data);
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
     * @param $code
     * @param $value
     * @param int $projectId
     * @return bool
     */
    public function set($code, $value, $projectId = 0)
    {
        $info = $this->info(['code'=>$code,'projectId'=>$projectId]);
        $data = [
            'code'=>$code,
            'value'=>$value,
            'projectId'=>$projectId,
        ];

        if (isset($this->setting['code'])){
            $data['name'] = $this->setting['code'];
        }else{
            $data['name'] = $code;
        }

        if (empty($info)){
            $this->insert($data);
        }else{
            $this->update($info['id'],$data);
        }
        return true;
    }

    /**
     * @param $code
     * @param $projectId
     * @return int|mixed
     */
    public function get($code, $projectId)
    {
        $info = $this->info(['code'=>$code,'projectId'=>$projectId]);
        if (empty($info)){
            return 0;
        }else{
            return $info['value'];
        }
    }
}