<?php

namespace Tests\Unit;

use App\Models\DepositPayment;
use App\Models\RentCharge;
use App\Models\RentPayment;
use App\Models\TenancyReconciliationDamage;
use App\Services\RentPaymentLedger;
use App\Services\TenancyReconciliationCalculator;
use Tests\TestCase;

class TenancyReconciliationCalculatorTest extends TestCase
{
    public function test_it_calculates_a_refund_after_arrears_and_damage_costs(): void
    {
        $calculator = new TenancyReconciliationCalculator(new RentPaymentLedger());

        $summary = $calculator->calculate(
            collect([
                new RentCharge(['month_year' => '2026-01', 'total_due' => 1000]),
            ]),
            collect([
                new RentPayment(['amount' => 600]),
            ]),
            collect([
                new DepositPayment(['amount' => 1500]),
            ]),
            collect([
                new TenancyReconciliationDamage(['amount' => 200]),
            ])
        );

        $this->assertSame(400.0, $summary['rent']['arrears']);
        $this->assertSame(1500.0, $summary['deposit_collected']);
        $this->assertSame(200.0, $summary['damage_total']);
        $this->assertSame(900.0, $summary['settlement_balance']);
    }

    public function test_it_preserves_a_negative_balance_when_deductions_exceed_deposit(): void
    {
        $calculator = new TenancyReconciliationCalculator(new RentPaymentLedger());

        $summary = $calculator->calculate(
            collect([
                new RentCharge(['month_year' => '2026-01', 'total_due' => 1000]),
            ]),
            collect(),
            collect([
                new DepositPayment(['amount' => 500]),
            ]),
            collect([
                new TenancyReconciliationDamage(['amount' => 200]),
            ])
        );

        $this->assertSame(-700.0, $summary['settlement_balance']);
    }
}
