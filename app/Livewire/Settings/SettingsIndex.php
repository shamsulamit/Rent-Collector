<?php

namespace App\Livewire\Settings;

use App\Models\AccountingPeriod;
use App\Models\Setting;
use App\Models\User;
use App\Services\AuditService;
use Livewire\Component;

class SettingsIndex extends Component
{
    public array $recurring = [];
    public string $companyName = '';
    public string $whatsappLocale = 'en';

    public string $closeMonth = '';

    public string $gasRate = '0';
    public string $waterRate = '0';

    protected $rules = [
        'companyName' => 'required|string|max:255',
        'recurring.waste' => 'required|numeric|min:0',
        'recurring.security' => 'required|numeric|min:0',
        'recurring.cleaning' => 'required|numeric|min:0',
        'recurring.internet' => 'required|numeric|min:0',
        'recurring.parking' => 'required|numeric|min:0',
        'recurring.other' => 'required|numeric|min:0',
        'gasRate' => 'required|numeric|min:0',
        'waterRate' => 'required|numeric|min:0',
    ];

    public function mount(): void
    {
        $this->companyName = (string) Setting::get('company_name', config('landlord.name'));
        $this->whatsappLocale = (string) Setting::get('whatsapp_locale', 'en');

        $recurring = Setting::get('recurring_charges', '[]');
        $recurring = is_array($recurring) ? $recurring : json_decode((string) $recurring, true) ?? [];
        $this->recurring = array_merge([
            'waste' => 0, 'security' => 0, 'cleaning' => 0, 'internet' => 0, 'parking' => 0, 'other' => 0,
        ], $recurring);

        $this->gasRate = (string) Setting::get('gas_unit_rate', '0');
        $this->waterRate = (string) Setting::get('water_unit_rate', '0');

        $this->closeMonth = now()->format('Y-m');
    }

    public function save(): void
    {
        $this->validate();
        $this->authorize('manage settings');

        Setting::set('company_name', $this->companyName);
        Setting::set('whatsapp_locale', $this->whatsappLocale);
        Setting::set('recurring_charges', json_encode($this->recurring));
        Setting::set('gas_unit_rate', $this->gasRate);
        Setting::set('water_unit_rate', $this->waterRate);

        app(AuditService::class)->record('settings.updated', 'Setting', null, $this->recurring);
        session()->flash('message', 'Settings saved.');
    }

    public function closePeriod(): void
    {
        $this->authorize('close accounting periods');

        AccountingPeriod::updateOrCreate(['period' => $this->closeMonth], [
            'status' => 'closed',
            'closed_by' => auth()->id(),
            'closed_at' => now()->toDateString(),
        ]);

        app(AuditService::class)->record('period.closed', 'AccountingPeriod', $this->closeMonth);
        session()->flash('message', "Accounting period {$this->closeMonth} closed. Historical records locked.");
    }

    public function render()
    {
        return view('livewire.settings.index', [
            'periods' => AccountingPeriod::orderByDesc('period')->paginate(12),
            'roles' => User::ROLES,
        ])->layout('layouts.app');
    }
}
