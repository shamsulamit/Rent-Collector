<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'full_name', 'bangla_name', 'nid', 'passport', 'phone', 'whatsapp_number',
        'email', 'address', 'emergency_contact', 'photo_path', 'notes', 'is_deleted',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    public function tenancies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Tenancy::class);
    }

    public function activeTenancy(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Tenancy::class)->where('status', 'active')->latest();
    }

    public function leases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function bills(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function securityDeposits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SecurityDeposit::class);
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function timeline(): \Illuminate\Support\Collection
    {
        $events = collect();

        foreach ($this->tenancies()->with(['property', 'unit'])->get() as $tenancy) {
            $events->push([
                'date' => $tenancy->move_in_date,
                'type' => 'move_in',
                'title' => "Moved into {$tenancy->unit?->name}",
                'data' => $tenancy,
            ]);
            if ($tenancy->move_out_date) {
                $events->push([
                    'date' => $tenancy->move_out_date,
                    'type' => 'move_out',
                    'title' => "Moved out of {$tenancy->unit?->name}",
                    'data' => $tenancy,
                ]);
            }
        }

        foreach ($this->bills()->with(['unit', 'property'])->get() as $bill) {
            $events->push([
                'date' => $bill->billing_month,
                'type' => 'bill',
                'title' => "Bill {$bill->billing_month}: ".number_format((float) $bill->total, 2),
                'data' => $bill,
            ]);
        }

        foreach ($this->payments()->with(['unit', 'property'])->get() as $payment) {
            $events->push([
                'date' => $payment->payment_date,
                'type' => 'payment',
                'title' => 'Payment of '.number_format((float) $payment->amount, 2).' ('.$payment->method.')',
                'data' => $payment,
            ]);
        }

        foreach ($this->securityDeposits()->get() as $deposit) {
            $events->push([
                'date' => $deposit->date,
                'type' => 'deposit',
                'title' => ucfirst($deposit->type).' deposit '.number_format((float) $deposit->amount, 2),
                'data' => $deposit,
            ]);
        }

        return $events->sortBy('date')->values();
    }

    public function depositBalance(): float
    {
        return (float) $this->securityDeposits()
            ->whereIn('type', ['received', 'additional'])
            ->sum('amount')
            - (float) $this->securityDeposits()
                ->whereIn('type', ['deduction', 'refund'])
                ->sum('amount');
    }

    public function outstandingBalance(): float
    {
        $billed = (float) $this->bills()->where('status', '!=', 'draft')->sum('total');
        $paid = (float) $this->payments()->sum('amount');

        return round($billed - $paid, 2);
    }
}
