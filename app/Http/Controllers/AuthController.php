<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Landlord;
use App\Models\Expense;
use App\Models\RentCharge;
use App\Models\RentPayment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Unit;
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
        $dashboardCharts = [
            'monthly' => [],
            'occupancy' => [
                'occupied' => 0,
                'vacant' => 0,
                'maintenance' => 0,
                'other' => 0,
                'total' => 0,
            ],
        ];

        if ($landlord) {
            $monthStart = now()->startOfMonth()->subMonths(5);
            $months = collect(range(0, 5))->map(function ($offset) use ($monthStart) {
                $date = $monthStart->copy()->addMonths($offset);

                return [
                    'key' => $date->format('Y-m'),
                    'label' => $date->format('M'),
                ];
            });

            $chargesByMonth = RentCharge::whereIn('month_year', $months->pluck('key'))
                ->whereHas('tenancy', fn ($query) => $query->where('landlord_id', $landlord->id))
                ->get(['month_year', 'total_due'])
                ->groupBy('month_year')
                ->map(fn ($charges) => $charges->sum(
                    fn ($charge) => (float) $charge->total_due
                ));

            $paymentsByMonth = RentPayment::whereHas(
                'tenancy',
                fn ($query) => $query->where('landlord_id', $landlord->id)
            )
                ->whereBetween('paid_at', [$monthStart, now()])
                ->get(['amount', 'paid_at'])
                ->groupBy(fn ($payment) => $payment->paid_at->format('Y-m'))
                ->map(fn ($payments) => $payments->sum(
                    fn ($payment) => (float) $payment->amount
                ));

            $expensesByMonth = Expense::where('landlord_id', $landlord->id)
                ->whereBetween('expense_date', [
                    $monthStart->toDateString(),
                    now()->toDateString(),
                ])
                ->get(['amount', 'expense_date'])
                ->groupBy(fn ($expense) => $expense->expense_date->format('Y-m'))
                ->map(fn ($expenses) => $expenses->sum(
                    fn ($expense) => (float) $expense->amount
                ));

            $dashboardCharts['monthly'] = $months->map(fn ($month) => [
                'label' => $month['label'],
                'billed' => round((float) $chargesByMonth->get($month['key'], 0), 2),
                'collected' => round((float) $paymentsByMonth->get($month['key'], 0), 2),
                'expenses' => round((float) $expensesByMonth->get($month['key'], 0), 2),
            ])->all();

            $unitStatusCounts = Unit::whereHas(
                'property',
                fn ($query) => $query->where('landlord_id', $landlord->id)
            )->get(['status'])->countBy('status');

            $dashboardCharts['occupancy'] = [
                'occupied' => (int) $unitStatusCounts->get('occupied', 0),
                'vacant' => (int) $unitStatusCounts->get('vacant', 0),
                'maintenance' => (int) $unitStatusCounts->get('maintenance', 0),
                'other' => (int) $unitStatusCounts
                    ->except(['occupied', 'vacant', 'maintenance'])
                    ->sum(),
                'total' => (int) $unitStatusCounts->sum(),
            ];
        }

        return view('dashboard', compact(
            'user',
            'landlord',
            'subscription',
            'dashboardCharts'
        ));
    }
}
