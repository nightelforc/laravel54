<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/1
 * Time: 12:03
 */

namespace App\Http\Model;

use Illuminate\Support\Facades\DB;

class WarehouseLogModel
{
    private $table = 'warehouse_log';

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        $data['createTime'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->insertGetId($data);
    }

    /**
     * @param array $data
     * @return string
     */
    public function addLog(array $data)
    {
        $logInfo = $data['data'];
        unset($data['data']);
        $insertId = '';
        try {
            DB::transaction(function () use ($data, $logInfo, $insertId) {
                $insertId = $this->insert($data);
                $warehouseLogInfoModel = new WarehouseLogInfoModel();
                foreach ($logInfo as $i) {
                    $i['logId'] = $insertId;
                    $warehouseLogInfoModel->insert($i);
                }
            });
            return $insertId;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $pk
     * @param $data
     * @param $approvalResult
     * @todo  审批回调
     */
    public function consume($pk,$data,$approvalResult){

    }
}