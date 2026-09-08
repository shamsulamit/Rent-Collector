<?php

namespace App\Providers;

use App\Models\BackupRecord;
use App\Models\Bill;
use App\Models\Document;
use App\Models\ElectricityBill;
use App\Models\Expense;
use App\Models\MaintenanceTicket;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tariff;
use App\Models\Tenancy;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vendor;
use App\Policies\BackupRecordPolicy;
use App\Policies\BillPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\ElectricityBillPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\MaintenanceTicketPolicy;
use App\Policies\MeterPolicy;
use App\Policies\MeterReadingPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PropertyPolicy;
use App\Policies\TariffPolicy;
use App\Policies\TenancyPolicy;
use App\Policies\TenantPolicy;
use App\Policies\UnitPolicy;
use App\Policies\UserPolicy;
use App\Policies\VendorPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('helpers.php');
        $this->app->singleton(\App\Services\AuditService::class);
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useTailwind();

        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(Unit::class, UnitPolicy::class);
        Gate::policy(Tenant::class, TenantPolicy::class);
        Gate::policy(Bill::class, BillPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(MaintenanceTicket::class, MaintenanceTicketPolicy::class);
        Gate::policy(Meter::class, MeterPolicy::class);
        Gate::policy(MeterReading::class, MeterReadingPolicy::class);
        Gate::policy(ElectricityBill::class, ElectricityBillPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(BackupRecord::class, BackupRecordPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Vendor::class, VendorPolicy::class);
        Gate::policy(Tariff::class, TariffPolicy::class);
        Gate::policy(Tenancy::class, TenancyPolicy::class);

        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('owner') ? true : null;
        });
    }
}
