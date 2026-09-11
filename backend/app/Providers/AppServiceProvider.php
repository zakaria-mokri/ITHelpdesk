<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Listeners\ModelAuditSubscriber;
use App\Models\Ticket;
use App\Models\Asset;
use App\Observers\AssetAssignmentObserver;
use App\Observers\NotificationObserver;
use App\Observers\TicketObserver;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       RateLimiter::for('login', function (Request $request) {
           $username = mb_strtolower(trim((string) $request->input('username', '')));
           $key = hash('sha256', $username.'|'.$request->ip());

           return Limit::perMinute(8)->by($key);
       });

        //
        Ticket::observe(TicketObserver::class);
        Asset::observe(AssetAssignmentObserver::class);
        DatabaseNotification::observe(NotificationObserver::class);
        Event::subscribe(ModelAuditSubscriber::class);

        Event::listen(function (
            \SocialiteProviders\Manager\SocialiteWasCalled $event
        ) {
            $event->extendSocialite(
                'microsoft',
                \SocialiteProviders\Microsoft\Provider::class
            );
        });
    }
}
