<?php

namespace App\Providers;

use App\Models\ContactMessage;
use App\Models\SupportMessage;
use App\Services\AccommodationAccess;
use Illuminate\Support\Facades\Auth;
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
        View::composer(['layouts.admin', 'layouts.landlord'], function ($view) {
            $user = Auth::user();
            $unreadContacts = 0;
            $unreadSupport = 0;
            $accommodationEnabled = false;

            if ($user?->isAdmin()) {
                $unreadContacts = ContactMessage::where('status', 'new')->count();
                $unreadSupport = SupportMessage::where('sender_type', 'landlord')
                    ->whereNull('read_at')
                    ->count();
            } elseif ($user?->landlord) {
                $unreadSupport = SupportMessage::where('sender_type', 'admin')
                    ->whereNull('read_at')
                    ->whereHas('conversation', fn ($query) => $query
                        ->where('landlord_id', $user->landlord->id))
                    ->count();
                $accommodationEnabled = app(AccommodationAccess::class)
                    ->enabled($user->landlord);
            }

            $view->with(compact(
                'unreadContacts',
                'unreadSupport',
                'accommodationEnabled'
            ));
        });
    }
}
