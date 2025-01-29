<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;
use Jenssegers\Agent\Agent;

class PublicPagesRedirections
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
        $agent = new Agent();
        $redirect = session('redirect_url');
        
        //not logged user so its a public page
        //if is not mobile continue using desktop flow
        if(is_null($redirect) && !auth()->check() && $agent->isMobile()){
            //store the session
            session(['redirect' => $request->fullUrl()]);
        }
        
        //logged in owner users can override redirections, no matter device
        //right now only 2 roles exists: organization-owner (full backend admin) | organization-contact (profile access in backend) 03-04-2018
        if(auth()->check() && auth()->user()->hasRole('organization-owner')){
            //override session
            session(['redirect' => array_get($request, 'redirect')]);
        }
        
        return $next($request);
    }
}
