<?php

namespace App\Http\Middleware;

use Closure;

class FrameHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('Content-Security-Policy', "frame-ancestors *");
        // for IE8 and other older browsers
        $response->header('X-Frame-Options', 'ALLOW FROM *');
        return $response;
    }
}
