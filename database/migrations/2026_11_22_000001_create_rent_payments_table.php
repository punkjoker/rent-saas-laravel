<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rent_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenancy_id')
                ->constrained('tenancies')
                ->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->dateTime('paid_at');
            $table->string('transaction_code', 100)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->text('message')->nullable();
            $table->string('source', 30);
            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['tenancy_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_payments');
    }
};
