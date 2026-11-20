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
    public function home()
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->where('slug', '!=', 'free-trial')
            ->orderBy('sort_order')
            ->get();

        return view('welcome', compact('plans'));
    }

    /**
     * Register landlord and start the 14-day trial.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:30',
            'password' => 'required|string|min:8|confirmed',

            'selected_plan_id' => [
                'required',
                'integer',
                'exists:subscription_plans,id',
            ],
        ]);

        $user = DB::transaction(function () use ($validated) {

            // Find the landlord's selected paid package.
            $selectedPlan = SubscriptionPlan::where(
                'id',
                $validated['selected_plan_id']
            )
                ->where('is_active', true)
                ->where('slug', '!=', 'free-trial')
                ->first();

            if (!$selectedPlan) {
                throw ValidationException::withMessages([
                    'selected_plan_id' =>
                        'Please select a valid subscription package.',
                ]);
            }

            // Get the configured free trial package.
            $trialPlan = SubscriptionPlan::where(
                'slug',
                'free-trial'
            )
                ->where('is_active', true)
                ->first();

            if (!$trialPlan) {
                throw new \RuntimeException(
                    'Free trial package has not been configured.'
                );
            }

            // Create landlord login.
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Create landlord profile.
            $landlord = Landlord::create([
                'user_id' => $user->id,
                'business_name' => $validated['business_name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'country' => 'Kenya',
                'is_active' => true,
            ]);

            // Start the free trial.
            $trialStart = now();

            $trialEnd = $trialStart->copy()->addDays(14);

            Subscription::create([
                'landlord_id' => $landlord->id,

                // Current access package: Free Trial.
                'plan_id' => $trialPlan->id,

                // Package selected for future billing.
                'selected_plan_id' => $selectedPlan->id,

                'starts_at' => $trialStart,
                'ends_at' => $trialEnd,
                'trial_ends_at' => $trialEnd,

                'status' => 'trial',
                'amount_paid' => 0,
            ]);

            return $user;
        });

        // Authenticate newly registered landlord.
        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                'Welcome to Pango! Your 14-day free trial has started.'
            );
    }

    /**
     * Authenticate landlord.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt(
            $credentials,
            $request->boolean('remember')
        )) {
            throw ValidationException::withMessages([
                'email' => 'The email or password you entered is incorrect.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Logout landlord.
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
     * Display landlord dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user()->load(
            'landlord.activeSubscription.plan',
            'landlord.activeSubscription.selectedPlan'
        );

        $landlord = $user->landlord;

        $subscription = $landlord?->activeSubscription;

        return view('dashboard', compact(
            'user',
            'landlord',
            'subscription'
        ));
    }
}
