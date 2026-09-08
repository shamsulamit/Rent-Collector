<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\UtilityType;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::updateOrCreate(['key' => 'recurring_charges'], [
            'value' => json_encode([
                'waste' => 200,
                'security' => 0,
                'cleaning' => 0,
                'internet' => 0,
                'parking' => 0,
                'other' => 0,
            ]),
        ]);

        Setting::updateOrCreate(['key' => 'whatsapp_locale'], ['value' => 'en']);
        Setting::updateOrCreate(['key' => 'company_name'], ['value' => 'Landlord Ledger']);
        Setting::updateOrCreate(['key' => 'gas_unit_rate'], ['value' => '12']);
        Setting::updateOrCreate(['key' => 'water_unit_rate'], ['value' => '8']);

        $utilities = [
            ['name' => 'Electricity', 'type' => 'metered', 'is_metered' => true],
            ['name' => 'Gas', 'type' => 'metered', 'is_metered' => true],
            ['name' => 'Water', 'type' => 'metered', 'is_metered' => true],
            ['name' => 'Waste', 'type' => 'fixed', 'is_metered' => false],
            ['name' => 'Internet', 'type' => 'recurring', 'is_metered' => false],
            ['name' => 'Cleaning', 'type' => 'recurring', 'is_metered' => false],
            ['name' => 'Parking', 'type' => 'recurring', 'is_metered' => false],
        ];

        foreach ($utilities as $utility) {
            UtilityType::updateOrCreate(['name' => $utility['name']], $utility);
        }
    }
}
