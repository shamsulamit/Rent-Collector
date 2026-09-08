<?php

namespace App\Livewire\Prepaid;

use App\Models\Meter;
use App\Models\PrepaidRecharge;
use App\Services\AuditService;
use Livewire\Component;
use Livewire\WithPagination;

class PrepaidIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showForm = false;
    public ?string $editingId = null;
    public array $form = [
        'meter_id' => '',
        'recharge_date' => '',
        'units' => '',
        'unit_price' => '',
        'amount' => '',
        'reference' => '',
        'provider' => '',
        'notes' => '',
    ];

    protected $rules = [
        'form.meter_id' => 'required|exists:meters,id',
        'form.recharge_date' => 'required|date',
        'form.units' => 'nullable|numeric|min:0',
        'form.unit_price' => 'nullable|numeric|min:0',
        'form.amount' => 'required|numeric|min:0.01',
    ];

    public function mount(): void
    {
        $this->form['recharge_date'] = now()->toDateString();
    }

    public function openCreate(): void
    {
        $this->reset('editingId');
        $this->form['recharge_date'] = now()->toDateString();
        $this->form['amount'] = '';
        $this->form['units'] = '';
        $this->form['unit_price'] = '';
        $this->showForm = true;
    }

    public function updatedFormMeterId($value): void
    {
        $meter = Meter::find($value);
        if ($meter && $meter->unit_price !== null) {
            $this->form['unit_price'] = (string) $meter->unit_price;
            $this->syncAmount();
        }
    }

    public function updatedFormUnits(): void
    {
        $this->syncAmount();
    }

    public function updatedFormUnitPrice(): void
    {
        $this->syncAmount();
    }

    protected function syncAmount(): void
    {
        if (! is_numeric($this->form['units'] ?? '') || ! is_numeric($this->form['unit_price'] ?? '')) {
            return;
        }
        $this->form['amount'] = (string) round((float) $this->form['units'] * (float) $this->form['unit_price'], 2);
    }

    public function openEdit(string $id): void
    {
        $row = PrepaidRecharge::findOrFail($id);
        $this->editingId = $id;
        $this->form = $row->only(array_keys($this->form));
        $this->form['recharge_date'] = optional($row->recharge_date)->toDateString();
        $this->form['units'] = $row->units !== null ? (string) $row->units : '';
        $this->form['unit_price'] = $row->unit_price !== null ? (string) $row->unit_price : '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();
        $this->authorize('manage readings');

        $meter = Meter::findOrFail($this->form['meter_id']);
        $tenancy = $meter->unit?->activeTenancy;

        $previous = PrepaidRecharge::query()
            ->where('meter_id', $meter->id)
            ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
            ->orderByDesc('recharge_date')
            ->orderByDesc('created_at')
            ->value('balance_after');

        $previousUnits = PrepaidRecharge::query()
            ->where('meter_id', $meter->id)
            ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
            ->orderByDesc('recharge_date')
            ->orderByDesc('created_at')
            ->value('units_after');

        $units = (float) ($this->form['units'] ?: 0);
        $unitPrice = (float) ($this->form['unit_price'] ?: 0);

        $payload = $this->form + [
            'tenant_id' => $tenancy?->tenant_id,
            'unit_id' => $meter->unit_id,
            'provider' => $this->form['provider'] ?: $meter->provider,
            'units' => $units,
            'unit_price' => $unitPrice,
            'units_after' => round((float) ($previousUnits ?? 0) + $units, 2),
            'balance_after' => round((float) ($previous ?? 0) + (float) $this->form['amount'], 2),
            'entered_by' => auth()->id(),
        ];

        if ($this->editingId) {
            $row = PrepaidRecharge::findOrFail($this->editingId);
            $row->update($payload);
            app(AuditService::class)->record('prepaid.updated', 'PrepaidRecharge', $row->id, $payload);
            notify('Prepaid recharge updated.');
        } else {
            $row = PrepaidRecharge::create($payload);
            app(AuditService::class)->record('prepaid.created', 'PrepaidRecharge', $row->id, $payload);
            notify('Prepaid recharge added.');
        }

        $this->showForm = false;
        $this->reset('editingId');
    }

    public function delete(string $id): void
    {
        $row = PrepaidRecharge::findOrFail($id);
        $this->authorize('manage readings');
        app(AuditService::class)->record('prepaid.deleted', 'PrepaidRecharge', $row->id, null, $row->toArray());
        $row->delete();
        notify('Prepaid recharge deleted.');
    }

    public function render()
    {
        $recharges = PrepaidRecharge::query()
            ->when($this->search, fn ($q) => $q->where('reference', 'like', "%{$this->search}%")
                ->orWhereHas('meter', fn ($q) => $q->where('meter_number', 'like', "%{$this->search}%"))
                ->orWhereHas('tenant', fn ($q) => $q->where('full_name', 'like', "%{$this->search}%")))
            ->with(['meter', 'tenant', 'unit'])
            ->orderByDesc('recharge_date')
            ->paginate(12);

        return view('livewire.prepaid.index', [
            'recharges' => $recharges,
            'meters' => Meter::where('is_deleted', false)
                ->where('meter_type', 'prepaid')
                ->with(['unit', 'property'])
                ->orderBy('meter_number')
                ->get(),
        ])->layout('layouts.app');
    }
}
