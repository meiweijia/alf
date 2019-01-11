<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class Log
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        DB::enableQueryLog();
        $response = $next($request);
        $query = DB::getQueryLog();
        if (strpos($request->path(), 'admin/logs') !== false) {
            return $response;
        }
        $log = array('url' => $request->path(), 'data' => $request->all(), 'sql' => $query, 'agent' => $request->userAgent(), 'ip' => $request->header('X-Real-IP'));
        \Illuminate\Support\Facades\Log::info('OperationLog', $log);
        return $response;
    }
}
