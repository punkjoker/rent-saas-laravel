<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('accommodations')) {
            Schema::create('accommodations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('landlord_id')->constrained()->cascadeOnDelete();
                $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
                $table->string('title');
                $table->string('place_name', 150);
                $table->string('address')->nullable();
                $table->text('description')->nullable();
                $table->decimal('price_per_night', 12, 2);
                $table->unsignedSmallInteger('max_guests')->default(1);
                $table->unsignedTinyInteger('bedrooms')->nullable();
                $table->unsignedTinyInteger('bathrooms')->nullable();
                $table->string('status', 20)->default('draft')->index();
                $table->timestamp('published_at')->nullable();
                $table->unsignedBigInteger('clicks_count')->default(0);
                $table->timestamps();

                $table->index(['status', 'place_name', 'price_per_night']);
                $table->index(['landlord_id', 'status']);
            });
        }

        if (! Schema::hasTable('accommodation_images')) {
            Schema::create('accommodation_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('accommodation_id')->constrained()->cascadeOnDelete();
                $table->string('image_path');
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['accommodation_id', 'sort_order']);
            });
        }

        if (! Schema::hasTable('accommodation_bookings')) {
            Schema::create('accommodation_bookings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('accommodation_id')->constrained()->cascadeOnDelete();
                $table->string('guest_name', 150);
                $table->string('guest_email');
                $table->string('guest_phone', 30);
                $table->date('check_in');
                $table->date('check_out');
                $table->unsignedSmallInteger('guests');
                $table->text('guest_message')->nullable();
                $table->string('status', 20)->default('pending')->index();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();

                $table->index(['accommodation_id', 'check_in', 'check_out']);
            });
        }

        if (! Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->string('email');
                $table->string('phone', 30)->nullable();
                $table->string('subject', 200);
                $table->text('message');
                $table->string('status', 20)->default('new')->index();
                $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('handled_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('support_conversations')) {
            Schema::create('support_conversations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('landlord_id')->unique()->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('support_messages')) {
            Schema::create('support_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained('support_conversations')->cascadeOnDelete();
                $table->foreignId('sender_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('sender_type', 20);
                $table->text('message');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['conversation_id', 'created_at']);
                $table->index(['sender_type', 'read_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_conversations');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('accommodation_bookings');
        Schema::dropIfExists('accommodation_images');
        Schema::dropIfExists('accommodations');
    }
};
