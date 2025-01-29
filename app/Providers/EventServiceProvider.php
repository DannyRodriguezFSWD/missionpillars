<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Observers\CustomTrackingObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        Event::listen('eloquent.created: *', function ($event, array $data) {
            $observer = new CustomTrackingObserver($event, $data);
            $observer->created();
        });
        
        Event::listen('eloquent.updated: *', function ($event, array $data) {
            $observer = new CustomTrackingObserver($event, $data);
            $observer->updated();
        });

        //
    }
}
