<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/31
 * Time: 20:16
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class MaterialModel
{
    private $table = 'material';
    const TABLE = 'material';

    /**
     * @param array $data
     * @param $string
     * @return mixed
     */
    public static function getValue(array $data, $string)
    {
        return DB::table(self::TABLE)->where($data)->value($string);
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
            ->where(function ($query) use ($input) {
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where('name', 'like', '%' . $input['search'] . '%');
                }
            })
            ->offset($start)->limit($limit)->get()->toArray();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
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
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data)
    {
        return DB::table($this->table)->where('id',$id)->update($data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function search(array $data)
    {
        return DB::table($this->table)
            ->where(function ($query) use($data){
                $query->where('name','like','%'.$data['search'].'%')->where('status',1);
            })
            ->get()->toArray();
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function countLists(array $input)
    {
        return DB::table($this->table)
            ->where(function ($query) use ($input) {
                if (isset($input['search']) && !is_null($input['search'])) {
                    $query->where('name', 'like', '%' . $input['search'] . '%');
                }
            })
            ->count();
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

}