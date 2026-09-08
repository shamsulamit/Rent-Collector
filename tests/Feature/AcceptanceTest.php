<?php

namespace Tests\Feature;

use App\Models\ElectricityBill;
use App\Models\Floor;
use App\Models\GasBill;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\Property;
use App\Models\RentChange;
use App\Models\Setting;
use App\Models\Tariff;
use App\Models\Tenancy;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Models\WaterBill;
use App\Services\Billing\BillingService;
use App\Services\Electricity\TariffService;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Acceptance tests matching README section 56.
 */
class AcceptanceTest extends TestCase
{
    use RefreshDatabase;

    protected function makeSetup(string $rent = '15000'): array
    {
        $property = Property::create([
            'name' => 'Test Property',
            'address' => '12 Dhanmondi',
            'city' => 'Dhaka',
            'status' => 'active',
        ]);
        $floor = Floor::create(['property_id' => $property->id, 'name' => 'Floor 1']);
        $unit = Unit::create([
            'property_id' => $property->id,
            'floor_id' => $floor->id,
            'name' => 'A-101',
            'monthly_rent' => $rent,
            'status' => 'occupied',
        ]);
        $tenant = Tenant::create([
            'full_name' => 'Rahim Uddin',
            'phone' => '+8801711000001',
            'whatsapp_number' => '+8801711000001',
        ]);
        $tenancy = Tenancy::create([
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'move_in_date' => now()->subMonths(3)->toDateString(),
            'monthly_rent' => $rent,
            'deposit' => 30000,
            'status' => 'active',
        ]);
        $meter = Meter::create([
            'property_id' => $property->id,
            'floor_id' => $floor->id,
            'unit_id' => $unit->id,
            'meter_number' => 'EL-100',
            'meter_type' => 'postpaid',
            'utility' => 'electricity',
            'provider' => 'DESCO',
            'unit' => 'kWh',
            'starting_reading' => 1250,
            'status' => 'active',
        ]);

        return compact('property', 'floor', 'unit', 'tenant', 'tenancy', 'meter');
    }

    public function test_1_normal_monthly_bill_totals_18458(): void
    {
        $s = $this->makeSetup();
        $month = now()->format('Y-m');

        Setting::set('recurring_charges', json_encode(['waste' => 200]));

        ElectricityBill::create([
            'property_id' => $s['property']->id,
            'unit_id' => $s['unit']->id,
            'tenant_id' => $s['tenant']->id,
            'tenancy_id' => $s['tenancy']->id,
            'meter_id' => $s['meter']->id,
            'billing_month' => $month,
            'previous_reading' => 1250,
            'current_reading' => 1410,
            'usage' => 160,
            'total' => 1678,
            'status' => 'finalized',
        ]);
        GasBill::create([
            'property_id' => $s['property']->id,
            'unit_id' => $s['unit']->id,
            'tenant_id' => $s['tenant']->id,
            'tenancy_id' => $s['tenancy']->id,
            'billing_month' => $month,
            'charge' => 1080,
            'status' => 'finalized',
        ]);
        WaterBill::create([
            'property_id' => $s['property']->id,
            'unit_id' => $s['unit']->id,
            'tenant_id' => $s['tenant']->id,
            'tenancy_id' => $s['tenancy']->id,
            'billing_month' => $month,
            'charge' => 500,
            'status' => 'finalized',
        ]);

        app(BillingService::class)->generateMonthlyBills($month);

        $bill = $s['tenant']->bills()->where('billing_month', $month)->first();
        $this->assertNotNull($bill);
        $this->assertEquals(15000.0, (float) $bill->rent);
        $this->assertEquals(1678.0, (float) $bill->electricity);
        $this->assertEquals(1080.0, (float) $bill->gas);
        $this->assertEquals(500.0, (float) $bill->water);
        $this->assertEquals(200.0, (float) $bill->waste);
        $this->assertEquals(18458.0, (float) $bill->total);
        $this->assertCount(5, $bill->items);
    }

