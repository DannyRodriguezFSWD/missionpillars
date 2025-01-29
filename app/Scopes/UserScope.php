<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use App\Traits\Subdomains;
use App\Constants;

/**
 * Gets the tenant_id by subdomain name and
 * Constraints User model to get data only if the current user tenant_id matches
 * @author josemiguel
 */
class UserScope implements Scope{
    use Subdomains;

    public function apply(Builder $builder, Model $model){
        if (App::runningInConsole()) return $builder->where($model->getTable() . '.tenant_id', auth()->user()->tenant_id);
        $url = Request::getHost();
        $subdomain = $this->getSubdomain($url);
        $tenant = $this->getTenant($subdomain);
        $app = sprintf(env('APP_DOMAIN'), 'app');

        if(!$tenant && strpos($app, $url) === false){
            abort(404, "Subdomain ($subdomain) does not exists");
        }
        else if($tenant){
            $builder->where($model->getTable() . '.tenant_id', $tenant->id);
        }

        return $builder;
    }

}
