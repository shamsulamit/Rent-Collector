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
        'amount' => '',
        'reference' => '',
        'provider' => '',
        'notes' => '',
    ];

    protected $rules = [
        'form.meter_id' => 'required|exists:meters,id',
        'form.recharge_date' => 'required|date',
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
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $row = PrepaidRecharge::findOrFail($id);
        $this->editingId = $id;
        $this->form = $row->only(array_keys($this->form));
        $this->form['recharge_date'] = optional($row->recharge_date)->toDateString();
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

        $payload = $this->form + [
            'tenant_id' => $tenancy?->tenant_id,
            'unit_id' => $meter->unit_id,
            'provider' => $this->form['provider'] ?: $meter->provider,
            'balance_after' => round((float) ($previous ?? 0) + (float) $this->form['amount'], 2),
            'entered_by' => auth()->id(),
        ];

        if ($this->editingId) {
            $row = PrepaidRecharge::findOrFail($this->editingId);
            $row->update($payload);
            app(AuditService::class)->record('prepaid.updated', 'PrepaidRecharge', $row->id, $payload);
            session()->flash('message', 'Prepaid recharge updated.');
        } else {
            $row = PrepaidRecharge::create($payload);
            app(AuditService::class)->record('prepaid.created', 'PrepaidRecharge', $row->id, $payload);
            session()->flash('message', 'Prepaid recharge recorded. No postpaid invoice was generated.');
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
        session()->flash('message', 'Prepaid recharge deleted.');
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
                ->with('unit')
                ->orderBy('meter_number')
                ->get(),
        ])->layout('layouts.app');
    }
}