    public function test_2_partial_payment_leaves_3458_due(): void
    {
        $s = $this->makeSetup();
        $month = now()->format('Y-m');

        $bill = $s['tenant']->bills()->create([
            'property_id' => $s['property']->id,
            'unit_id' => $s['unit']->id,
            'tenancy_id' => $s['tenancy']->id,
            'bill_no' => 'T-1',
            'billing_month' => $month,
            'rent' => 15000,
            'electricity' => 1678,
            'gas' => 1080,
            'water' => 500,
            'waste' => 200,
            'total' => 18458,
            'status' => 'finalized',
        ]);

        $payment = Payment::create([
            'tenant_id' => $s['tenant']->id,
            'tenancy_id' => $s['tenancy']->id,
            'property_id' => $s['property']->id,
            'unit_id' => $s['unit']->id,
            'amount' => 15000,
            'payment_date' => now()->toDateString(),
            'method' => 'bkash',
            'reference' => 'PAY-1',
        ]);

        app(PaymentService::class)->allocate($payment, 'oldest-first');

        $this->assertEquals(15000.0, (float) $bill->fresh()->totalPaid());
        $this->assertEquals(3458.0, $bill->fresh()->balance());
        $this->assertEquals('partial', $bill->fresh()->status);
    }

    public function test_3_overpayment_creates_1542_credit(): void
    {
        $s = $this->makeSetup();
        $month = now()->format('Y-m');

        $bill = $s['tenant']->bills()->create([
            'property_id' => $s['property']->id,
            'unit_id' => $s['unit']->id,
            'tenancy_id' => $s['tenancy']->id,
            'bill_no' => 'T-1',
            'billing_month' => $month,
            'rent' => 15000,
            'electricity' => 1678,
            'gas' => 1080,
            'water' => 500,
            'waste' => 200,
            'total' => 18458,
            'status' => 'finalized',
        ]);

        $payment = Payment::create([
            'tenant_id' => $s['tenant']->id,
            'tenancy_id' => $s['tenancy']->id,
            'property_id' => $s['property']->id,
            'unit_id' => $s['unit']->id,
            'amount' => 20000,
            'payment_date' => now()->toDateString(),
            'method' => 'bank',
            'reference' => 'PAY-2',
        ]);

        app(PaymentService::class)->allocate($payment, 'oldest-first');

        $this->assertEquals('paid', $bill->fresh()->status);
        $this->assertEquals(1542.0, $payment->unallocated());
        $this->assertEquals(1542.0, app(PaymentService::class)->tenantCredit($s['tenant']));
    }

    public function test_4_tenant_transfer_preserves_history(): void
    {
        $s = $this->makeSetup();
        $floorB = Floor::create(['property_id' => $s['property']->id, 'name' => 'Floor 2']);
        $unitB = Unit::create([
            'property_id' => $s['property']->id,
            'floor_id' => $floorB->id,
            'name' => 'B-202',
            'monthly_rent' => 17000,
            'status' => 'occupied',
        ]);

        $s['tenancy']->update([
            'status' => 'ended',
            'move_out_date' => now()->subMonth()->toDateString(),
        ]);

        Tenancy::create([
            'tenant_id' => $s['tenant']->id,
            'property_id' => $s['property']->id,
            'unit_id' => $unitB->id,
            'move_in_date' => now()->subMonth()->toDateString(),
            'monthly_rent' => 17000,
            'status' => 'active',
        ]);

        $timeline = $s['tenant']->timeline();
        $moveIns = $timeline->where('type', 'move_in');

        $this->assertCount(2, $moveIns);
        $this->assertEquals('A-101', $s['tenant']->tenancies()->oldest('move_in_date')->first()->unit->name);
        $this->assertEquals($unitB->id, $s['tenant']->activeTenancy->unit_id);
    }

