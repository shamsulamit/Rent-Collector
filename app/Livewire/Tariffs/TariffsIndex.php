<?php

namespace App\Livewire\Tariffs;

use App\Models\Tariff;
use App\Services\AuditService;
use Livewire\Component;
use Livewire\WithPagination;

class TariffsIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $utility = '';
    public bool $showForm = false;
    public ?string $editingId = null;

    public array $form = [
        'name' => '',
        'provider' => 'DESCO',
        'utility' => 'electricity',
        'meter_type' => 'postpaid',
        'effective_date' => '',
        'expiry_date' => '',
        'fixed_charge' => '0',
        'service_charge' => '0',
        'demand_charge' => '0',
        'vat_rate' => '0',
        'other_charge' => '0',
        'is_active' => true,
    ];

    /** @var array<int, array{min: string, max: string, rate: string}> */
    public array $slabs = [
        ['min' => '0', 'max' => '75', 'rate' => ''],
        ['min' => '75', 'max' => '200', 'rate' => ''],
        ['min' => '200', 'max' => '', 'rate' => ''],
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.utility' => 'required|in:electricity,gas,water',
        'form.meter_type' => 'required|in:postpaid,prepaid',
        'form.effective_date' => 'nullable|date',
        'form.vat_rate' => 'nullable|numeric|min:0',
    ];

    public function openCreate(): void
    {
        $this->reset('editingId', 'form', 'slabs');
        $this->form['effective_date'] = now()->toDateString();
        $this->form['is_active'] = true;
        $this->slabs = [
            ['min' => '0', 'max' => '75', 'rate' => ''],
            ['min' => '75', 'max' => '200', 'rate' => ''],
            ['min' => '200', 'max' => '', 'rate' => ''],
        ];
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $tariff = Tariff::findOrFail($id);
        $this->authorize('update', $tariff);
        $this->editingId = $id;
        $this->form = [
            'name' => $tariff->name,
            'provider' => $tariff->provider ?? '',
            'utility' => $tariff->utility,
            'meter_type' => $tariff->meter_type,
            'effective_date' => optional($tariff->effective_date)->toDateString(),
            'expiry_date' => optional($tariff->expiry_date)->toDateString(),
            'fixed_charge' => (string) $tariff->fixed_charge,
            'service_charge' => (string) $tariff->service_charge,
            'demand_charge' => (string) $tariff->demand_charge,
            'vat_rate' => (string) $tariff->vat_rate,
            'other_charge' => (string) $tariff->other_charge,
            'is_active' => (bool) $tariff->is_active,
        ];
        $this->slabs = collect($tariff->slabs ?: [])->map(fn ($s) => [
            'min' => (string) ($s['min'] ?? 0),
            'max' => isset($s['max']) ? (string) $s['max'] : '',
            'rate' => (string) ($s['rate'] ?? ''),
        ])->values()->all() ?: [['min' => '0', 'max' => '', 'rate' => '']];
        $this->showForm = true;
    }

    public function addSlab(): void
    {
        $this->slabs[] = ['min' => '', 'max' => '', 'rate' => ''];
    }

    public function removeSlab(int $index): void
    {
        unset($this->slabs[$index]);
        $this->slabs = array_values($this->slabs);
    }

    public function save(): void
    {
        $this->validate();

        $slabs = collect($this->slabs)
            ->filter(fn ($s) => $s['rate'] !== '' && is_numeric($s['rate']))
            ->map(fn ($s) => [
                'min' => (float) $s['min'],
                'max' => $s['max'] === '' ? null : (float) $s['max'],
                'rate' => (float) $s['rate'],
            ])
            ->values()
            ->all();

        $payload = $this->form + ['slabs' => $slabs];
        $payload['is_active'] = (bool) $this->form['is_active'];
        $payload['expiry_date'] = $payload['expiry_date'] ?: null;

        if ($this->editingId) {
            $tariff = Tariff::findOrFail($this->editingId);
            $this->authorize('update', $tariff);
            $tariff->update($payload);
            app(AuditService::class)->record('tariff.updated', 'Tariff', $tariff->id, $payload);
            session()->flash('message', 'Tariff updated. Historical bills keep their original rates.');
        } else {
            $this->authorize('create', Tariff::class);
            $tariff = Tariff::create($payload);
            app(AuditService::class)->record('tariff.created', 'Tariff', $tariff->id, $payload);
            session()->flash('message', 'Tariff created.');
        }

        $this->showForm = false;
        $this->reset('editingId');
    }

    public function delete(string $id): void
    {
        $tariff = Tariff::findOrFail($id);
        $this->authorize('delete', $tariff);
        app(AuditService::class)->record('tariff.deleted', 'Tariff', $tariff->id, null, $tariff->toArray());
        $tariff->delete();
        session()->flash('message', 'Tariff deleted.');
    }

    public function render()
    {
        $this->authorize('viewAny', Tariff::class);

        $tariffs = Tariff::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('provider', 'like', "%{$this->search}%"))
            ->when($this->utility, fn ($q) => $q->where('utility', $this->utility))
            ->orderByDesc('effective_date')
            ->paginate(12);

        return view('livewire.tariffs.index', [
            'tariffs' => $tariffs,
            'providers' => ['DESCO', 'DPDC', 'Palli Bidyut', 'NESCO', 'WZPDCL', 'BPDB', 'Custom'],
        ])->layout('layouts.app');
    }
}
