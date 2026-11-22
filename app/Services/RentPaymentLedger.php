<?php

namespace App\Services;

use Illuminate\Support\Collection;

class RentPaymentLedger
{
    public function allocationsForPayment(
        Collection $charges,
        Collection $payments,
        int $paymentId
    ): array {
        $charges = $charges->sortBy('month_year')->values();
        $payments = $payments->sort(function ($a, $b) {
            $dateComparison = $a->paid_at <=> $b->paid_at;

            return $dateComparison !== 0
                ? $dateComparison
                : $a->id <=> $b->id;
        })->values();
        $paidByCharge = [];
        $allocations = [];
        $unallocated = 0.0;

        foreach ($payments as $payment) {
            $remaining = (float) $payment->amount;

            foreach ($charges as $charge) {
                $chargeKey = $charge->getKey() ?? spl_object_id($charge);
                $due = (float) $charge->total_due;
                $available = max($due - ($paidByCharge[$chargeKey] ?? 0), 0);
                $allocated = min($available, $remaining);

                if ($allocated <= 0) {
                    continue;
                }

                $paidByCharge[$chargeKey] = round(
                    ($paidByCharge[$chargeKey] ?? 0) + $allocated,
                    2
                );
                $remaining = round($remaining - $allocated, 2);

                if ((int) $payment->id === $paymentId) {
                    $allocations[] = [
                        'charge' => $charge,
                        'amount' => $allocated,
                    ];
                }

                if ($remaining <= 0) {
                    break;
                }
            }

            if ((int) $payment->id === $paymentId) {
                $unallocated = $remaining;
                break;
            }
        }

        return [
            'allocations' => $allocations,
            'unallocated' => round($unallocated, 2),
        ];
    }

    public function summarize(Collection $charges, Collection $payments): array
    {
        $charges = $charges->sortBy('month_year')->values();
        $remainingPayment = $payments->sum(
            fn ($payment) => (float) $payment->amount
        );
        $rentTotal = 0.0;
        $garbageTotal = 0.0;
        $waterTotal = 0.0;
        $totalDue = 0.0;
        $totalPaid = 0.0;
        $months = [];

        foreach ($charges as $charge) {
            $due = (float) $charge->total_due;
            $allocated = min($due, $remainingPayment);
            $remainingPayment = round($remainingPayment - $allocated, 2);
            $rentTotal += (float) $charge->rent_amount;
            $garbageTotal += (float) $charge->garbage_fee;
            $waterTotal += (float) $charge->water_bill;
            $totalDue += $due;
            $totalPaid += $allocated;

            $months[] = [
                'charge' => $charge,
                'due' => $due,
                'paid' => $allocated,
                'balance' => round($due - $allocated, 2),
            ];
        }

        return [
            'months' => $months,
            'rent_total' => round($rentTotal, 2),
            'garbage_total' => round($garbageTotal, 2),
            'water_total' => round($waterTotal, 2),
            'total_due' => round($totalDue, 2),
            'total_paid' => round($totalPaid, 2),
            'arrears' => round(max($totalDue - $totalPaid, 0), 2),
            'credit' => round($remainingPayment, 2),
        ];
    }
}
