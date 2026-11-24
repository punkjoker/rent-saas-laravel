<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subscription_payments')) {
            Schema::create('subscription_payments', function (Blueprint $table) {
                $this->addColumns($table);
            });

            return;
        }

        Schema::table('subscription_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('subscription_payments', 'landlord_id')) {
                $table->foreignId('landlord_id')
                    ->nullable()
                    ->constrained('landlords')
                    ->cascadeOnDelete();
            }
            if (!Schema::hasColumn('subscription_payments', 'subscription_id')) {
                $table->foreignId('subscription_id')
                    ->nullable()
                    ->constrained('subscriptions')
                    ->cascadeOnDelete();
            }
            if (!Schema::hasColumn('subscription_payments', 'provider')) {
                $table->string('provider', 30)->default('mpesa');
            }
            if (!Schema::hasColumn('subscription_payments', 'phone')) {
                $table->string('phone', 30)->nullable();
            }
            if (!Schema::hasColumn('subscription_payments', 'amount')) {
                $table->decimal('amount', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('subscription_payments', 'currency')) {
                $table->string('currency', 3)->default('KES');
            }
            if (!Schema::hasColumn('subscription_payments', 'account_reference')) {
                $table->string('account_reference')->nullable();
            }
            if (!Schema::hasColumn('subscription_payments', 'checkout_request_id')) {
                $table->string('checkout_request_id')->nullable()->unique();
            }
            if (!Schema::hasColumn('subscription_payments', 'merchant_request_id')) {
                $table->string('merchant_request_id')->nullable();
            }
            if (!Schema::hasColumn('subscription_payments', 'mpesa_receipt_number')) {
                $table->string('mpesa_receipt_number')->nullable()->unique();
            }
            if (!Schema::hasColumn('subscription_payments', 'status')) {
                $table->string('status', 30)->default('pending');
            }
            if (!Schema::hasColumn('subscription_payments', 'result_code')) {
                $table->string('result_code')->nullable();
            }
            if (!Schema::hasColumn('subscription_payments', 'result_description')) {
                $table->text('result_description')->nullable();
            }
            if (!Schema::hasColumn('subscription_payments', 'callback_payload')) {
                $table->json('callback_payload')->nullable();
            }
            if (!Schema::hasColumn('subscription_payments', 'paid_at')) {
                $table->dateTime('paid_at')->nullable();
            }
        });

    }

    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropForeign(['landlord_id']);
            $table->dropForeign(['subscription_id']);
            $table->dropColumn([
                'landlord_id',
                'subscription_id',
                'provider',
                'phone',
                'amount',
                'currency',
                'account_reference',
                'checkout_request_id',
                'merchant_request_id',
                'mpesa_receipt_number',
                'status',
                'result_code',
                'result_description',
                'callback_payload',
                'paid_at',
            ]);
        });
    }

    private function addColumns(Blueprint $table): void
    {
        $table->id();
        $table->foreignId('landlord_id')
            ->nullable()
            ->constrained('landlords')
            ->cascadeOnDelete();
        $table->foreignId('subscription_id')
            ->nullable()
            ->constrained('subscriptions')
            ->cascadeOnDelete();
        $table->string('provider', 30)->default('mpesa');
        $table->string('phone', 30)->nullable();
        $table->decimal('amount', 12, 2)->default(0);
        $table->string('currency', 3)->default('KES');
        $table->string('account_reference')->nullable();
        $table->string('checkout_request_id')->nullable()->unique();
        $table->string('merchant_request_id')->nullable();
        $table->string('mpesa_receipt_number')->nullable()->unique();
        $table->string('status', 30)->default('pending');
        $table->string('result_code')->nullable();
        $table->text('result_description')->nullable();
        $table->json('callback_payload')->nullable();
        $table->dateTime('paid_at')->nullable();
        $table->timestamps();
        $table->index(['landlord_id', 'status']);
        $table->index(['subscription_id', 'status']);
    }
};
