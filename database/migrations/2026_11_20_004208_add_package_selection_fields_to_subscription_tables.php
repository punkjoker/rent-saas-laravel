
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedInteger('property_limit')
                ->nullable()
                ->after('tenant_limit');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('selected_plan_id')
                ->nullable()
                ->after('plan_id')
                ->constrained('subscription_plans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['selected_plan_id']);
            $table->dropColumn('selected_plan_id');
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('property_limit');
        });
    }
};
