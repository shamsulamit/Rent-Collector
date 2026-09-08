<?php

namespace Tests\Feature;

use App\Livewire\Bills\BillsIndex;
use App\Livewire\Meters\MetersIndex;
use App\Livewire\Payments\PaymentsIndex;
use App\Livewire\Properties\PropertiesIndex;
use App\Livewire\Tariffs\TariffsIndex;
use App\Livewire\Tenants\TenantsIndex;
use App\Livewire\Users\UsersIndex;
use App\Livewire\Vendors\VendorsIndex;
use App\Models\Bill;
use App\Models\Meter;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tariff;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OwnerCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function owner(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-crud@test.com',
            'password' => 'password',
        ]);
        $owner->assignRole('owner');

        return $owner;
    }

    public function test_owner_can_create_and_delete_meter(): void
    {
        $owner = $this->owner();
        $property = Property::create(['name' => 'P1', 'status' => 'active']);
        $unit = $property->units()->create(['name' => 'A-1', 'monthly_rent' => 10000, 'status' => 'vacant']);

        Livewire::actingAs($owner)
            ->test(MetersIndex::class)
            ->call('openCreate')
            ->set('form.property_id', $property->id)
            ->set('form.unit_id', $unit->id)
            ->set('form.meter_number', 'EL-NEW-1')
            ->set('form.utility', 'electricity')
            ->set('form.meter_type', 'postpaid')
            ->call('save')
            ->assertHasNoErrors();

        $meter = Meter::where('meter_number', 'EL-NEW-1')->first();
        $this->assertNotNull($meter);

        Livewire::actingAs($owner)
            ->test(MetersIndex::class)
            ->call('delete', $meter->id);

        $this->assertTrue($meter->fresh()->is_deleted);
    }

    public function test_owner_can_register_and_delete_user(): void
    {
        $owner = $this->owner();

        Livewire::actingAs($owner)
            ->test(UsersIndex::class)
            ->call('openCreate')
            ->set('form.name', 'Staff One')
            ->set('form.email', 'staff1@test.com')
            ->set('form.password', 'password123')
            ->set('form.role', 'staff')
            ->call('save')
            ->assertHasNoErrors();

        $staff = User::where('email', 'staff1@test.com')->first();
        $this->assertNotNull($staff);
        $this->assertTrue($staff->hasRole('staff'));

        Livewire::actingAs($owner)
            ->test(UsersIndex::class)
            ->call('delete', (string) $staff->id);

        $this->assertNull(User::find($staff->id));
    }

    public function test_owner_cannot_delete_self(): void
    {
        $owner = $this->owner();

        Livewire::actingAs($owner)
            ->test(UsersIndex::class)
            ->call('delete', (string) $owner->id);

        $this->assertNotNull(User::find($owner->id));
    }

    public function test_owner_can_edit_and_delete_property_and_tenant(): void
    {
        $owner = $this->owner();
        $property = Property::create(['name' => 'Old Name', 'status' => 'active']);
        $tenant = Tenant::create(['full_name' => 'Old Tenant']);

        Livewire::actingAs($owner)
            ->test(PropertiesIndex::class)
            ->call('openEdit', $property->id)
            ->set('form.name', 'New Name')
            ->call('save');

        $this->assertEquals('New Name', $property->fresh()->name);

        Livewire::actingAs($owner)
            ->test(PropertiesIndex::class)
            ->call('delete', $property->id);

        $this->assertTrue($property->fresh()->is_deleted);

        Livewire::actingAs($owner)
            ->test(TenantsIndex::class)
            ->call('openEdit', $tenant->id)
            ->set('form.full_name', 'New Tenant')
            ->call('save');

        $this->assertEquals('New Tenant', $tenant->fresh()->full_name);

        Livewire::actingAs($owner)
            ->test(TenantsIndex::class)
            ->call('delete', $tenant->id);

        $this->assertTrue($tenant->fresh()->is_deleted);
    }

    public function test_owner_can_delete_bill_and_payment(): void
    {
        $owner = $this->owner();
        $property = Property::create(['name' => 'P2', 'status' => 'active']);
        $unit = $property->units()->create(['name' => 'B-1', 'monthly_rent' => 12000, 'status' => 'occupied']);
        $tenant = Tenant::create(['full_name' => 'Pay Tenant']);
        $bill = Bill::create([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'bill_no' => '1',
            'billing_month' => now()->format('Y-m'),
            'rent' => 12000,
            'total' => 12000,
            'status' => 'finalized',
            'finalized_at' => now()->toDateString(),
        ]);
        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'amount' => 5000,
            'payment_date' => now()->toDateString(),
            'method' => 'cash',
        ]);

        Livewire::actingAs($owner)
            ->test(BillsIndex::class)
            ->call('delete', $bill->id);

        $this->assertNull(Bill::find($bill->id));

        Livewire::actingAs($owner)
            ->test(PaymentsIndex::class)
            ->call('delete', $payment->id);

        $this->assertNull(Payment::find($payment->id));
    }

    public function test_owner_can_manage_tariffs_and_vendors(): void
    {
        $owner = $this->owner();

        Livewire::actingAs($owner)
            ->test(TariffsIndex::class)
            ->call('openCreate')
            ->set('form.name', 'DESCO Test')
            ->set('form.utility', 'electricity')
            ->set('form.meter_type', 'postpaid')
            ->set('slabs.0.rate', '5.5')
            ->call('save')
            ->assertHasNoErrors();

        $tariff = Tariff::where('name', 'DESCO Test')->first();
        $this->assertNotNull($tariff);

        Livewire::actingAs($owner)
            ->test(TariffsIndex::class)
            ->call('delete', $tariff->id);

        $this->assertNull(Tariff::find($tariff->id));

        Livewire::actingAs($owner)
            ->test(VendorsIndex::class)
            ->call('openCreate')
            ->set('form.name', 'Plumber Co')
            ->call('save')
            ->assertHasNoErrors();

        $vendor = Vendor::where('name', 'Plumber Co')->first();
        $this->assertNotNull($vendor);

        Livewire::actingAs($owner)
            ->test(VendorsIndex::class)
            ->call('delete', $vendor->id);

        $this->assertTrue($vendor->fresh()->is_deleted);
    }
}
