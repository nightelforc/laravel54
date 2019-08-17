<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/13
 * Time: 9:57
 */

namespace App\Http\Model;


use Illuminate\Support\Facades\DB;

class AdminSessionModel
{
    const TABLE = 'admin_session';

    /**
     * @param $adminId
     * @param int $projectId
     * @param $token
     */
    public static function put($token,$adminId,$projectId= 0){
        $session = self::info($token);
        if (empty($session)){
            DB::table(self::TABLE)->where('adminId',$adminId)->delete();
            $data = ['adminId'=>$adminId,'token'=>$token,'tokenTime'=>date('Y-m-d H:i:s')];
            if ($projectId != 0){
                $data['projectId'] = $projectId;
            }
            DB::table(self::TABLE)->insert($data);
        }else{
            $data = ['adminId'=>$adminId,'tokenTime'=>date('Y-m-d H:i:s')];
            if ($projectId != 0){
                $data['projectId'] = $projectId;
            }
            DB::table(self::TABLE)->where('token',$token)->update($data);
        }
    }

    /**
     * @param $token
     * @return array
     */
    public static function get($token){
        $session = self::info($token);
        return $session;
    }

    public static function delete($token){
        DB::table(self::TABLE)->where('token',$token)->delete();
    }

    /**
     * @param $token
     * @return array
     */
    private static function info($token)
    {
        $result = DB::table(self::TABLE)->where('token',$token)->first();
        return empty($result) ? [] : get_object_vars($result);
    }
}