    public function test_5_rent_increase_keeps_history(): void
    {
        $s = $this->makeSetup();
        $month = now()->format('Y-m');
        $service = app(BillingService::class);

        $oldBill = $s['tenant']->bills()->create([
            'property_id' => $s['property']->id,
            'unit_id' => $s['unit']->id,
            'tenancy_id' => $s['tenancy']->id,
            'bill_no' => 'T-1',
            'billing_month' => now()->subMonth()->format('Y-m'),
            'rent' => 15000,
            'total' => 15000,
            'status' => 'finalized',
        ]);

        RentChange::create([
            'tenancy_id' => $s['tenancy']->id,
            'effective_date' => now()->startOfMonth()->toDateString(),
            'old_rent' => 15000,
            'new_rent' => 17000,
        ]);

        $this->assertEquals(15000.0, (float) $oldBill->fresh()->rent);
        $this->assertEquals(15000.0, (float) $service->computeRent(
            $s['tenancy'],
            \Carbon\Carbon::createFromFormat('Y-m-d', now()->subMonth()->startOfMonth()->toDateString()),
            30
        ));
        $this->assertEquals(17000.0, (float) $service->computeRent(
            $s['tenancy'],
            \Carbon\Carbon::createFromFormat('Y-m-d', now()->startOfMonth()->toDateString()),
            now()->daysInMonth
        ));
    }

    public function test_6_and_7_meter_carryover_and_postpaid_electricity(): void
    {
        $s = $this->makeSetup();
        $july = now()->subMonth()->format('Y-m');
        $august = now()->format('Y-m');

        $julyReading = MeterReading::create([
            'meter_id' => $s['meter']->id,
            'property_id' => $s['property']->id,
            'unit_id' => $s['unit']->id,
            'tenant_id' => $s['tenant']->id,
            'tenancy_id' => $s['tenancy']->id,
            'billing_month' => $july,
            'previous_reading' => 1250,
            'current_reading' => 1410,
            'usage' => 160,
            'status' => 'submitted',
        ]);

        $tariff = Tariff::create([
            'name' => 'Test Postpaid',
            'provider' => 'DESCO',
            'utility' => 'electricity',
            'meter_type' => 'postpaid',
            'effective_date' => now()->subYear()->toDateString(),
            'slabs' => [
                ['min' => 0, 'max' => 75, 'rate' => 0],
                ['min' => 76, 'max' => 200, 'rate' => 6.50],
                ['min' => 201, 'max' => null, 'rate' => 7.80],
            ],
            'fixed_charge' => 200,
            'vat_rate' => 5,
        ]);

        $elecBill = app(TariffService::class)->calculate($s['meter'], $julyReading, $tariff);
        $this->assertEquals(160.0, (float) $elecBill->usage);
        $this->assertEquals(552.5, (float) $elecBill->energy_charge);

        $this->assertEquals(1410, (int) $s['meter']->lastReading()->current_reading);

        $augReading = MeterReading::create([
            'meter_id' => $s['meter']->id,
            'property_id' => $s['property']->id,
            'unit_id' => $s['unit']->id,
            'tenant_id' => $s['tenant']->id,
            'tenancy_id' => $s['tenancy']->id,
            'billing_month' => $august,
            'previous_reading' => $s['meter']->lastReading()->current_reading,
            'current_reading' => 1610,
            'usage' => 200,
            'status' => 'submitted',
        ]);

        $this->assertEquals(1410, (int) $augReading->previous_reading);
        $this->assertEquals(200.0, (float) $augReading->usage);
    }

