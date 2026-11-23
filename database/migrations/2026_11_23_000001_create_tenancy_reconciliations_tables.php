<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenancy_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenancy_id')
                ->unique()
                ->constrained('tenancies')
                ->cascadeOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tenancy_reconciliation_damages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenancy_reconciliation_id')
                ->constrained('tenancy_reconciliations')
                ->cascadeOnDelete();
            $table->string('description', 255);
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->index('tenancy_reconciliation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenancy_reconciliation_damages');
        Schema::dropIfExists('tenancy_reconciliations');
    }
};
