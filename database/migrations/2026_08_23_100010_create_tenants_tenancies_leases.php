<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name');
            $table->string('bangla_name')->nullable();
            $table->string('nid')->nullable();
            $table->string('passport')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->index(['full_name', 'is_deleted']);
            $table->index('nid');
            $table->index('phone');
        });

        Schema::create('tenancies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignUuid('unit_id')->constrained('units')->cascadeOnDelete();
            $table->date('move_in_date')->nullable();
            $table->date('move_out_date')->nullable();
            $table->decimal('monthly_rent', 12, 2)->default(0);
            $table->decimal('deposit', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['property_id', 'unit_id', 'status']);
            $table->index('move_out_date');
        });

        Schema::create('leases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenancy_id')->nullable()->constrained('tenancies')->nullOnDelete();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('monthly_rent', 12, 2)->default(0);
            $table->decimal('security_deposit', 12, 2)->default(0);
            $table->date('renewal_date')->nullable();
            $table->text('terms')->nullable();
            $table->string('document_path')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['unit_id', 'status']);
            $table->index('end_date');
        });

        Schema::create('rent_changes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenancy_id')->nullable()->constrained('tenancies')->cascadeOnDelete();
            $table->foreignUuid('unit_id')->nullable()->constrained('units')->cascadeOnDelete();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('old_rent', 12, 2)->default(0);
            $table->decimal('new_rent', 12, 2)->default(0);
            $table->date('effective_date')->nullable();
            $table->string('proration_method')->default('calendar');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['tenancy_id', 'effective_date']);
            $table->index(['unit_id', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_changes');
        Schema::dropIfExists('leases');
        Schema::dropIfExists('tenancies');
        Schema::dropIfExists('tenants');
    }
};
