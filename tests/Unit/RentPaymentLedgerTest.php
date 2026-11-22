<?php

namespace Tests\Unit;

use App\Models\RentCharge;
use App\Models\RentPayment;
use App\Services\RentPaymentLedger;
use Tests\TestCase;

class RentPaymentLedgerTest extends TestCase
{
    public function test_payments_are_applied_to_the_oldest_charges_before_newer_charges(): void
    {
        $charges = collect([
            new RentCharge(['month_year' => '2026-01', 'total_due' => 100]),
            new RentCharge(['month_year' => '2026-02', 'total_due' => 100]),
        ]);
        $payments = collect([
            new RentPayment(['amount' => 150]),
        ]);

        $summary = (new RentPaymentLedger())->summarize($charges, $payments);

        $this->assertSame([100.0, 50.0], array_column($summary['months'], 'paid'));
        $this->assertSame([0.0, 50.0], array_column($summary['months'], 'balance'));
        $this->assertSame(50.0, $summary['arrears']);
        $this->assertSame(0.0, $summary['credit']);
    }

    public function test_payment_exceeding_saved_charges_is_carried_as_credit(): void
    {
        $charges = collect([
            new RentCharge(['month_year' => '2026-01', 'total_due' => 80]),
        ]);
        $payments = collect([
            new RentPayment(['amount' => 100]),
        ]);

        $summary = (new RentPaymentLedger())->summarize($charges, $payments);

        $this->assertSame(80.0, $summary['total_paid']);
        $this->assertSame(0.0, $summary['arrears']);
        $this->assertSame(20.0, $summary['credit']);
    }

    public function test_receipt_allocation_shows_oldest_charges_and_remaining_credit(): void
    {
        $chargeOne = new RentCharge([
            'month_year' => '2026-01',
            'total_due' => 50,
        ]);
        $chargeOne->setAttribute('id', 1);
        $chargeTwo = new RentCharge([
            'month_year' => '2026-02',
            'total_due' => 50,
        ]);
        $chargeTwo->setAttribute('id', 2);

        $previousPayment = new RentPayment([
            'amount' => 20,
            'paid_at' => '2026-02-01 09:00:00',
        ]);
        $previousPayment->setAttribute('id', 1);
        $receiptPayment = new RentPayment([
            'amount' => 100,
            'paid_at' => '2026-02-02 09:00:00',
        ]);
        $receiptPayment->setAttribute('id', 2);

        $result = (new RentPaymentLedger())->allocationsForPayment(
            collect([$chargeOne, $chargeTwo]),
            collect([$previousPayment, $receiptPayment]),
            2
        );

        $this->assertSame(
            [
                ['month' => '2026-01', 'amount' => 30.0],
                ['month' => '2026-02', 'amount' => 50.0],
            ],
            collect($result['allocations'])->map(fn ($allocation) => [
                'month' => $allocation['charge']->month_year,
                'amount' => $allocation['amount'],
            ])->all()
        );
        $this->assertSame(20.0, $result['unallocated']);
    }
}
