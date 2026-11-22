<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rent_payment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenancy_id')
                ->constrained('tenancies')
                ->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->dateTime('transaction_date');
            $table->string('transaction_code', 100);
            $table->text('message')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('review_message')->nullable();
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->foreignId('rent_payment_id')
                ->nullable()
                ->unique()
                ->constrained('rent_payments')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['tenancy_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_payment_submissions');
    }
};
