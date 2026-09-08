<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\OfflineSyncController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\WhatsAppController;
use App\Livewire\Audit\AuditIndex;
use App\Livewire\Backups\BackupsIndex;
use App\Livewire\Bills\BillsIndex;
use App\Livewire\Dashboard;
use App\Livewire\Electricity\ElectricityBillsIndex;
use App\Livewire\Expenses\ExpensesIndex;
use App\Livewire\Maintenance\MaintenanceIndex;
use App\Livewire\Meters\BulkReadings;
use App\Livewire\Meters\MetersIndex;
use App\Livewire\Payments\PaymentsIndex;
use App\Livewire\Prepaid\PrepaidIndex;
use App\Livewire\Properties\FloorsIndex;
use App\Livewire\Properties\PropertiesIndex;
use App\Livewire\Properties\PropertyShow;
use App\Livewire\Properties\UnitsIndex;
use App\Livewire\Reports\ReportsIndex;
use App\Livewire\Settings\SettingsIndex;
use App\Livewire\Tariffs\TariffsIndex;
use App\Livewire\Tenants\TenancyIndex;
use App\Livewire\Tenants\TenantShow;
use App\Livewire\Tenants\TenantsIndex;
use App\Livewire\Users\UsersIndex;
use App\Livewire\Utilities\UtilityBillsIndex;
use App\Livewire\Vendors\VendorsIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Offline sync API (web-authenticated JSON endpoints for the PWA queue)
Route::prefix('sync')->middleware('auth')->group(function () {
    Route::get('meta', [OfflineSyncController::class, 'meta']);
    Route::post('reading', [OfflineSyncController::class, 'storeReading']);
    Route::post('payment', [OfflineSyncController::class, 'storePayment']);
    Route::get('bills', [OfflineSyncController::class, 'bills']);
    Route::get('properties', [OfflineSyncController::class, 'properties']);
    Route::get('tenants', [OfflineSyncController::class, 'tenants']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/properties', PropertiesIndex::class)->name('properties.index');
    Route::get('/properties/{property}', PropertyShow::class)->name('properties.show');
    Route::get('/properties/{property}/floors', FloorsIndex::class)->name('properties.floors');
    Route::get('/properties/{property}/units', UnitsIndex::class)->name('properties.units');

    Route::get('/tenants', TenantsIndex::class)->name('tenants.index');
    Route::get('/tenants/{tenant}', TenantShow::class)->name('tenants.show');
    Route::get('/tenancies', TenancyIndex::class)->name('tenancies.index');

    Route::get('/meters', MetersIndex::class)->name('meters.index');
    Route::get('/meters/bulk-readings', BulkReadings::class)->name('meters.bulk-readings');
    Route::get('/electricity', ElectricityBillsIndex::class)->name('electricity.index');
    Route::get('/utilities', UtilityBillsIndex::class)->name('utilities.index');
    Route::get('/prepaid', PrepaidIndex::class)->name('prepaid.index');
    Route::get('/tariffs', TariffsIndex::class)->name('tariffs.index');

    Route::get('/bills', BillsIndex::class)->name('bills.index');
    Route::get('/payments', PaymentsIndex::class)->name('payments.index');
    Route::get('/expenses', ExpensesIndex::class)->name('expenses.index');
    Route::get('/vendors', VendorsIndex::class)->name('vendors.index');
    Route::get('/maintenance', MaintenanceIndex::class)->name('maintenance.index');

    Route::get('/reports', ReportsIndex::class)->name('reports.index');
    Route::get('/reports/pdf/{report}', [PdfController::class, 'report'])->name('reports.pdf');
    Route::get('/bills/{bill}/pdf', [PdfController::class, 'bill'])->name('bills.pdf');
    Route::get('/electricity/{bill}/pdf', [PdfController::class, 'electricity'])->name('electricity.pdf');
    Route::get('/payments/{payment}/receipt', [PdfController::class, 'receipt'])->name('payments.receipt');

    Route::get('/backups', BackupsIndex::class)->name('backups.index');
    Route::get('/audit', AuditIndex::class)->name('audit.index');

    Route::get('/settings', SettingsIndex::class)->name('settings.index');
    Route::get('/users', UsersIndex::class)->name('users.index');

    Route::get('/documents/download/{document}', [DocumentController::class, 'download'])->name('documents.download');
    Route::post('/documents/{document}/delete', [DocumentController::class, 'destroy'])->name('documents.destroy');

    Route::get('/whatsapp/send/{tenant}', [WhatsAppController::class, 'send'])->name('whatsapp.send');
    Route::get('/whatsapp/history', [WhatsAppController::class, 'history'])->name('whatsapp.history');

    Route::get('/import', [ImportExportController::class, 'importForm'])->name('import.form');
    Route::post('/import', [ImportExportController::class, 'import'])->name('import.run');
    Route::get('/export/{type}', [ImportExportController::class, 'export'])->name('export');
});

require __DIR__.'/auth.php';
