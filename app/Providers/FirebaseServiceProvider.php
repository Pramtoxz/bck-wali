<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('firebase.messaging', function ($app) {
            $factory = (new Factory)
                ->withServiceAccount(storage_path('app/private/absen-wali-firebase.json'));

            return $factory->createMessaging();
        });

        $this->app->alias('firebase.messaging', Messaging::class);
    }

    public function boot(): void
    {
        //
    }
}
