
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('landlord_id')
                ->constrained('landlords')
                ->cascadeOnDelete();

            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);

            $table->string('identification_type', 30)->nullable();
            $table->string('identification_number', 100)->nullable();

            $table->string('phone', 30);
            $table->string('alternative_phone', 30)->nullable();
            $table->string('email')->nullable();

            $table->date('date_of_birth')->nullable();
            $table->string('occupation', 150)->nullable();
            $table->text('address')->nullable();

            $table->string('emergency_contact_name', 150)->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['landlord_id', 'identification_number'],
                'tenants_landlord_identification_unique'
            );

            $table->index(['landlord_id', 'last_name', 'first_name']);
            $table->index(['landlord_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
