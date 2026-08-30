<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use App\Models\Property;
use App\Models\Vendor;
use App\Services\AuditService;
use Livewire\Component;
use Livewire\WithPagination;

class ExpensesIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $propertyId = null;
    public string $category = '';
    public string $from = '';
    public string $to = '';

    public bool $showForm = false;
    public ?string $editingId = null;
    public array $form = [
        'property_id' => '',
        'unit_id' => '',
        'vendor_id' => '',
        'category' => 'maintenance',
        'amount' => '',
        'expense_date' => '',
        'payment_method' => 'cash',
        'description' => '',
        'notes' => '',
    ];

    protected $rules = [
        'form.category' => 'required|string',
        'form.amount' => 'required|numeric|min:0.01',
        'form.expense_date' => 'required|date',
        'form.property_id' => 'nullable|exists:properties,id',
    ];

    public function mount(): void
    {
        $this->form['expense_date'] = now()->toDateString();
    }

    public function openCreate(): void
    {
        $this->reset('editingId');
        $this->form['expense_date'] = now()->toDateString();
        $this->showForm = true;
    }

    public function openEdit(string $id): void
    {
        $expense = Expense::findOrFail($id);
        $this->editingId = $id;
        $this->form = $expense->only(array_keys($this->form));
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $expense = Expense::findOrFail($this->editingId);
            $this->authorize('update', $expense);
            $expense->update($this->form);
            app(AuditService::class)->record('expense.updated', 'Expense', $expense->id, $this->form);
            session()->flash('message', 'Expense updated.');
        } else {
            $this->authorize('create', Expense::class);
            $expense = Expense::create($this->form + ['created_by' => auth()->id()]);
            app(AuditService::class)->record('expense.created', 'Expense', $expense->id, $expense->toArray());
            session()->flash('message', 'Expense recorded.');
        }

        $this->showForm = false;
        $this->reset('editingId');
    }

    public function render()
    {
        $expenses = Expense::query()
            ->when($this->search, fn ($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->propertyId, fn ($q) => $q->where('property_id', $this->propertyId))
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->when($this->from, fn ($q) => $q->whereDate('expense_date', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('expense_date', '<=', $this->to))
            ->with(['property', 'unit', 'vendor'])
            ->orderByDesc('expense_date')
            ->paginate(12);

        $total = (clone $expenses)->getCollection()->sum('amount');

        return view('livewire.expenses.index', [
            'expenses' => $expenses,
            'total' => $total,
            'properties' => Property::active()->orderBy('name')->get(),
            'vendors' => Vendor::where('is_deleted', false)->orderBy('name')->get(),
            'categories' => Expense::CATEGORIES,
        ])->layout('layouts.app');
    }
}
