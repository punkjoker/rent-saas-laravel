
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('landlord_id')
                ->constrained('landlords')
                ->cascadeOnDelete();

            $table->foreignId('plan_id')
                ->nullable()
                ->constrained('subscription_plans')
                ->nullOnDelete();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();

            $table->dateTime('trial_ends_at')->nullable();

            $table->string('status', 30)->default('trial');

            $table->decimal('amount_paid', 12, 2)->default(0);

            $table->string('payment_reference')->nullable();
            $table->string('payment_method', 50)->nullable();

            $table->dateTime('last_payment_at')->nullable();

            $table->timestamps();

            $table->index(['landlord_id', 'status']);
            $table->index(['status', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
