<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedLandlord
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $landlord = $user->landlord;
        if (!$landlord) {
            abort(403, 'Landlord account not found.');
        }

        if (!$landlord->is_active ||
            $landlord->approval_status !== 'approved') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'This landlord account is inactive or is not approved to access the workspace.',
                ]);
        }

        return $next($request);
    }
}
