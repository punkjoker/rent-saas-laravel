
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();

            $table->foreignId('property_id')
                ->constrained('properties')
                ->cascadeOnDelete();

            $table->string('unit_number');
            $table->string('unit_type', 50)->nullable();

            $table->decimal('monthly_rent', 12, 2)->default(0);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->decimal('garbage_fee', 12, 2)->default(0);

            $table->string('status', 30)->default('vacant');

            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(['property_id', 'unit_number']);
            $table->index(['property_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
