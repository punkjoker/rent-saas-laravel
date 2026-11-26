<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->foreignId('subscription_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Existing payment rows may legitimately have no subscription yet.
    }
};
