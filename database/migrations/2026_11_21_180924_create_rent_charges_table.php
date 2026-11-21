<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rent_charges', function (Blueprint $table) {
            $table->id();

            // Each charge belongs to one specific tenancy.
            $table->foreignId('tenancy_id')
                ->constrained('tenancies')
                ->restrictOnDelete();

            // Billing month stored as YYYY-MM, for example 2026-11.
            $table->string('month_year', 7);

            $table->decimal('rent_amount', 12, 2)->default(0);
            $table->decimal('garbage_fee', 12, 2)->default(0);
            $table->decimal('water_bill', 12, 2)->default(0);
            $table->decimal('total_due', 12, 2)->default(0);

            $table->timestamps();

            // A tenancy can only have one charge record per month.
            $table->unique(
                ['tenancy_id', 'month_year'],
                'rent_charges_tenancy_month_unique'
            );

            $table->index('month_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_charges');
    }
};