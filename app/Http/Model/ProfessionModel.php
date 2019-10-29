<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/19
 * Time: 13:42
 */

namespace App\Http\Model;

use Illuminate\Support\Facades\DB;

class ProfessionModel
{
    private $table = 'profession';
    const TABLE = 'profession';

    /**
     * @return mixed
     */
    public function lists()
    {
        return DB::table($this->table)->orderBy('order','desc')->get()->toArray();
    }

    /**
     * @param array $data
     * @return array
     */
    public function info($data = [])
    {
        $result = DB::table($this->table)->where($data)->first();
        return empty($result) ? [] : get_object_vars($result);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(Array $data)
    {
        if (!isset($data['order']) || empty($data['order'])){
            $order = 1;
        }else{
            $order = $data['order'];
        }
        $insertData = [
            'name' => $data['name'],
            'order' => $order,
        ];
        return DB::table($this->table)->insert($insertData);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function update($data)
    {
        if (!isset($data['order']) || empty($data['order'])){
            $order = 1;
        }else{
            $order = $data['order'];
        }
        $updateData = [
            'name' => $data['name'],
            'order' => $order,
        ];
        return DB::table($this->table)->where('id', $data['id'])->update($updateData);
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
     * @param array $check
     * @param $id
     * @return array
     */
    public function checkRepeat(array $check, $id = 0)
    {
        $result = DB::table($this->table)->where($check)
            ->where(function ($query) use ($id){
                if ($id != 0){
                    $query->where('id','!=',$id);
                }
            })
            ->first();
        return empty($result) ? [] : get_object_vars($result);
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