    public function test_9_tariff_change_selects_effective_tariff(): void
    {
        $s = $this->makeSetup();
        $july = now()->subMonth()->format('Y-m');
        $august = now()->format('Y-m');

        $oldTariff = Tariff::create([
            'name' => 'Old Rate',
            'provider' => 'DESCO',
            'utility' => 'electricity',
            'meter_type' => 'postpaid',
            'effective_date' => now()->subYear()->toDateString(),
            'expiry_date' => now()->startOfMonth()->subDay()->toDateString(),
            'slabs' => [['min' => 0, 'max' => null, 'rate' => 5.00]],
            'fixed_charge' => 100,
            'vat_rate' => 5,
        ]);
        $newTariff = Tariff::create([
            'name' => 'New Rate',
            'provider' => 'DESCO',
            'utility' => 'electricity',
            'meter_type' => 'postpaid',
            'effective_date' => now()->startOfMonth()->toDateString(),
            'slabs' => [['min' => 0, 'max' => null, 'rate' => 8.00]],
            'fixed_charge' => 200,
            'vat_rate' => 5,
        ]);

        $julyReading = MeterReading::create([
            'meter_id' => $s['meter']->id,
            'property_id' => $s['property']->id,
            'unit_id' => $s['unit']->id,
            'tenant_id' => $s['tenant']->id,
            'tenancy_id' => $s['tenancy']->id,
            'billing_month' => $july,
            'previous_reading' => 1000,
            'current_reading' => 1200,
            'usage' => 200,
            'reading_date' => now()->subMonth()->toDateString(),
            'status' => 'submitted',
        ]);
        $augReading = MeterReading::create([
            'meter_id' => $s['meter']->id,
            'property_id' => $s['property']->id,
            'unit_id' => $s['unit']->id,
            'tenant_id' => $s['tenant']->id,
            'tenancy_id' => $s['tenancy']->id,
            'billing_month' => $august,
            'previous_reading' => 1200,
            'current_reading' => 1400,
            'usage' => 200,
            'reading_date' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        $julyBill = app(TariffService::class)->calculate($s['meter'], $julyReading);
        $augBill = app(TariffService::class)->calculate($s['meter'], $augReading);

        $this->assertEquals($oldTariff->id, $julyBill->tariff_id);
        $this->assertEquals($newTariff->id, $augBill->tariff_id);
        $this->assertLessThan((float) $augBill->total, (float) $julyBill->total);
    }

    public function test_10_calculated_electricity_bill_can_be_deleted(): void
    {
        $s = $this->makeSetup();
        $july = now()->subMonth()->format('Y-m');

        $julyReading = MeterReading::create([
            'meter_id' => $s['meter']->id,
            'property_id' => $s['property']->id,
            'unit_id' => $s['unit']->id,
            'tenant_id' => $s['tenant']->id,
            'tenancy_id' => $s['tenancy']->id,
            'billing_month' => $july,
            'previous_reading' => 1250,
            'current_reading' => 1410,
            'usage' => 160,
            'status' => 'submitted',
        ]);

        $tariff = Tariff::create([
            'name' => 'Delete Test Tariff',
            'provider' => 'DESCO',
            'utility' => 'electricity',
            'meter_type' => 'postpaid',
            'effective_date' => now()->subYear()->toDateString(),
            'slabs' => [['min' => 0, 'max' => null, 'rate' => 6.50]],
            'fixed_charge' => 200,
            'vat_rate' => 5,
        ]);

        $bill = app(TariffService::class)->calculate($s['meter'], $julyReading, $tariff);
        $this->assertEquals('calculated', $bill->status);

        $bill->delete();

        $this->assertNull(ElectricityBill::find($bill->id));
        $this->assertDatabaseMissing('electricity_bills', ['id' => $bill->id]);
    }

    public function test_11_finalized_electricity_bill_cannot_be_deleted(): void
    {
        $s = $this->makeSetup();
        $july = now()->subMonth()->format('Y-m');

        $julyReading = MeterReading::create([
            'meter_id' => $s['meter']->id,
            'property_id' => $s['property']->id,
            'unit_id' => $s['unit']->id,
            'tenant_id' => $s['tenant']->id,
            'tenancy_id' => $s['tenancy']->id,
            'billing_month' => $july,
            'previous_reading' => 1250,
            'current_reading' => 1410,
            'usage' => 160,
            'status' => 'submitted',
        ]);

        $tariff = Tariff::create([
            'name' => 'Finalize Test Tariff',
            'provider' => 'DESCO',
            'utility' => 'electricity',
            'meter_type' => 'postpaid',
            'effective_date' => now()->subYear()->toDateString(),
            'slabs' => [['min' => 0, 'max' => null, 'rate' => 6.50]],
            'fixed_charge' => 200,
            'vat_rate' => 5,
        ]);

        $bill = app(TariffService::class)->calculate($s['meter'], $julyReading, $tariff);
        app(TariffService::class)->finalize($bill);

        $this->assertTrue($bill->isImmutable());
        $this->expectException(\DomainException::class);
        $bill->delete();
    }

    public function test_12_owner_can_delete_calculated_electricity_bill_via_ui(): void
    {
        $s = $this->makeSetup();
        $month = now()->format('Y-m');

        $reading = MeterReading::create([
            'meter_id' => $s['meter']->id,
            'property_id' => $s['property']->id,
            'unit_id' => $s['unit']->id,
            'tenant_id' => $s['tenant']->id,
            'tenancy_id' => $s['tenancy']->id,
            'billing_month' => $month,
            'previous_reading' => 1250,
            'current_reading' => 1410,
            'usage' => 160,
            'status' => 'submitted',
        ]);

        $tariff = Tariff::create([
            'name' => 'UI Delete Tariff',
            'provider' => 'DESCO',
            'utility' => 'electricity',
            'meter_type' => 'postpaid',
            'effective_date' => now()->subYear()->toDateString(),
            'slabs' => [['min' => 0, 'max' => null, 'rate' => 6.50]],
            'fixed_charge' => 200,
            'vat_rate' => 5,
        ]);

        $bill = app(TariffService::class)->calculate($s['meter'], $reading, $tariff);

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-delete@test.com',
            'password' => bcrypt('password'),
        ]);
        $owner->assignRole(Role::firstOrCreate(['name' => 'owner']));

        Livewire::actingAs($owner)
            ->test(\App\Livewire\Meters\BulkReadings::class)
            ->set('month', $month)
            ->call('deleteElectricityBill', $s['meter']->id);

        $this->assertNull(ElectricityBill::find($bill->id));
    }

