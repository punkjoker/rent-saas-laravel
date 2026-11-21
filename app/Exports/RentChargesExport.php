<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RentChargesExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(private Collection $charges)
    {
    }

    public function collection(): Collection
    {
        $rows = $this->charges->map(function ($charge) {
            $tenancy = $charge->tenancy;

            return [
                $charge->month_year,
                $tenancy->property->property_name ?? '',
                $tenancy->unit->unit_number ?? '',
                $tenancy->tenant->full_name ?? '',
                $tenancy->tenant->phone ?? '',
                $charge->rent_amount,
                $charge->garbage_fee,
                $charge->water_bill,
                $charge->total_due,
            ];
        });

        $rows->push([
            'TOTAL',
            '',
            '',
            '',
            '',
            $this->charges->sum(fn ($charge) => (float) $charge->rent_amount),
            $this->charges->sum(fn ($charge) => (float) $charge->garbage_fee),
            $this->charges->sum(fn ($charge) => (float) $charge->water_bill),
            $this->charges->sum(fn ($charge) => (float) $charge->total_due),
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Billing Month',
            'Property',
            'House Number',
            'Tenant',
            'Phone',
            'Rent (KES)',
            'Garbage (KES)',
            'Water (KES)',
            'Total Due (KES)',
        ];
    }
}
