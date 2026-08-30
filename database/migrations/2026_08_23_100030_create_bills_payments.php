<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignUuid('unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignUuid('tenancy_id')->nullable()->constrained('tenancies')->nullOnDelete();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('bill_no')->nullable();
            $table->string('billing_month', 7);
            $table->decimal('rent', 12, 2)->default(0);
            $table->decimal('electricity', 12, 2)->default(0);
            $table->decimal('gas', 12, 2)->default(0);
            $table->decimal('water', 12, 2)->default(0);
            $table->decimal('waste', 12, 2)->default(0);
            $table->decimal('security', 12, 2)->default(0);
            $table->decimal('cleaning', 12, 2)->default(0);
            $table->decimal('internet', 12, 2)->default(0);
            $table->decimal('parking', 12, 2)->default(0);
            $table->decimal('other', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('adjustment', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('status')->default('draft');
            $table->date('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'billing_month', 'unit_id']);
            $table->index(['unit_id', 'billing_month', 'status']);
            $table->index(['property_id', 'billing_month']);
            $table->index('status');
        });

        Schema::create('bill_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->string('type'); // rent | electricity | gas | water | recurring | discount | adjustment | other
            $table->string('label')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->integer('sort')->default(0);
            $table->timestamps();

            $table->index(['bill_id', 'type']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('tenancy_id')->nullable()->constrained('tenancies')->nullOnDelete();
            $table->foreignUuid('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignUuid('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('payment_date');
            $table->string('method')->default('cash');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'payment_date']);
            $table->index(['property_id', 'payment_date']);
            $table->index('method');
        });

        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignUuid('bill_id')->nullable()->constrained('bills')->nullOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('strategy')->default('oldest-first');
            $table->foreignId('allocated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['payment_id']);
            $table->index(['bill_id']);
        });

        Schema::create('security_deposits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenancy_id')->nullable()->constrained('tenancies')->cascadeOnDelete();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('type')->default('received'); // received | additional | deduction | refund
            $table->date('date')->nullable();
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'type']);
            $table->index(['tenancy_id']);
        });

        Schema::create('accounting_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('period', 7)->unique(); // YYYY-MM
            $table->string('status')->default('open'); // open | closed
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_periods');
        Schema::dropIfExists('security_deposits');
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('bills');
    }
};
