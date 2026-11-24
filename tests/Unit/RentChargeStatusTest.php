<?php

namespace Tests\Unit;

use App\Models\RentCharge;
use App\Models\RentPayment;
use App\Models\Tenancy;
use App\Services\RentChargeStatus;
use App\Services\RentPaymentLedger;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class RentChargeStatusTest extends TestCase
{
    public function test_charge_is_overdue_after_its_agreed_due_date(): void
    {
        $charge = $this->charge(100);
        $service = new RentChargeStatus(new RentPaymentLedger());

        $status = $service->forCharge(
            $charge,
            new Collection([$charge]),
            new Collection(),
            Carbon::parse('2026-02-06')
        );

        $this->assertSame('overdue', $status['status']);
        $this->assertSame('2026-02-05', $status['due_date']->toDateString());
        $this->assertSame(100.0, $status['balance']);
    }

    public function test_partial_payment_keeps_balance_before_due_date(): void
    {
        $charge = $this->charge(100);
        $payment = new RentPayment([
            'amount' => 40,
            'paid_at' => Carbon::parse('2026-02-03'),
        ]);
        $service = new RentChargeStatus(new RentPaymentLedger());

        $status = $service->forCharge(
            $charge,
            new Collection([$charge]),
            new Collection([$payment]),
            Carbon::parse('2026-02-04')
        );

        $this->assertSame('partial', $status['status']);
        $this->assertSame(40.0, $status['paid']);
        $this->assertSame(60.0, $status['balance']);
    }

    public function test_partial_payment_past_the_due_date_is_still_overdue(): void
    {
        $charge = $this->charge(100);
        $payment = new RentPayment([
            'amount' => 40,
            'paid_at' => Carbon::parse('2026-02-06'),
        ]);
        $service = new RentChargeStatus(new RentPaymentLedger());

        $status = $service->forCharge(
            $charge,
            new Collection([$charge]),
            new Collection([$payment]),
            Carbon::parse('2026-02-07')
        );

        $this->assertSame('overdue', $status['status']);
        $this->assertSame(60.0, $status['balance']);
    }

    public function test_unpaid_charge_is_not_overdue_on_the_due_date(): void
    {
        $charge = $this->charge(100);
        $service = new RentChargeStatus(new RentPaymentLedger());

        $status = $service->forCharge(
            $charge,
            new Collection([$charge]),
            new Collection(),
            Carbon::parse('2026-02-05')
        );

        $this->assertSame('due', $status['status']);
    }

    public function test_fully_paid_charge_is_not_overdue_after_its_due_date(): void
    {
        $charge = $this->charge(100);
        $payment = new RentPayment([
            'amount' => 100,
            'paid_at' => Carbon::parse('2026-02-08'),
        ]);
        $service = new RentChargeStatus(new RentPaymentLedger());

        $status = $service->forCharge(
            $charge,
            new Collection([$charge]),
            new Collection([$payment]),
            Carbon::parse('2026-02-09')
        );

        $this->assertSame('paid', $status['status']);
        $this->assertSame(0.0, $status['balance']);
    }

    private function charge(float $amount): RentCharge
    {
        $tenancy = new Tenancy([
            'rent_due_day' => 5,
            'start_date' => '2026-01-01',
        ]);
        $charge = new RentCharge([
            'month_year' => '2026-02',
            'total_due' => $amount,
        ]);
        $charge->id = 1;
        $charge->tenancy_id = 1;
        $charge->setRelation('tenancy', $tenancy);

        return $charge;
    }
}
