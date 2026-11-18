
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            $table->foreignId('landlord_id')
                ->constrained('landlords')
                ->cascadeOnDelete();

            $table->string('property_name');
            $table->string('property_code')->nullable();

            $table->string('property_type', 50)->default('apartment');

            $table->string('address')->nullable();
            $table->string('town')->nullable();
            $table->string('county')->nullable();

            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['landlord_id', 'property_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
