<?php

namespace App\Services;

use Illuminate\Support\Collection;

class TenancyReconciliationCalculator
{
    public function __construct(
        private RentPaymentLedger $rentPaymentLedger
    ) {
    }

    public function calculate(
        Collection $charges,
        Collection $rentPayments,
        Collection $depositPayments,
        Collection $damages
    ): array {
        $rent = $this->rentPaymentLedger->summarize($charges, $rentPayments);
        $depositCollected = round(
            $depositPayments->sum(fn ($payment) => (float) $payment->amount),
            2
        );
        $damageTotal = round(
            $damages->sum(fn ($damage) => (float) $damage->amount),
            2
        );

        return [
            'rent' => $rent,
            'deposit_collected' => $depositCollected,
            'damage_total' => $damageTotal,
            'settlement_balance' => round(
                $depositCollected - $rent['arrears'] - $damageTotal,
                2
            ),
        ];
    }
}
