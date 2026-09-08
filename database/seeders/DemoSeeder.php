<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\ElectricityBill;
use App\Models\Expense;
use App\Models\Floor;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tariff;
use App\Models\Tenancy;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\Billing\BillingService;
use App\Services\Electricity\TariffService;
use App\Services\Payments\PaymentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'owner@landlord.test'],
            ['name' => 'Demo Owner', 'password' => Hash::make('password')]
        );
        $owner->syncRoles(['owner']);

        $manager = User::firstOrCreate(
            ['email' => 'manager@landlord.test'],
            ['name' => 'Demo Manager', 'password' => Hash::make('password')]
        );
        $manager->syncRoles(['manager']);

        $property = Property::firstOrCreate(['name' => 'Demo Residency'], [
            'address' => '12 Dhanmondi',
            'city' => 'Dhaka',
            'area' => 'Dhanmondi',
            'postal_code' => '1205',
            'contact_phone' => '+8801711000000',
            'status' => 'active',
        ]);

        $floorA = Floor::firstOrCreate(['property_id' => $property->id, 'name' => 'Floor 1']);
        $floorB = Floor::firstOrCreate(['property_id' => $property->id, 'name' => 'Floor 2']);

        $unitA = $this->makeUnit($property, $floorA, 'A-101', 'vacant');
        $unitB = $this->makeUnit($property, $floorB, 'B-202', 'vacant');

        $tariff = Tariff::updateOrCreate(['name' => 'DESCO Postpaid Residential'], [
            'provider' => 'DESCO',
            'utility' => 'electricity',
            'meter_type' => 'postpaid',
            'effective_date' => now()->subYear()->toDateString(),
            'slabs' => config('landlord.utilities.default_slabs'),
            'fixed_charge' => 200,
            'service_charge' => 0,
            'demand_charge' => 0,
            'vat_rate' => 5,
            'other_charge' => 0,
            'is_active' => true,
        ]);

        $tenants = [
            ['name' => 'Rahim Uddin', 'whatsapp' => '+8801711000001', 'unit' => $unitA, 'rent' => 15000],
            ['name' => 'Karim Ahmed', 'whatsapp' => '+8801711000002', 'unit' => $unitB, 'rent' => 17000],
        ];

        $meters = [];
        foreach ($tenants as $key => $t) {
            $tenant = Tenant::firstOrCreate(['full_name' => $t['name']], [
                'bangla_name' => $t['name'],
                'phone' => $t['whatsapp'],
                'whatsapp_number' => $t['whatsapp'],
                'nid' => 'NID'.str_pad((string) ($key + 1), 6, '0', STR_PAD_LEFT),
            ]);

            $tenancy = Tenancy::firstOrCreate(
                ['tenant_id' => $tenant->id, 'unit_id' => $t['unit']->id],
                [
                    'property_id' => $property->id,
                    'move_in_date' => now()->subMonths(2)->toDateString(),
                    'monthly_rent' => $t['rent'],
                    'deposit' => $t['rent'] * 3,
                    'status' => 'active',
                ]
            );

            $t['unit']->markOccupied($tenant->full_name);

            $meter = Meter::firstOrCreate(
                ['meter_number' => 'EL-'.str_pad((string) ($key + 100), 4, '0', STR_PAD_LEFT)],
                [
                    'property_id' => $property->id,
                    'floor_id' => $t['unit']->floor_id,
                    'unit_id' => $t['unit']->id,
                    'meter_type' => 'postpaid',
                    'utility' => 'electricity',
                    'provider' => 'DESCO',
                    'measurement_unit' => 'kWh',
                    'starting_reading' => 1250,
                    'installation_date' => now()->subYear()->toDateString(),
                    'status' => 'active',
                ]
            );

            $meters[$t['unit']->id] = $meter;

            MeterReading::firstOrCreate(
                ['meter_id' => $meter->id, 'billing_month' => now()->subMonth()->format('Y-m')],
                [
                    'property_id' => $property->id,
                    'unit_id' => $t['unit']->id,
                    'tenant_id' => $tenant->id,
                    'tenancy_id' => $tenancy->id,
                    'previous_reading' => 1250,
                    'current_reading' => 1410,
                    'usage' => 160,
                    'reading_date' => now()->subMonth()->toDateString(),
                    'status' => 'submitted',
                    'entered_by' => $owner->id,
                ]
            );
        }

        foreach ($meters as $meter) {
            app(TariffService::class)->calculate($meter, $meter->readings()->latest('billing_month')->first(), $tariff);
        }

        app(BillingService::class)->generateMonthlyBills(now()->subMonth()->format('Y-m'));

        $bill = Bill::where('billing_month', now()->subMonth()->format('Y-m'))->first();
        if ($bill) {
            app(BillingService::class)->finalize($bill);

            $payment = Payment::firstOrCreate(
                ['reference' => 'DEMO-PAY-1'],
                [
                    'tenant_id' => $bill->tenant_id,
                    'property_id' => $bill->property_id,
                    'unit_id' => $bill->unit_id,
                    'tenancy_id' => $bill->tenancy_id,
                    'amount' => 15000,
                    'payment_date' => now()->subDays(5)->toDateString(),
                    'method' => 'bkash',
                    'reference' => 'DEMO-PAY-1',
                    'recorded_by' => $owner->id,
                ]
            );
            app(PaymentService::class)->allocate($payment, 'oldest-first');
        }

        Expense::firstOrCreate(['description' => 'Lift maintenance'], [
            'property_id' => $property->id,
            'category' => 'maintenance',
            'amount' => 5000,
            'expense_date' => now()->subDays(10)->toDateString(),
            'payment_method' => 'cash',
            'created_by' => $owner->id,
        ]);
    }

    protected function makeUnit(Property $property, Floor $floor, string $name, string $status): Unit
    {
        return Unit::firstOrCreate(['property_id' => $property->id, 'name' => $name], [
            'floor_id' => $floor->id,
            'unit_type' => 'Apartment',
            'size' => 800,
            'bedrooms' => 2,
            'bathrooms' => 1,
            'monthly_rent' => $name === 'A-101' ? 15000 : 17000,
            'status' => $status,
        ]);
    }
}
