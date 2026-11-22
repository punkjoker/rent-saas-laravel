<?php

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\Property;
use App\Models\RentPayment;
use App\Models\RentPaymentSubmission;
use App\Models\Tenant;
use App\Models\Tenancy;
use App\Services\RentPaymentLedger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RentPaymentController extends Controller
{
    private function landlordId(): int
    {
        $landlordId = Landlord::where('user_id', Auth::id())->value('id');

        abort_unless($landlordId, 403, 'Landlord account not found.');

        return (int) $landlordId;
    }

    private function ownedTenancy(int $tenancyId): Tenancy
    {
        return Tenancy::with(['tenant', 'property', 'unit'])
            ->where('landlord_id', $this->landlordId())
            ->findOrFail($tenancyId);
    }

    private function sharedTenancy(string $token): Tenancy
    {
        return Tenancy::with([
            'tenant',
            'property',
            'unit',
            'rentCharges' => fn ($query) => $query
                ->where('month_year', '<=', now()->format('Y-m'))
                ->orderBy('month_year'),
            'rentPayments' => fn ($query) => $query
                ->where('paid_at', '<=', now())
                ->orderBy('paid_at'),
            'rentPaymentSubmissions' => fn ($query) => $query->latest(),
        ])->where('payment_share_token_hash', hash('sha256', $token))
            ->firstOrFail();
    }

    public function index(Request $request, RentPaymentLedger $ledger)
    {
        $landlordId = $this->landlordId();
        $filters = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'property_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $month = $filters['month'] ?? now()->format('Y-m');
        $propertyId = isset($filters['property_id'])
            ? (int) $filters['property_id']
            : null;
        $search = trim($filters['search'] ?? '');
        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        if ($propertyId !== null) {
            Property::where('landlord_id', $landlordId)
                ->findOrFail($propertyId);
        }

        $tenanciesQuery = Tenancy::where('landlord_id', $landlordId)
            ->whereDate('start_date', '<=', $monthEnd)
            ->with([
                'tenant',
                'property',
                'unit',
                'rentCharges' => fn ($query) => $query
                    ->where('month_year', '<=', $month)
                    ->orderBy('month_year'),
                'rentPayments' => fn ($query) => $query
                    ->where('paid_at', '<=', $monthEnd)
                    ->orderBy('paid_at'),
            ]);

        if ($propertyId !== null) {
            $tenanciesQuery->where('property_id', $propertyId);
        }

        if ($search !== '') {
            $tenanciesQuery->where(function ($query) use ($search) {
                $query->whereHas('tenant', function ($tenantQuery) use ($search) {
                    $tenantQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('unit', function ($unitQuery) use ($search) {
                    $unitQuery->where('unit_number', 'like', "%{$search}%");
                })->orWhereHas('property', function ($propertyQuery) use ($search) {
                    $propertyQuery->where('property_name', 'like', "%{$search}%");
                });
            });
        }

        $reports = $tenanciesQuery->orderBy('id')->get()->map(function ($tenancy) use (
            $ledger,
            $month
        ) {
            $summary = $ledger->summarize(
                $tenancy->rentCharges,
                $tenancy->rentPayments
            );
            $selectedMonth = collect($summary['months'])->first(
                fn ($row) => $row['charge']->month_year === $month
            );

            return [
                'tenancy' => $tenancy,
                'summary' => $summary,
                'selected_month_due' => $selectedMonth['due'] ?? 0,
            ];
        });

        $pendingSubmissions = RentPaymentSubmission::where('status', 'pending')
            ->whereHas('tenancy', fn ($query) => $query->where('landlord_id', $landlordId))
            ->with(['tenancy.tenant', 'tenancy.property', 'tenancy.unit'])
            ->latest('transaction_date')
            ->get();

        $properties = Property::where('landlord_id', $landlordId)
            ->orderBy('property_name')
            ->get();

        return view('rent-payments.index', compact(
            'reports',
            'pendingSubmissions',
            'properties',
            'month',
            'propertyId',
            'search'
        ));
    }

    public function create(int $tenancy)
    {
        $tenancy = $this->ownedTenancy($tenancy);

        return view('rent-payments.create', compact('tenancy'));
    }

    public function history(Request $request, RentPaymentLedger $ledger, int $tenancy)
    {
        $month = $this->historyMonth($request);
        $tenancy = $this->ownedTenancy($tenancy);
        $histories = collect([
            $this->historyReport($tenancy, $month, $ledger),
        ]);

        return view('rent-payments.history', [
            'tenant' => $tenancy->tenant,
            'histories' => $histories,
            'month' => $month,
            'filterAction' => route('payments.history', $tenancy->id),
            'backUrl' => route('payments.index', ['month' => $month]),
            'backLabel' => 'Back to rent payments',
        ]);
    }

    public function tenantHistory(Request $request, RentPaymentLedger $ledger, int $tenant)
    {
        $landlordId = $this->landlordId();
        $month = $this->historyMonth($request);
        $tenantRecord = Tenant::where('landlord_id', $landlordId)
            ->findOrFail($tenant);

        $tenancies = Tenancy::with(['tenant', 'property', 'unit'])
            ->where('landlord_id', $landlordId)
            ->where('tenant_id', $tenantRecord->id)
            ->orderByDesc('start_date')
            ->get();

        $histories = $tenancies->map(
            fn ($tenancy) => $this->historyReport($tenancy, $month, $ledger)
        );

        return view('rent-payments.history', [
            'tenant' => $tenantRecord,
            'histories' => $histories,
            'month' => $month,
            'filterAction' => route('payments.tenant-history', $tenantRecord->id),
            'backUrl' => route('tenants.index'),
            'backLabel' => 'Back to tenants',
        ]);
    }

    private function historyMonth(Request $request): string
    {
        $filters = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        return $filters['month'] ?? now()->format('Y-m');
    }

    private function historyReport(
        Tenancy $tenancy,
        string $month,
        RentPaymentLedger $ledger
    ): array {
        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
        $lastMonth = Carbon::createFromFormat('Y-m', $month)
            ->subMonthNoOverflow()
            ->format('Y-m');
        $lastMonthEnd = Carbon::createFromFormat('Y-m', $lastMonth)->endOfMonth();

        $tenancy->load([
            'tenant',
            'property',
            'unit',
            'rentCharges' => fn ($query) => $query
                ->where('month_year', '<=', $month)
                ->orderBy('month_year'),
            'rentPayments' => fn ($query) => $query
                ->where('paid_at', '<=', $monthEnd)
                ->orderBy('paid_at'),
        ]);

        $lastMonthCharges = $tenancy->rentCharges
            ->where('month_year', '<=', $lastMonth);
        $lastMonthPayments = $tenancy->rentPayments
            ->filter(fn ($payment) => $payment->paid_at <= $lastMonthEnd);
        $lastMonthSummary = $ledger->summarize(
            $lastMonthCharges,
            $lastMonthPayments
        );
        $summary = $ledger->summarize(
            $tenancy->rentCharges,
            $tenancy->rentPayments
        );

        return [
            'tenancy' => $tenancy,
            'summary' => $summary,
            'last_month_arrears' => $lastMonthSummary['arrears'],
            'this_month_arrears' => $summary['arrears'],
            'total_collected' => round(
                $tenancy->rentPayments->sum(fn ($payment) => (float) $payment->amount),
                2
            ),
        ];
    }

    public function store(Request $request, int $tenancy)
    {
        $tenancy = $this->ownedTenancy($tenancy);
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'paid_at' => ['required', 'date', 'before_or_equal:now'],
            'transaction_code' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $transactionCode = trim($validated['transaction_code'] ?? '');
        DB::transaction(function () use ($tenancy, $validated, $transactionCode) {
            $lockedTenancy = Tenancy::where('landlord_id', $tenancy->landlord_id)
                ->lockForUpdate()
                ->findOrFail($tenancy->id);

            if ($transactionCode !== '' &&
                $this->hasRecordedCode($lockedTenancy, $transactionCode, true)) {
                throw ValidationException::withMessages([
                    'transaction_code' => 'This transaction code is already recorded for this tenancy.',
                ]);
            }

            $lockedTenancy->rentPayments()->create([
                'amount' => $validated['amount'],
                'paid_at' => $validated['paid_at'],
                'transaction_code' => $transactionCode !== '' ? $transactionCode : null,
                'payment_method' => $validated['payment_method'] ?? null,
                'message' => $validated['message'] ?? null,
                'source' => 'landlord',
                'recorded_by' => Auth::id(),
            ]);
        });

        return redirect()
            ->route('payments.index')
            ->with('success', 'Rent payment recorded successfully.');
    }

    public function approve(int $submission)
    {
        $landlordId = $this->landlordId();

        DB::transaction(function () use ($submission, $landlordId) {
            $record = RentPaymentSubmission::whereHas(
                'tenancy',
                fn ($query) => $query->where('landlord_id', $landlordId)
            )->lockForUpdate()->findOrFail($submission);

            if ($record->status !== 'pending') {
                throw ValidationException::withMessages([
                    'submission' => 'This payment report has already been reviewed.',
                ]);
            }

            $tenancy = Tenancy::where('landlord_id', $landlordId)
                ->lockForUpdate()
                ->findOrFail($record->tenancy_id);

            if ($this->hasRecordedCode($tenancy, $record->transaction_code)) {
                throw ValidationException::withMessages([
                    'submission' => 'A payment with this transaction code is already recorded.',
                ]);
            }

            $payment = $tenancy->rentPayments()->create([
                'amount' => $record->amount,
                'paid_at' => $record->transaction_date,
                'transaction_code' => $record->transaction_code,
                'payment_method' => 'Tenant reported',
                'message' => $record->message,
                'source' => 'tenant',
                'recorded_by' => Auth::id(),
            ]);

            $record->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'rent_payment_id' => $payment->id,
            ]);
        });

        return redirect()
            ->route('payments.index')
            ->with('success', 'Payment report approved and added to the payment ledger.');
    }

    public function reject(Request $request, int $submission)
    {
        $landlordId = $this->landlordId();
        $validated = $request->validate([
            'review_message' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($submission, $landlordId, $validated) {
            $record = RentPaymentSubmission::whereHas(
                'tenancy',
                fn ($query) => $query->where('landlord_id', $landlordId)
            )->lockForUpdate()->findOrFail($submission);

            if ($record->status !== 'pending') {
                throw ValidationException::withMessages([
                    'submission' => 'This payment report has already been reviewed.',
                ]);
            }

            $record->update([
                'status' => 'rejected',
                'review_message' => $validated['review_message'] ?? null,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        });

        return redirect()
            ->route('payments.index')
            ->with('success', 'Payment report rejected.');
    }

    public function share(int $tenancy)
    {
        $tenancy = $this->ownedTenancy($tenancy);
        $token = Str::random(64);

        $tenancy->update([
            'payment_share_token_hash' => hash('sha256', $token),
        ]);

        return redirect()->back()
            ->with('success', 'A new tenant payment link was created. Copy it now; the raw link is only shown once.')
            ->with('share_url', route('tenant-payments.show', ['token' => $token]));
    }

    public function revokeShare(int $tenancy)
    {
        $this->ownedTenancy($tenancy)->update([
            'payment_share_token_hash' => null,
        ]);

        return redirect()->back()
            ->with('success', 'The tenant payment link has been revoked.');
    }

    public function publicHistory(string $token, RentPaymentLedger $ledger)
    {
        $tenancy = $this->sharedTenancy($token);
        $summary = $ledger->summarize(
            $tenancy->rentCharges,
            $tenancy->rentPayments
        );

        return $this->privateShareView(
            'rent-payments.public',
            compact('tenancy', 'summary', 'token')
        );
    }

    public function submitPublic(Request $request, string $token)
    {
        $tenancy = $this->sharedTenancy($token);
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'transaction_date' => ['required', 'date', 'before_or_equal:now'],
            'transaction_code' => ['required', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $transactionCode = trim($validated['transaction_code']);
        DB::transaction(function () use ($tenancy, $validated, $transactionCode, $token) {
            $lockedTenancy = Tenancy::where(
                'payment_share_token_hash',
                hash('sha256', $token)
            )->lockForUpdate()->findOrFail($tenancy->id);

            if ($this->hasRecordedCode($lockedTenancy, $transactionCode, true)) {
                throw ValidationException::withMessages([
                    'transaction_code' => 'A payment report with this transaction code is already on record.',
                ]);
            }

            $lockedTenancy->rentPaymentSubmissions()->create([
                'amount' => $validated['amount'],
                'transaction_date' => $validated['transaction_date'],
                'transaction_code' => $transactionCode,
                'message' => $validated['message'] ?? null,
            ]);
        });

        return redirect()
            ->route('tenant-payments.show', ['token' => $token])
            ->with('success', 'Your payment details were submitted for landlord review.');
    }

    public function publicReceipt(RentPaymentLedger $ledger, string $token, int $payment)
    {
        $tenancy = $this->sharedTenancy($token);
        $payment = $tenancy->rentPayments()
            ->findOrFail($payment);
        $charges = $tenancy->rentCharges()
            ->where('month_year', '<=', $payment->paid_at->format('Y-m'))
            ->orderBy('month_year')
            ->get();
        $payments = $tenancy->rentPayments()
            ->where('paid_at', '<=', $payment->paid_at)
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get();
        $paymentAllocations = $ledger->allocationsForPayment(
            $charges,
            $payments,
            (int) $payment->id
        );

        return $this->privateShareView(
            'rent-payments.receipt',
            compact('tenancy', 'payment', 'paymentAllocations', 'token')
        );
    }

    private function privateShareView(string $view, array $data)
    {
        return response()->view($view, $data)
            ->header('Cache-Control', 'private, no-store, no-cache, must-revalidate')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function hasRecordedCode(
        Tenancy $tenancy,
        string $code,
        bool $includePendingSubmissions = false
    ): bool {
        $exists = $tenancy->rentPayments()
            ->where('transaction_code', $code)
            ->exists();

        if ($exists || !$includePendingSubmissions) {
            return $exists;
        }

        return $tenancy->rentPaymentSubmissions()
            ->where('transaction_code', $code)
            ->where('status', 'pending')
            ->exists();
    }
}
