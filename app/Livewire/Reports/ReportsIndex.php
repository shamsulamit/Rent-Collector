<?php

namespace App\Livewire\Reports;

use App\Models\Expense;
use App\Models\Property;
use App\Services\Reports\ReportService;
use Livewire\Component;

class ReportsIndex extends Component
{
    public ?string $propertyId = null;
    public string $from;
    public string $to;

    public array $totals = ['billed' => 0, 'collected' => 0, 'expenses' => 0, 'net' => 0];
    public array $propertyRows = [];
    public array $paymentMethods = [];
    public array $expenseBreakdown = [];

    protected $listeners = ['refreshReports' => '$refresh'];

    public function mount(): void
    {
        $this->from = now()->startOfYear()->toDateString();
        $this->to = now()->toDateString();
    }

    public function updatedPropertyId(): void
    {
        $this->load();
    }

    public function updatedFrom(): void
    {
        $this->load();
    }

    public function updatedTo(): void
    {
        $this->load();
    }

    public function load(): void
    {
        $report = app(ReportService::class);
        $rows = $report->propertyReport($this->propertyId, $this->from, $this->to);

        $this->propertyRows = $rows->map(fn ($row) => [
            'name' => $row['property']->name,
            'billed' => $row['billed'],
            'collected' => $row['collected'],
            'expenses' => $row['expenses'],
            'net' => $row['net'],
            'occupancy' => $row['occupancy'],
        ])->values()->toArray();

        $this->totals = [
            'billed' => round((float) $rows->sum('billed'), 2),
            'collected' => round((float) $rows->sum('collected'), 2),
            'expenses' => round((float) $rows->sum('expenses'), 2),
            'net' => round((float) $rows->sum('net'), 2),
        ];

        $this->paymentMethods = $report->paymentMethods($this->from, $this->to);

        $this->expenseBreakdown = Expense::query()
            ->when($this->propertyId, fn ($q) => $q->where('property_id', $this->propertyId))
            ->whereBetween('expense_date', [$this->from, $this->to])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->pluck('total', 'category')
            ->map(fn ($v) => round((float) $v, 2))
            ->toArray();
    }

    public function render()
    {
        if (empty($this->propertyRows)) {
            $this->load();
        }

        return view('livewire.reports.index', [
            'properties' => Property::active()->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
