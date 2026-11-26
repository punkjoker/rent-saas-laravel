<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Models\DepositPayment;
use App\Models\Expense;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\RentCharge;
use App\Models\RentPayment;
use App\Models\Tenancy;
use App\Models\TenancyReconciliation;
use App\Services\RentPaymentLedger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    private function landlordId(): int
    {
        $landlordId = Landlord::where('user_id', Auth::id())->value('id');

        abort_unless($landlordId, 403, 'Landlord account not found.');

        return (int) $landlordId;
    }

    public function index(Request $request, RentPaymentLedger $ledger)
    {
        $validated = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            'property_id' => ['nullable', 'integer'],
        ]);
        $landlordId = $this->landlordId();
        $propertyId = isset($validated['property_id']) ? (int) $validated['property_id'] : null;
        $year = (int) ($validated['year'] ?? now()->year);
        $month = isset($validated['month']) ? (int) $validated['month'] : null;

        $periodStart = $month
            ? Carbon::create($year, $month, 1)->startOfMonth()
            : Carbon::create($year, 1, 1)->startOfYear();
        $periodEnd = $month
            ? $periodStart->copy()->endOfMonth()
            : $periodStart->copy()->endOfYear();
        $periodStartDate = $periodStart->toDateString();
        $periodEndDate = $periodEnd->toDateString();
        $periodStartMonth = $periodStart->format('Y-m');
        $periodEndMonth = $periodEnd->format('Y-m');
        $periodEndOfDay = $periodEnd->copy()->endOfDay();
        $periodLabel = $month
            ? $periodStart->format('F Y')
            : 'Year '.$year;

        $months = $month ? [$month] : range(1, 12);
        $monthlySeries = [];
        foreach ($months as $seriesMonth) {
            $monthKey = sprintf('%04d-%02d', $year, $seriesMonth);
            $monthlySeries[$monthKey] = [
                'label' => Carbon::createFromFormat('Y-m', $monthKey)->format('M'),
                'rent_billed' => 0.0,
                'rent_collected' => 0.0,
                'expenses' => 0.0,
                'deposits' => 0.0,
                'deposit_refunds' => 0.0,
            ];
        }

        $properties = Property::where('landlord_id', $landlordId)
            ->withCount('units')
            ->orderBy('property_name')
            ->get();
        abort_if(
            $propertyId && ! $properties->contains('id', $propertyId),
            404
        );
        $reportProperties = $propertyId
            ? $properties->where('id', $propertyId)->values()
            : $properties;
        $propertyMetrics = [];
        foreach ($reportProperties as $property) {
            $propertyMetrics[$property->id] = [
                'name' => $property->property_name,
                'units' => (int) $property->units_count,
                'occupied_units' => 0,
                'rent_billed' => 0.0,
                'rent_collected' => 0.0,
                'arrears' => 0.0,
                'deposit_agreed' => 0.0,
                'deposit_collected' => 0.0,
                'deposit_balance' => 0.0,
            ];
        }

        $charges = RentCharge::whereBetween('month_year', [
            $periodStartMonth,
            $periodEndMonth,
        ])
            ->whereHas('tenancy', fn ($query) => $query
                ->where('landlord_id', $landlordId)
                ->when($propertyId, fn ($tenancyQuery) => $tenancyQuery
                    ->where('property_id', $propertyId)))
            ->with('tenancy:id,property_id')
            ->get();

        foreach ($charges as $charge) {
            $monthKey = $charge->month_year;
            $amount = (float) $charge->total_due;
            if (isset($monthlySeries[$monthKey])) {
                $monthlySeries[$monthKey]['rent_billed'] += $amount;
            }

            $propertyId = $charge->tenancy->property_id;
            if (isset($propertyMetrics[$propertyId])) {
                $propertyMetrics[$propertyId]['rent_billed'] += $amount;
            }
        }

        $payments = RentPayment::whereBetween('paid_at', [
            $periodStart->copy()->startOfDay(),
            $periodEndOfDay,
        ])
            ->whereHas('tenancy', fn ($query) => $query
                ->where('landlord_id', $landlordId)
                ->when($propertyId, fn ($tenancyQuery) => $tenancyQuery
                    ->where('property_id', $propertyId)))
            ->with('tenancy:id,property_id')
            ->get();

        foreach ($payments as $payment) {
            $monthKey = $payment->paid_at->format('Y-m');
            $amount = (float) $payment->amount;
            if (isset($monthlySeries[$monthKey])) {
                $monthlySeries[$monthKey]['rent_collected'] += $amount;
            }

            $propertyId = $payment->tenancy->property_id;
            if (isset($propertyMetrics[$propertyId])) {
                $propertyMetrics[$propertyId]['rent_collected'] += $amount;
            }
        }

        $periodDeposits = DepositPayment::whereBetween('paid_at', [
            $periodStart->copy()->startOfDay(),
            $periodEndOfDay,
        ])
            ->whereHas('tenancy', fn ($query) => $query
                ->where('landlord_id', $landlordId)
                ->when($propertyId, fn ($tenancyQuery) => $tenancyQuery
                    ->where('property_id', $propertyId)))
            ->get();

        foreach ($periodDeposits as $deposit) {
            $monthKey = $deposit->paid_at->format('Y-m');
            if (isset($monthlySeries[$monthKey])) {
                $monthlySeries[$monthKey]['deposits'] += (float) $deposit->amount;
            }
        }

        $periodRefunds = TenancyReconciliation::whereNotNull('refunded_at')
            ->whereBetween('refunded_at', [
                $periodStart->copy()->startOfDay(),
                $periodEndOfDay,
            ])
            ->whereHas('tenancy', fn ($query) => $query
                ->where('landlord_id', $landlordId)
                ->when($propertyId, fn ($tenancyQuery) => $tenancyQuery
                    ->where('property_id', $propertyId)))
            ->with('tenancy:id,property_id')
            ->get();

        foreach ($periodRefunds as $refund) {
            $monthKey = $refund->refunded_at->format('Y-m');
            if (isset($monthlySeries[$monthKey])) {
                $monthlySeries[$monthKey]['deposit_refunds'] +=
                    (float) $refund->refund_amount;
            }
        }

        $expenses = Expense::where('landlord_id', $landlordId)
            ->when($propertyId, fn ($query) => $query->where('property_id', $propertyId))
            ->whereBetween('expense_date', [$periodStartDate, $periodEndDate])
            ->orderBy('expense_date')
            ->get();

        foreach ($expenses as $expense) {
            $monthKey = $expense->expense_date->format('Y-m');
            if (isset($monthlySeries[$monthKey])) {
                $monthlySeries[$monthKey]['expenses'] += (float) $expense->amount;
            }
        }

        $balanceTenancies = Tenancy::where('landlord_id', $landlordId)
            ->when($propertyId, fn ($query) => $query->where('property_id', $propertyId))
            ->whereDate('start_date', '<=', $periodEndDate)
            ->with([
                'rentCharges' => fn ($query) => $query
                    ->where('month_year', '<=', $periodEndMonth)
                    ->orderBy('month_year'),
                'rentPayments' => fn ($query) => $query
                    ->where('paid_at', '<=', $periodEndOfDay)
                    ->orderBy('paid_at')
                    ->orderBy('id'),
                'depositPayments' => fn ($query) => $query
                    ->where('paid_at', '<=', $periodEndOfDay)
                    ->orderBy('paid_at'),
            ])
            ->get();

        $rentArrears = 0.0;
        $depositAgreed = 0.0;
        $depositCollectedToDate = 0.0;
        $depositBalance = 0.0;

        foreach ($balanceTenancies as $tenancy) {
            $tenancyEnd = $tenancy->ended_at ?? $tenancy->lease_end_date;
            $chargeThroughMonth = $tenancyEnd
                ? min($periodEndMonth, $tenancyEnd->format('Y-m'))
                : $periodEndMonth;
            $tenancyCharges = $tenancy->rentCharges
                ->filter(fn ($charge) => $charge->month_year <= $chargeThroughMonth);
            $summary = $ledger->summarize($tenancyCharges, $tenancy->rentPayments);
            $rentArrears += $summary['arrears'];

            if (isset($propertyMetrics[$tenancy->property_id])) {
                $propertyMetrics[$tenancy->property_id]['arrears'] += $summary['arrears'];
            }

            $agreed = (float) $tenancy->deposit_amount;
            $collected = (float) $tenancy->depositPayments->sum(
                fn ($payment) => (float) $payment->amount
            );
            $depositAgreed += $agreed;
            $depositCollectedToDate += $collected;
            $depositBalance += max($agreed - $collected, 0);

            if (isset($propertyMetrics[$tenancy->property_id])) {
                $propertyMetrics[$tenancy->property_id]['deposit_agreed'] += $agreed;
                $propertyMetrics[$tenancy->property_id]['deposit_collected'] += $collected;
                $propertyMetrics[$tenancy->property_id]['deposit_balance'] += max(
                    $agreed - $collected,
                    0
                );
            }
        }

        $activeAtPeriodEnd = $balanceTenancies->filter(function ($tenancy) use ($periodEndDate) {
            return (! $tenancy->ended_at ||
                    $tenancy->ended_at->toDateString() >= $periodEndDate) &&
                (! $tenancy->lease_end_date ||
                    $tenancy->lease_end_date->toDateString() >= $periodEndDate);
        });
        foreach ($activeAtPeriodEnd->groupBy('property_id') as $propertyId => $tenancies) {
            if (isset($propertyMetrics[$propertyId])) {
                $propertyMetrics[$propertyId]['occupied_units'] = $tenancies
                    ->pluck('unit_id')
                    ->unique()
                    ->count();
            }
        }

        $upcomingTenancies = Tenancy::where('landlord_id', $landlordId)
            ->when($propertyId, fn ($query) => $query->where('property_id', $propertyId))
            ->where('status', 'upcoming')
            ->whereDate('start_date', '>', $periodEndDate)
            ->count();
        $vacatedDuringPeriod = Tenancy::where('landlord_id', $landlordId)
            ->when($propertyId, fn ($query) => $query->where('property_id', $propertyId))
            ->where('status', 'ended')
            ->whereBetween('ended_at', [$periodStartDate, $periodEndDate])
            ->count();
        $totalUnits = (int) $reportProperties->sum('units_count');
        $occupiedUnits = $activeAtPeriodEnd->pluck('unit_id')->unique()->count();
        $availableUnits = max($totalUnits - $occupiedUnits, 0);

        $monthlySeries = array_values($monthlySeries);
        $totals = [
            'rent_billed' => collect($monthlySeries)->sum('rent_billed'),
            'rent_collected' => collect($monthlySeries)->sum('rent_collected'),
            'expenses' => collect($monthlySeries)->sum('expenses'),
            'deposits' => collect($monthlySeries)->sum('deposits'),
            'deposit_refunds' => collect($monthlySeries)->sum('deposit_refunds'),
        ];

        $categoryTotals = $expenses->groupBy('category')
            ->map(fn ($items) => round(
                $items->sum(fn ($expense) => (float) $expense->amount),
                2
            ))
            ->sortDesc();
        $expenseBreakdown = $categoryTotals->take(6);
        if ($categoryTotals->count() > 6) {
            $expenseBreakdown->put('Other', round($categoryTotals->slice(6)->sum(), 2));
        }

        $propertyMetrics = collect($propertyMetrics)
            ->sortByDesc('rent_billed')
            ->values();
        $monthsForFilter = collect(range(1, 12))->mapWithKeys(fn ($number) => [
            $number => Carbon::create($year, $number, 1)->format('F'),
        ]);
        $years = range(2000, now()->year + 1);
        $activeTenantCount = $activeAtPeriodEnd->pluck('tenant_id')->unique()->count();
        $accommodations = Accommodation::where('landlord_id', $landlordId)
            ->when($propertyId, fn ($query) => $query->where('property_id', $propertyId))
            ->get(['id', 'price_per_night', 'clicks_count', 'status']);
        $bookingQuery = AccommodationBooking::whereIn(
            'accommodation_id',
            $accommodations->pluck('id')
        )
            ->where('status', 'confirmed')
            ->where('check_in', '<', $periodEnd->copy()->addDay()->toDateString())
            ->where('check_out', '>', $periodStartDate)
            ->with('accommodation:id,price_per_night');
        $confirmedBookings = $bookingQuery->get();
        $confirmedStayRevenue = 0.0;
        $projectionBasisRevenue = 0.0;
        $confirmedStayNights = 0;
        $elapsedEnd = $periodEnd->lessThan(now()) ? $periodEnd->copy() : now()->copy();
        $elapsedDays = $periodStart->greaterThan(now())
            ? 0
            : $periodStart->copy()->startOfDay()->diffInDays($elapsedEnd->startOfDay()) + 1;
        $projectionEnd = $elapsedDays > 0
            ? $elapsedEnd->copy()->addDay()->startOfDay()
            : $periodStart->copy()->startOfDay();
        foreach ($confirmedBookings as $booking) {
            $bookingStart = $booking->check_in->copy()->startOfDay();
            $stayStart = $bookingStart->greaterThan($periodStart)
                ? $bookingStart
                : $periodStart->copy()->startOfDay();
            $bookingEnd = $booking->check_out->copy()->startOfDay();
            $stayEnd = $bookingEnd->lessThan($periodEnd->copy()->addDay())
                ? $bookingEnd
                : $periodEnd->copy()->addDay()->startOfDay();
            $nights = max($stayStart->diffInDays($stayEnd), 0);
            $confirmedStayNights += $nights;
            $nightlyRate = (float) $booking->accommodation->price_per_night;
            $confirmedStayRevenue += $nights * $nightlyRate;

            $projectionStayEnd = $bookingEnd->lessThan($projectionEnd)
                ? $bookingEnd
                : $projectionEnd;
            if ($projectionStayEnd->greaterThan($stayStart)) {
                $projectionBasisRevenue += $stayStart->diffInDays($projectionStayEnd) * $nightlyRate;
            }
        }
        $pendingStayBookings = AccommodationBooking::whereIn(
            'accommodation_id',
            $accommodations->pluck('id')
        )
            ->where('status', 'pending')
            ->whereBetween('created_at', [$periodStart, $periodEndOfDay])
            ->count();
        $periodDays = $periodStart->diffInDays($periodEnd->copy()->addDay());
        $accommodationProjection = $elapsedDays > 0
            ? $projectionBasisRevenue / $elapsedDays * $periodDays
            : 0;
        $accommodationReport = [
            'listings' => $accommodations->count(),
            'published' => $accommodations->where('status', 'published')->count(),
            'clicks' => (int) $accommodations->sum('clicks_count'),
            'confirmed_bookings' => $confirmedBookings->count(),
            'pending_bookings' => $pendingStayBookings,
            'confirmed_nights' => $confirmedStayNights,
            'confirmed_revenue' => round($confirmedStayRevenue, 2),
            'projected_revenue' => round($accommodationProjection, 2),
            'projection_days' => $elapsedDays,
            'period_days' => $periodDays,
        ];

        return view('reports.index', compact(
            'year',
            'month',
            'years',
            'monthsForFilter',
            'periodLabel',
            'monthlySeries',
            'totals',
            'properties',
            'propertyMetrics',
            'expenseBreakdown',
            'rentArrears',
            'depositAgreed',
            'depositCollectedToDate',
            'depositBalance',
            'activeAtPeriodEnd',
            'activeTenantCount',
            'upcomingTenancies',
            'vacatedDuringPeriod',
            'totalUnits',
            'occupiedUnits',
            'availableUnits', 'propertyId', 'accommodationReport'
        ));
    }
}
