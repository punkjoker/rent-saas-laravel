<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenancy_reconciliations', function (Blueprint $table) {
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->dateTime('refunded_at')->nullable();
            $table->string('refund_reference', 100)->nullable();
            $table->foreignId('refund_paid_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenancy_reconciliations', function (Blueprint $table) {
            $table->dropForeign(['refund_paid_by']);
            $table->dropColumn([
                'refund_amount',
                'refunded_at',
                'refund_reference',
                'refund_paid_by',
            ]);
        });
    }
};
