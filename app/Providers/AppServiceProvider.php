<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('layouts.apps', function ($view) {
            $user = auth()->user();
            $pegawaiNotifications = collect();
            $pegawaiNotificationUnreadCount = 0;

            if ($user?->role === 'pegawai') {
                $pegawaiNotifications = $user->pegawaiNotifications()
                    ->latest()
                    ->limit(20)
                    ->get();

                $pegawaiNotificationUnreadCount = $user->pegawaiNotifications()
                    ->whereNull('dibaca_pada')
                    ->count();
            }

            $view->with(compact(
                'pegawaiNotifications',
                'pegawaiNotificationUnreadCount'
            ));
        });
    }
}
