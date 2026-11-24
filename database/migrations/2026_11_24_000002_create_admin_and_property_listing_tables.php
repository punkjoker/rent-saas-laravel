<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('landlord')->index();
        });

        Schema::table('landlords', function (Blueprint $table) {
            $table->string('approval_status', 20)->default('approved')->index();
            $table->text('rejection_reason')->nullable();
        });

        Schema::create('property_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')
                ->unique()
                ->constrained('properties')
                ->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->decimal('monthly_rent', 12, 2);
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('property_listing_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_listing_id')
                ->constrained('property_listings')
                ->cascadeOnDelete();
            $table->string('image_path');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['property_listing_id', 'sort_order']);
        });

        Schema::create('subscription_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landlord_id')
                ->constrained('landlords')
                ->cascadeOnDelete();
            $table->foreignId('subscription_id')
                ->constrained('subscriptions')
                ->cascadeOnDelete();
            $table->foreignId('admin_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->unsignedInteger('days');
            $table->dateTime('previous_ends_at')->nullable();
            $table->dateTime('new_ends_at');
            $table->string('reason', 500);
            $table->timestamps();

            $table->index(['landlord_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_extensions');
        Schema::dropIfExists('property_listing_images');
        Schema::dropIfExists('property_listings');

        Schema::table('landlords', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'rejection_reason']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }
};
