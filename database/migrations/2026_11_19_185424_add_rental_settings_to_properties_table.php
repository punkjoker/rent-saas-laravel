<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('unit_prefix', 20)->default('HSE');

            $table->decimal('default_rent', 12, 2)->default(0);
            $table->decimal('default_deposit', 12, 2)->default(0);
            $table->decimal('default_garbage_fee', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'unit_prefix',
                'default_rent',
                'default_deposit',
                'default_garbage_fee',
            ]);
        });
    }
};
