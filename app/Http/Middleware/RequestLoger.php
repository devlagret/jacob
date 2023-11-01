<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\RequestLog;
use Illuminate\Support\Facades\Auth;

class RequestLoger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        RequestLog::create(['method'=>$request->method(),'uri'=>$request->fullUrl(),'user_id'=>Auth::id(),'ip'=>$request->ip(),'heder'=>$request->header()]);
        return $next($request);
    }
}
