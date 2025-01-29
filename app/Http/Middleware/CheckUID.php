<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class CheckUID {

    private $verbs = ['PUT', 'PATCH', 'DELETE', 'GET'];

    /**
     * Handle an incoming request.
     * Avoid update/delete actions if the current id doesn't match with encrypted unique id (uid)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $params = array_values($request->route()->parameters());
        $verb = $request->input('_method');
        //dd($params, $verb);
        if ($verb && in_array($verb, $this->verbs)) {
            if (!$this->checkUID($params, $request)) {
                return redirect(route('cheating'));
            }
        }

        return $next($request);
    }

    private function checkUID($params, $request) {
        if ($params && $request->has('uid')) {
            try {
                $uid = (string) Crypt::decrypt($request->uid);
                $id = $params[0];
            } catch (DecryptException $e) {
                return false;
            } catch (Exception $e) {
                return false;
            }
            return $id === $uid;
        }
        return false;
    }

}
