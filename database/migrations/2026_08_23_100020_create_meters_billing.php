<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type')->default('metered'); // fixed | metered | slab | recurring
            $table->boolean('is_metered')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['name']);
        });

        Schema::create('meters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignUuid('floor_id')->nullable()->constrained('floors')->nullOnDelete();
            $table->foreignUuid('unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignUuid('utility_type_id')->nullable()->constrained('utility_types')->nullOnDelete();
            $table->string('meter_number');
            $table->string('provider')->nullable();
            $table->string('meter_type')->default('postpaid'); // postpaid | prepaid
            $table->string('utility')->default('electricity'); // electricity | gas | water
            $table->string('measurement_unit')->default('kWh');
            $table->date('installation_date')->nullable();
            $table->decimal('starting_reading', 12, 2)->default(0);
            $table->string('status')->default('active'); // active | closed | inactive
            $table->text('notes')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->unique(['meter_number']);
            $table->index(['unit_id', 'status']);
            $table->index(['utility', 'status']);
        });

        Schema::create('meter_readings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('meter_id')->constrained('meters')->cascadeOnDelete();
            $table->foreignUuid('property_id')->nullable()->constrained('properties')->cascadeOnDelete();
            $table->foreignUuid('unit_id')->nullable()->constrained('units')->cascadeOnDelete();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignUuid('tenancy_id')->nullable()->constrained('tenancies')->nullOnDelete();
            $table->string('billing_month', 7); // YYYY-MM
            $table->decimal('previous_reading', 12, 2)->default(0);
            $table->decimal('current_reading', 12, 2)->default(0);
            $table->decimal('usage', 12, 2)->default(0);
            $table->date('reading_date')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status')->default('draft'); // draft | submitted | finalized
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['meter_id', 'billing_month']);
            $table->index(['unit_id', 'billing_month']);
            $table->index(['property_id', 'billing_month', 'status']);
        });

        Schema::create('tariffs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('provider')->nullable();
            $table->string('utility')->default('electricity');
            $table->string('meter_type')->default('postpaid');
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->json('slabs')->nullable(); // [{min, max, rate}]
            $table->decimal('fixed_charge', 12, 2)->default(0);
            $table->decimal('service_charge', 12, 2)->default(0);
            $table->decimal('demand_charge', 12, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(0); // percent
            $table->decimal('other_charge', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['utility', 'meter_type', 'effective_date']);
        });

        Schema::create('electricity_bills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('property_id')->nullable()->constrained('properties')->cascadeOnDelete();
            $table->foreignUuid('unit_id')->nullable()->constrained('units')->cascadeOnDelete();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignUuid('tenancy_id')->nullable()->constrained('tenancies')->nullOnDelete();
            $table->foreignUuid('meter_id')->nullable()->constrained('meters')->nullOnDelete();
            $table->foreignUuid('tariff_id')->nullable()->constrained('tariffs')->nullOnDelete();
            $table->string('billing_month', 7);
            $table->decimal('previous_reading', 12, 2)->default(0);
            $table->decimal('current_reading', 12, 2)->default(0);
            $table->decimal('usage', 12, 2)->default(0);
            $table->decimal('energy_charge', 12, 2)->default(0);
            $table->decimal('fixed_charge', 12, 2)->default(0);
            $table->decimal('service_charge', 12, 2)->default(0);
            $table->decimal('demand_charge', 12, 2)->default(0);
            $table->decimal('vat', 12, 2)->default(0);
            $table->decimal('other_charge', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('adjustment', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('status')->default('draft');
            $table->date('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['meter_id', 'billing_month']);
            $table->index(['unit_id', 'billing_month', 'status']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('gas_bills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('meter_id')->nullable()->constrained('meters')->nullOnDelete();
            $table->foreignUuid('property_id')->nullable()->constrained('properties')->cascadeOnDelete();
            $table->foreignUuid('unit_id')->nullable()->constrained('units')->cascadeOnDelete();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignUuid('tenancy_id')->nullable()->constrained('tenancies')->nullOnDelete();
            $table->string('billing_month', 7);
            $table->decimal('previous_reading', 12, 2)->default(0);
            $table->decimal('current_reading', 12, 2)->default(0);
            $table->decimal('usage', 12, 2)->default(0);
            $table->decimal('rate', 12, 2)->default(0);
            $table->decimal('charge', 12, 2)->default(0);
            $table->string('photo_path')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->unique(['meter_id', 'billing_month']);
            $table->index(['unit_id', 'billing_month']);
        });

        Schema::create('water_bills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('meter_id')->nullable()->constrained('meters')->nullOnDelete();
            $table->foreignUuid('property_id')->nullable()->constrained('properties')->cascadeOnDelete();
            $table->foreignUuid('unit_id')->nullable()->constrained('units')->cascadeOnDelete();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignUuid('tenancy_id')->nullable()->constrained('tenancies')->nullOnDelete();
            $table->string('billing_month', 7);
            $table->decimal('previous_reading', 12, 2)->default(0);
            $table->decimal('current_reading', 12, 2)->default(0);
            $table->decimal('usage', 12, 2)->default(0);
            $table->decimal('rate', 12, 2)->default(0);
            $table->decimal('charge', 12, 2)->default(0);
            $table->string('photo_path')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->unique(['meter_id', 'billing_month']);
            $table->index(['unit_id', 'billing_month']);
        });

        Schema::create('prepaid_recharges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('meter_id')->constrained('meters')->cascadeOnDelete();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignUuid('unit_id')->nullable()->constrained('units')->cascadeOnDelete();
            $table->date('recharge_date');
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('balance_after', 12, 2)->default(0);
            $table->string('reference')->nullable();
            $table->string('provider')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['meter_id', 'recharge_date']);
            $table->index(['unit_id', 'recharge_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prepaid_recharges');
        Schema::dropIfExists('water_bills');
        Schema::dropIfExists('gas_bills');
        Schema::dropIfExists('electricity_bills');
        Schema::dropIfExists('tariffs');
        Schema::dropIfExists('meter_readings');
        Schema::dropIfExists('meters');
        Schema::dropIfExists('utility_types');
    }
};
