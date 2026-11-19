<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Landlord;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Display the public homepage.
     */
    public function home()
    {
        return view('welcome');
    }

    /**
     * Register a new landlord and automatically activate
     * their 14-day free trial.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:30',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = DB::transaction(function () use ($validated) {

            // 1. Create landlord login account.
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // 2. Create landlord business profile.
            $landlord = Landlord::create([
                'user_id' => $user->id,
                'business_name' => $validated['business_name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'country' => 'Kenya',
                'is_active' => true,
            ]);

            // 3. Create a subscription using the configured free-trial plan.
            $trialPlan = SubscriptionPlan::where(
                'slug',
                'free-trial'
            )->firstOrFail();

            $trialStart = now();
            $trialEnd = $trialStart->copy()->addDays(14);

            Subscription::create([
                'landlord_id' => $landlord->id,
                'plan_id' => $trialPlan->id,
                'starts_at' => $trialStart,
                'ends_at' => $trialEnd,
                'trial_ends_at' => $trialEnd,
                'status' => 'trial',
                'amount_paid' => 0,
            ]);

            return $user;
        });

        // 4. Log in the newly registered landlord.
        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Welcome to Pango! Your 14-day free trial has started.');
    }

    /**
     * Authenticate an existing landlord.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'The email or password you entered is incorrect.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Log out the current user.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('success', 'You have successfully signed out.');
    }

    /**
     * Display the protected landlord dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user()->load('landlord.activeSubscription');

        $landlord = $user->landlord;
        $subscription = $landlord?->activeSubscription;

        return view('dashboard', compact(
            'user',
            'landlord',
            'subscription'
        ));
    }
}
