<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->index(['status', 'is_deleted']);
            $table->index('city');
        });

        Schema::create('floors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->unique(['property_id', 'name']);
            $table->index(['property_id', 'status']);
        });

        Schema::create('units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignUuid('floor_id')->nullable()->constrained('floors')->nullOnDelete();
            $table->string('name');
            $table->string('unit_type')->nullable();
            $table->decimal('size', 10, 2)->nullable();
            $table->unsignedTinyInteger('bedrooms')->default(0);
            $table->unsignedTinyInteger('bathrooms')->default(1);
            $table->decimal('monthly_rent', 12, 2)->default(0);
            $table->string('status')->default('vacant');
            $table->text('notes')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->unique(['property_id', 'name']);
            $table->index(['property_id', 'floor_id', 'status']);
        });

        Schema::create('unit_vacancy_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('unit_id')->constrained('units')->cascadeOnDelete();
            $table->date('vacated_on')->nullable();
            $table->date('occupied_on')->nullable();
            $table->string('tenant_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['unit_id', 'occupied_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_vacancy_history');
        Schema::dropIfExists('units');
        Schema::dropIfExists('floors');
        Schema::dropIfExists('properties');
    }
};
