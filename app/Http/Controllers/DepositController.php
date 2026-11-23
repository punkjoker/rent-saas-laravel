<?php

namespace App\Http\Controllers;

use App\Models\DepositPayment;
use App\Models\Landlord;
use App\Models\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepositController extends Controller
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

    public function index(Request $request)
    {
        $landlordId = $this->landlordId();
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['search'] ?? '');
        $query = Tenancy::where('landlord_id', $landlordId)
            ->whereIn('status', ['active', 'upcoming']);

        if ($search !== '') {
            $terms = collect(preg_split('/\s+/', $search))
                ->filter()
                ->values();

            $query->where(function ($query) use ($search, $terms) {
                $query->whereHas('unit', function ($unitQuery) use ($search) {
                    $unitQuery->where('unit_number', 'like', "%{$search}%");
                })->orWhereHas('tenant', function ($tenantQuery) use ($terms) {
                    foreach ($terms as $term) {
                        $tenantQuery->where(function ($nameQuery) use ($term) {
                            $nameQuery->where('first_name', 'like', "%{$term}%")
                                ->orWhere('middle_name', 'like', "%{$term}%")
                                ->orWhere('last_name', 'like', "%{$term}%");
                        });
                    }
                });
            });
        }

        $tenancies = $query->with([
            'tenant',
            'property',
            'unit',
            'depositPayments',
        ])
            ->orderByDesc('start_date')
            ->get()
            ->map(function ($tenancy) {
                $collected = round(
                    $tenancy->depositPayments->sum(
                        fn ($payment) => (float) $payment->amount
                    ),
                    2
                );

                return [
                    'tenancy' => $tenancy,
                    'collected' => $collected,
                    'balance' => round(
                        max((float) $tenancy->deposit_amount - $collected, 0),
                        2
                    ),
                    'overpaid' => round(
                        max($collected - (float) $tenancy->deposit_amount, 0),
                        2
                    ),
                ];
            });
        $totals = [
            'agreed' => round($tenancies->sum(
                fn ($row) => (float) $row['tenancy']->deposit_amount
            ), 2),
            'collected' => round($tenancies->sum('collected'), 2),
            'balance' => round($tenancies->sum('balance'), 2),
        ];

        return view('deposits.index', compact('tenancies', 'totals', 'search'));
    }

    public function create(int $tenancy)
    {
        $tenancy = $this->ownedTenancy($tenancy);
        $collected = (float) $tenancy->depositPayments()->sum('amount');
        $balance = max((float) $tenancy->deposit_amount - $collected, 0);

        return view('deposits.create', compact('tenancy', 'balance'));
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

            if ($transactionCode !== '' && $lockedTenancy->depositPayments()
                ->where('transaction_code', $transactionCode)
                ->exists()) {
                throw ValidationException::withMessages([
                    'transaction_code' => 'This transaction code is already recorded for this tenancy deposit.',
                ]);
            }

            $lockedTenancy->depositPayments()->create([
                'amount' => $validated['amount'],
                'paid_at' => $validated['paid_at'],
                'transaction_code' => $transactionCode !== '' ? $transactionCode : null,
                'payment_method' => $validated['payment_method'] ?? null,
                'message' => $validated['message'] ?? null,
                'recorded_by' => Auth::id(),
            ]);
        });

        return redirect()
            ->route('deposits.index')
            ->with('success', 'Deposit payment recorded successfully.');
    }

    public function history(int $tenancy)
    {
        $tenancy = $this->ownedTenancy($tenancy);
        $payments = $tenancy->depositPayments()
            ->latest('paid_at')
            ->get();
        $collected = round(
            $payments->sum(fn ($payment) => (float) $payment->amount),
            2
        );
        $balance = round(
            max((float) $tenancy->deposit_amount - $collected, 0),
            2
        );

        return view('deposits.history', compact(
            'tenancy',
            'payments',
            'collected',
            'balance'
        ));
    }
}
