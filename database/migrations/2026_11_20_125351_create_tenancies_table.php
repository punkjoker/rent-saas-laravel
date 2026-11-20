
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenancies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('landlord_id')
                ->constrained('landlords')
                ->cascadeOnDelete();

            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->restrictOnDelete();

            $table->foreignId('property_id')
                ->constrained('properties')
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            // Snapshot of the agreed charges at the time of allocation.
            $table->decimal('monthly_rent', 12, 2)->default(0);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->decimal('garbage_fee', 12, 2)->default(0);

            $table->date('start_date');
            $table->date('lease_end_date');
            $table->unsignedTinyInteger('rent_due_day');

            // upcoming, active, ended
            $table->string('status', 20)->default('active');

            $table->date('ended_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['landlord_id', 'status']);
            $table->index(['unit_id', 'start_date', 'lease_end_date']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenancies');
    }
};
