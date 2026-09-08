<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meters', function (Blueprint $table) {
            if (! Schema::hasColumn('meters', 'unit_price')) {
                $table->decimal('unit_price', 12, 4)->nullable()->after('starting_reading');
            }
        });

        Schema::table('prepaid_recharges', function (Blueprint $table) {
            if (! Schema::hasColumn('prepaid_recharges', 'units')) {
                $table->decimal('units', 12, 2)->default(0)->after('amount');
            }
            if (! Schema::hasColumn('prepaid_recharges', 'unit_price')) {
                $table->decimal('unit_price', 12, 4)->default(0)->after('units');
            }
            if (! Schema::hasColumn('prepaid_recharges', 'units_after')) {
                $table->decimal('units_after', 12, 2)->default(0)->after('unit_price');
            }
        });

        $this->addIndexIfMissing('meters', 'meters_deleted_status_idx', ['is_deleted', 'status']);
        $this->addIndexIfMissing('meters', 'meters_type_utility_idx', ['meter_type', 'utility']);
        $this->addIndexIfMissing('tenancies', 'tenancies_deleted_status_idx', ['is_deleted', 'status']);
        $this->addIndexIfMissing('gas_bills', 'gas_bills_month_status_idx', ['billing_month', 'status']);
        $this->addIndexIfMissing('water_bills', 'water_bills_month_status_idx', ['billing_month', 'status']);
        $this->addIndexIfMissing('prepaid_recharges', 'prepaid_meter_date_idx', ['meter_id', 'recharge_date']);
        $this->addIndexIfMissing('payments', 'payments_tenant_date_idx', ['tenant_id', 'payment_date']);
        $this->addIndexIfMissing('expenses', 'expenses_property_date_idx', ['property_id', 'expense_date']);
        $this->addIndexIfMissing('bills', 'bills_property_month_status_idx', ['property_id', 'billing_month', 'status']);
    }

    public function down(): void
    {
        $this->dropIndexIfExists('meters', 'meters_deleted_status_idx');
        $this->dropIndexIfExists('meters', 'meters_type_utility_idx');
        $this->dropIndexIfExists('tenancies', 'tenancies_deleted_status_idx');
        $this->dropIndexIfExists('gas_bills', 'gas_bills_month_status_idx');
        $this->dropIndexIfExists('water_bills', 'water_bills_month_status_idx');
        $this->dropIndexIfExists('prepaid_recharges', 'prepaid_meter_date_idx');
        $this->dropIndexIfExists('payments', 'payments_tenant_date_idx');
        $this->dropIndexIfExists('expenses', 'expenses_property_date_idx');
        $this->dropIndexIfExists('bills', 'bills_property_month_status_idx');
    }

    protected function addIndexIfMissing(string $table, string $name, array $columns): void
    {
        $existing = collect(Schema::getIndexes($table))->pluck('name')->all();
        if (in_array($name, $existing, true)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($name, $columns) {
            $blueprint->index($columns, $name);
        });
    }

    protected function dropIndexIfExists(string $table, string $name): void
    {
        $existing = collect(Schema::getIndexes($table))->pluck('name')->all();
        if (! in_array($name, $existing, true)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($name) {
            $blueprint->dropIndex($name);
        });
    }
};
