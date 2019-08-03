<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/24
 * Time: 16:51
 */

namespace App\Http\Middleware;
use App\Http\Controllers\Controller;
use App\Http\Model\YearModel;
use Closure;

class Yucheng
{
    /**
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $result = $this->currentYearIsExists();
        if (!$result) {
            return (new Controller())->ajaxResult(100003, '请先联系管理员设置工程年度', [],403);
        }
        return $next($request);
    }

    /**
     * 判断是否存在包含当前时间的工程年度
     *
     * @return bool
     */
    public function currentYearIsExists(){
        $currentDateTime = date('Y-m-d H:i:s');
        $yearModel = new YearModel();
        $result = $yearModel->findYear($currentDateTime);
        if (empty($result)){
            return false;
        }else{
            return true;
        }
    }
}