<?php

namespace App\Services;

use App\Models\RentCharge;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RentChargeStatus
{
    public function __construct(private RentPaymentLedger $ledger)
    {
    }

    public function forCharge(
        RentCharge $charge,
        Collection $charges,
        Collection $payments,
        Carbon $asOf
    ): array {
        $summary = $this->ledger->summarize($charges, $payments);
        $month = Carbon::createFromFormat('Y-m-d', $charge->month_year.'-01');
        $tenancy = $charge->tenancy;
        $dueDate = $month->copy()->day((int) $tenancy->rent_due_day);

        if ($tenancy->start_date &&
            $tenancy->start_date->format('Y-m') === $charge->month_year &&
            $tenancy->start_date->greaterThan($dueDate)) {
            $dueDate = $tenancy->start_date->copy();
        }

        $chargeSummary = collect($summary['months'])->first(
            fn ($row) => $row['charge']->is($charge)
        );
        $due = (float) $charge->total_due;
        $paid = (float) ($chargeSummary['paid'] ?? 0);
        $balance = max(round($due - $paid, 2), 0);

        $status = $balance <= 0
            ? 'paid'
            : ($dueDate->copy()->startOfDay()->lt($asOf->copy()->startOfDay())
                ? 'overdue'
                : ($paid > 0 ? 'partial' : 'due'));

        return [
            'due_date' => $dueDate,
            'paid' => round($paid, 2),
            'balance' => $balance,
            'status' => $status,
        ];
    }
}
