<?php

namespace App\Providers;

use App\Events\NotificationTriggered;
use App\Listeners\NotificationListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NotificationTriggered::class => [
            NotificationListener::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
