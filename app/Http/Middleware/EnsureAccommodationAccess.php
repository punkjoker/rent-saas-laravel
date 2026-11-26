<?php

namespace App\Http\Middleware;

use App\Services\AccommodationAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccommodationAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $landlord = $request->user()?->landlord;
        abort_unless($landlord, 403, 'Landlord account not found.');

        if (!app(AccommodationAccess::class)->enabled($landlord)) {
            return redirect()
                ->route('billing.plans')
                ->with('error', 'Activate accommodations access for KSh 3,500 monthly or KSh 33,600 yearly to publish and manage accommodation listings.');
        }

        return $next($request);
    }
}
