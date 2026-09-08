<?php

namespace App\Livewire;

use App\Models\Bill;
use App\Models\Expense;
use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenancy;
use App\Models\Unit;
use App\Services\Reports\ReportService;
use Livewire\Component;

class Dashboard extends Component
{
    public ?string $propertyId = null;
    public string $month;

    public array $stats = [];
    public array $trend = [];
    public array $incomeExpense = [];
    public array $utilityCollections = [];
    public array $attention = [];
    public array $quickActions = [];

    protected $listeners = ['refreshDashboard' => '$refresh'];

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function updatedPropertyId(): void
    {
        $this->load();
    }

    public function updatedMonth(): void
    {
        $this->load();
    }

    public function load(): void
    {
        $report = app(ReportService::class);
        $this->stats = $report->dashboard($this->propertyId, $this->month);
        $this->trend = $report->collectionTrend(6, $this->propertyId);
        $this->incomeExpense = $report->incomeVsExpense(6, $this->propertyId);
        $this->utilityCollections = $report->utilityCollections($this->month, $this->propertyId);
        $this->attention = $this->attentionItems();
        $this->quickActions = $this->quickActionList();

        $this->dispatch('dashboard-charts',
            trend: $this->trend,
            income: $this->incomeExpense,
            utilities: $this->utilityCollections,
            occupancy: [
                'occupied' => $this->stats['occupied_units'] ?? 0,
                'vacant' => max(0, ($this->stats['total_units'] ?? 0) - ($this->stats['occupied_units'] ?? 0)),
            ],
            mix: [
                'rent' => $this->stats['expected_rent'] ?? 0,
                'utility' => $this->stats['utility_billed'] ?? 0,
                'expenses' => $this->stats['expenses'] ?? 0,
            ],
        );
    }

    protected function attentionItems(): array
    {
        $items = [];

        $overdue = Bill::query()
            ->where('billing_month', '<=', $this->month)
            ->whereIn('status', ['finalized', 'due', 'partial'])
            ->when($this->propertyId, fn ($q) => $q->where('property_id', $this->propertyId))
            ->withSum('allocations as allocated_sum', 'amount')
            ->get()
            ->filter(fn ($b) => $b->balance() > 0);

        if ($overdue->count()) {
            $items[] = [
                'icon' => 'alert',
                'title' => "{$overdue->count()} overdue bills",
                'detail' => '৳'.number_format($overdue->sum(fn ($b) => $b->balance()), 2).' outstanding',
                'route' => route('bills.index', ['status' => 'due']),
            ];
        }

        $missingReadings = MeterReading::where('billing_month', $this->month)
            ->where('status', 'draft')
            ->when($this->propertyId, fn ($q) => $q->where('property_id', $this->propertyId))
            ->count();

        if ($missingReadings) {
            $items[] = [
                'icon' => 'meter',
                'title' => "{$missingReadings} readings pending",
                'detail' => 'Missing or draft readings for '.$this->month,
                'route' => route('meters.bulk-readings', ['month' => $this->month]),
            ];
        }

        $expiring = Lease::whereBetween('end_date', [now()->toDateString(), now()->addDays(90)->toDateString()])
            ->where('status', 'active')
            ->count();

        if ($expiring) {
            $items[] = [
                'icon' => 'lease',
                'title' => "{$expiring} leases expiring soon",
                'detail' => 'Within the next 90 days',
                'route' => route('tenancies.index'),
            ];
        }

        $vacant = Unit::where('status', 'vacant')
            ->when($this->propertyId, fn ($q) => $q->where('property_id', $this->propertyId))
            ->count();

        if ($vacant) {
            $items[] = [
                'icon' => 'unit',
                'title' => "{$vacant} vacant units",
                'detail' => 'Potential lost rent',
                'route' => route('properties.units', $this->propertyId ?? ''),
            ];
        }

        $unfinalized = Bill::where('billing_month', $this->month)
            ->where('status', 'draft')
            ->when($this->propertyId, fn ($q) => $q->where('property_id', $this->propertyId))
            ->count();

        if ($unfinalized) {
            $items[] = [
                'icon' => 'bill',
                'title' => "{$unfinalized} unfinalized bills",
                'detail' => 'Bills still in draft for '.$this->month,
                'route' => route('bills.index', ['status' => 'draft']),
            ];
        }

        $openMaintenance = MaintenanceTicket::whereIn('status', ['open', 'in_progress'])
            ->when($this->propertyId, fn ($q) => $q->where('property_id', $this->propertyId))
            ->count();

        if ($openMaintenance) {
            $items[] = [
                'icon' => 'wrench',
                'title' => "{$openMaintenance} open maintenance tickets",
                'detail' => 'Require attention',
                'route' => route('maintenance.index'),
            ];
        }

        return $items;
    }

    protected function quickActionList(): array
    {
        return [
            ['label' => 'Add Tenant', 'icon' => 'user', 'route' => route('tenants.index')],
            ['label' => 'Record Payment', 'icon' => 'cash', 'route' => route('payments.index')],
            ['label' => 'Electricity Bill', 'icon' => 'doc', 'route' => route('electricity.index')],
            ['label' => 'Generate Bills', 'icon' => 'doc', 'route' => route('bills.index')],
            ['label' => 'Add Expense', 'icon' => 'receipt', 'route' => route('expenses.index')],
        ];
    }

    public function render()
    {
        if (empty($this->stats)) {
            $this->load();
        }

        return view('livewire.dashboard', [
            'properties' => Property::active()->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
