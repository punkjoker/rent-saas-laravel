
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('currency', 3)->default('KES');
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('features')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'currency',
                'sort_order',
                'features'
            ]);
        });
    }
};
