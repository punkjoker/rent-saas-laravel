<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('subscription_payments', 'metadata')) {
            Schema::table('subscription_payments', function (Blueprint $table) {
                $table->json('metadata')->nullable();
            });
        }

        if (! Schema::hasTable('landlord_feature_access')) {
            Schema::create('landlord_feature_access', function (Blueprint $table) {
                $table->id();
                $table->foreignId('landlord_id')->constrained()->cascadeOnDelete();
                $table->string('feature', 50);
                $table->dateTime('starts_at');
                $table->dateTime('expires_at')->index();
                $table->foreignId('subscription_payment_id')
                    ->nullable()
                    ->unique()
                    ->constrained('subscription_payments')
                    ->nullOnDelete();
                $table->timestamps();

                $table->unique(['landlord_id', 'feature']);
            });
        }

        $now = now();
        DB::table('subscription_plans')->updateOrInsert(
            ['slug' => 'accommodations'],
            [
                'name' => 'Accommodations',
                'description' => 'Full access to Pango property management and the accommodation marketplace.',
                'price' => 4000,
                'tenant_limit' => null,
                'duration_days' => 30,
                'currency' => 'KES',
                'sort_order' => 6,
                'features' => json_encode([
                    'Full Pango property management',
                    'Accommodation listings and bookings',
                    'Accommodation reports and projections',
                    'Priority technical support',
                ]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('subscription_plans')->where('slug', 'accommodations')->delete();
        Schema::dropIfExists('landlord_feature_access');
        if (Schema::hasColumn('subscription_payments', 'metadata')) {
            Schema::table('subscription_payments', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });
        }
    }
};
