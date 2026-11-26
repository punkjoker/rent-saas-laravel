<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Models\ContactMessage;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\PropertyListing;
use App\Models\Subscription;
use App\Models\SubscriptionExtension;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\SupportMessage;
use App\Models\VacancyListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    private const SUCCESSFUL_PAYMENT_STATUSES = ['paid', 'success', 'completed'];

    public function index()
    {
        $monthStart = now()->startOfMonth()->subMonths(11);
        $incomePayments = SubscriptionPayment::whereIn(
            'status',
            self::SUCCESSFUL_PAYMENT_STATUSES
        )
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$monthStart, now()])
            ->get(['amount', 'paid_at'])
            ->groupBy(fn ($payment) => $payment->paid_at->format('Y-m'));

        $incomeSeries = collect(range(0, 11))->map(function ($offset) use (
            $monthStart,
            $incomePayments
        ) {
            $date = $monthStart->copy()->addMonths($offset);
            $payments = $incomePayments->get($date->format('Y-m'), collect());

            return [
                'label' => $date->format('M y'),
                'amount' => round($payments->sum(
                    fn ($payment) => (float) $payment->amount
                ), 2),
            ];
        });

        $popularPlans = SubscriptionPlan::withCount([
            'subscriptions as active_subscriptions_count' => fn ($query) => $query
                ->whereIn('status', ['trial', 'active']),
        ])
            ->orderByDesc('active_subscriptions_count')
            ->orderBy('sort_order')
            ->take(5)
            ->get();

        $stats = [
            'landlords' => Landlord::count(),
            'approved_landlords' => Landlord::where('approval_status', 'approved')->count(),
            'pending_landlords' => Landlord::where('approval_status', 'pending')->count(),
            'properties' => Property::count(),
            'active_subscriptions' => Subscription::whereIn('status', ['trial', 'active'])->count(),
            'pending_listings' => PropertyListing::where('status', 'pending')->count(),
            'published_vacancies' => VacancyListing::where('source', 'admin')
                ->where('status', 'published')
                ->count(),
            'published_accommodations' => Accommodation::where('status', 'published')->count(),
            'accommodation_clicks' => (int) Accommodation::sum('clicks_count'),
            'pending_accommodation_bookings' => AccommodationBooking::where('status', 'pending')->count(),
            'new_contact_messages' => ContactMessage::where('status', 'new')->count(),
            'unread_support_messages' => SupportMessage::where('sender_type', 'landlord')
                ->whereNull('read_at')
                ->count(),
            'income' => (float) SubscriptionPayment::whereIn(
                'status',
                self::SUCCESSFUL_PAYMENT_STATUSES
            )->sum('amount'),
        ];
        $topAccommodations = Accommodation::with('landlord.user')
            ->withCount([
                'bookings',
                'bookings as pending_bookings_count' => fn ($query) => $query
                    ->where('status', 'pending'),
                'bookings as confirmed_bookings_count' => fn ($query) => $query
                    ->where('status', 'confirmed'),
            ])
            ->orderByDesc('clicks_count')
            ->take(10)
            ->get();
        $mostSubscribedPlan = $popularPlans->first();

        return view('admin.dashboard', compact(
            'stats',
            'incomeSeries',
            'popularPlans',
            'mostSubscribedPlan', 'topAccommodations'
        ));
    }

    public function subscriptions()
    {
        $landlords = Landlord::with([
            'user',
            'subscriptions' => fn ($query) => $query->with(['plan', 'selectedPlan'])->latest('id'),
        ])
            ->withCount('properties')
            ->latest()
            ->paginate(20);

        return view('admin.subscriptions', compact('landlords'));
    }

    public function extendSubscription(Request $request, Landlord $landlord)
    {
        $validated = $request->validate([
            'days' => ['required', 'integer', 'between:1,3650'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($landlord, $validated) {
            $subscription = Subscription::where('landlord_id', $landlord->id)
                ->latest('id')
                ->lockForUpdate()
                ->firstOrFail();
            $subscription->load('plan');
            $isTrial = $subscription->status === 'trial' ||
                $subscription->plan?->slug === 'free-trial';
            $currentEnd = $isTrial
                ? $subscription->trial_ends_at
                : $subscription->ends_at;
            $base = $currentEnd && $currentEnd->isFuture()
                ? $currentEnd->copy()
                : now();
            $newEnd = $base->addDays((int) $validated['days']);

            $subscription->update($isTrial
                ? [
                    'status' => 'trial',
                    'trial_ends_at' => $newEnd,
                    'ends_at' => $newEnd,
                ]
                : [
                    'status' => 'active',
                    'ends_at' => $newEnd,
                ]);

            SubscriptionExtension::create([
                'landlord_id' => $landlord->id,
                'subscription_id' => $subscription->id,
                'admin_id' => Auth::id(),
                'days' => $validated['days'],
                'previous_ends_at' => $currentEnd,
                'new_ends_at' => $newEnd,
                'reason' => trim($validated['reason']),
            ]);
        });

        return back()->with('success', 'Subscription extended and audit record saved.');
    }

    public function accounts()
    {
        $landlords = Landlord::with(['user', 'subscriptions.plan'])
            ->withCount(['properties', 'tenants'])
            ->latest()
            ->paginate(20);

        return view('admin.accounts', compact('landlords'));
    }

    public function updateAccount(Request $request, Landlord $landlord)
    {
        $validated = $request->validate([
            'approval_status' => ['required', 'in:approved,pending,rejected'],
            'rejection_reason' => [
                'nullable',
                'required_if:approval_status,rejected',
                'string',
                'max:2000',
            ],
        ]);

        $landlord->update([
            'approval_status' => $validated['approval_status'],
            'rejection_reason' => $validated['approval_status'] === 'rejected'
                ? trim($validated['rejection_reason'])
                : null,
            'is_active' => $validated['approval_status'] === 'approved',
        ]);

        return back()->with('success', 'Landlord account status updated.');
    }

    public function listings()
    {
        $listings = PropertyListing::with([
            'property.landlord.user',
            'images',
        ])
            ->latest()
            ->paginate(15);

        return view('admin.listings', compact('listings'));
    }

    public function properties()
    {
        $properties = Property::with('landlord.user')
            ->withCount('units')
            ->latest()
            ->paginate(20);

        return view('admin.properties', compact('properties'));
    }

    public function updateProperty(Request $request, Property $property)
    {
        $validated = $request->validate([
            'revoked' => ['required', 'boolean'],
        ]);

        $property->update([
            'admin_revoked_at' => $validated['revoked'] ? now() : null,
        ]);

        return back()->with('success', 'Property moderation status updated.');
    }

    public function reviewListing(Request $request, PropertyListing $listing)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,revoked'],
            'rejection_reason' => [
                'nullable',
                'required_if:status,rejected',
                'string',
                'max:2000',
            ],
        ]);

        $listing->update([
            'status' => $validated['status'],
            'rejection_reason' => $validated['status'] === 'rejected'
                ? trim($validated['rejection_reason'])
                : null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Property advertisement status updated.');
    }
}