    public function test_13_owner_can_delete_finalized_electricity_bill(): void
    {
        $s = $this->makeSetup();
        $month = now()->format('Y-m');

        $reading = MeterReading::create([
            'meter_id' => $s['meter']->id,
            'property_id' => $s['property']->id,
            'unit_id' => $s['unit']->id,
            'tenant_id' => $s['tenant']->id,
            'tenancy_id' => $s['tenancy']->id,
            'billing_month' => $month,
            'previous_reading' => 1250,
            'current_reading' => 1410,
            'usage' => 160,
            'status' => 'submitted',
        ]);

        $tariff = Tariff::create([
            'name' => 'UI Owner Delete Tariff',
            'provider' => 'DESCO',
            'utility' => 'electricity',
            'meter_type' => 'postpaid',
            'effective_date' => now()->subYear()->toDateString(),
            'slabs' => [['min' => 0, 'max' => null, 'rate' => 6.50]],
            'fixed_charge' => 200,
            'vat_rate' => 5,
        ]);

        $bill = app(TariffService::class)->calculate($s['meter'], $reading, $tariff);
        app(TariffService::class)->finalize($bill);

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-blocked@test.com',
            'password' => bcrypt('password'),
        ]);
        $owner->assignRole(Role::firstOrCreate(['name' => 'owner']));

        Livewire::actingAs($owner)
            ->test(\App\Livewire\Meters\BulkReadings::class)
            ->set('month', $month)
            ->call('deleteElectricityBill', $s['meter']->id);

        $this->assertNull(ElectricityBill::find($bill->id));
    }
}
