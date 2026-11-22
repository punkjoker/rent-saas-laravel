<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenancies', function (Blueprint $table) {
            $table->char('payment_share_token_hash', 64)
                ->nullable()
                ->unique();
        });
    }

    public function down(): void
    {
        Schema::table('tenancies', function (Blueprint $table) {
            $table->dropUnique(['payment_share_token_hash']);
            $table->dropColumn('payment_share_token_hash');
        });
    }
};
