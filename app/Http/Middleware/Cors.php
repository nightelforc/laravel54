<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/8/3
 * Time: 11:09
 */

namespace App\Http\Middleware;

use Closure;
class Cors
{
    public function handle($request, Closure $next) {
        $response = $next($request);
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, Accept');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS');
        $response->header('Access-Control-Allow-Credentials', 'false');
        return $response;
    }
}