<?php

namespace App\Livewire\Properties;

use App\Models\Floor;
use App\Models\Property;
use App\Services\AuditService;
use Livewire\Component;

class FloorsIndex extends Component
{
    public Property $property;
    public bool $showForm = false;
    public ?string $editingId = null;
    public array $form = ['name' => '', 'description' => '', 'status' => 'active'];

    protected $rules = [
        'form.name' => 'required|string|max:255',
    ];

    public function mount(Property $property): void
    {
        $this->property = $property;
    }

    public function openCreate(): void
    {
        $this->reset('editingId', 'form');
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $floor = Floor::findOrFail($id);
        $this->editingId = $id;
        $this->form = $floor->only(['name', 'description', 'status']);
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();
        $this->authorize('update', $this->property);

        if ($this->editingId) {
            $floor = Floor::findOrFail($this->editingId);
            $floor->update($this->form);
            app(AuditService::class)->record('floor.updated', 'Floor', $floor->id, $this->form);
            session()->flash('message', 'Floor updated.');
        } else {
            $floor = $this->property->floors()->create($this->form);
            app(AuditService::class)->record('floor.created', 'Floor', $floor->id, $floor->toArray());
            session()->flash('message', 'Floor created.');
        }

        $this->showForm = false;
        $this->reset('editingId');
    }

    public function render()
    {
        return view('livewire.properties.floors', [
            'floors' => $this->property->floors()->withCount('units')->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
