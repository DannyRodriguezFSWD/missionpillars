<?php 
namespace App\Extension;

use Illuminate\Support\ServiceProvider;
use Session;

// thanks https://stackoverflow.com/a/24282759/2884623
class MPSessionServiceProvider extends ServiceProvider {

    public function register()
    {
        $connection = $this->app['config']['session.connection'];
        $table = $this->app['config']['session.table'];
        $lifetime = $this->app['config']['session.lifetime'];

        $this->app['session']->extend('database', function($app) use ($connection, $table, $lifetime){
            return new \App\Extension\MPDatabaseSessionHandler(
                $this->app['db']->connection($connection),
                $table,
                $lifetime,
                $this->app
            );
        });
    }

}
