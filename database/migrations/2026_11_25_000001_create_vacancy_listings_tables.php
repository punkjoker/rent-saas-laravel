<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancy_listings', function (Blueprint $table) {
            $table->id();
            $table->string('source', 20);
            $table->foreignId('property_id')
                ->nullable()
                ->constrained('properties')
                ->nullOnDelete();
            $table->foreignId('unit_id')
                ->nullable()
                ->unique()
                ->constrained('units')
                ->nullOnDelete();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('title');
            $table->string('place_name', 150);
            $table->string('address')->nullable();
            $table->string('unit_type', 80)->nullable();
            $table->decimal('monthly_rent', 12, 2);
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('published')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['source', 'place_name', 'status']);
        });

        Schema::create('vacancy_listing_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_listing_id')
                ->constrained('vacancy_listings')
                ->cascadeOnDelete();
            $table->string('image_path');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['vacancy_listing_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancy_listing_images');
        Schema::dropIfExists('vacancy_listings');
    }
};
