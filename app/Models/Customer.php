<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'customer_type',
        'wilaya',
        'area',
        'address',
        'latitude',
        'longitude',
        'location_url',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * إجمالي المتبقي على العميل (يستبعد الطلبات الملغية).
     */
    public function totalDue(): float
    {
        return (float) $this->orders()->where('status', '!=', Order::STATUS_CANCELLED)->sum('due_amount');
    }

    /**
     * إجمالي ما دفعه العميل.
     */
    public function totalPaid(): float
    {
        return (float) $this->orders()->sum('paid_amount');
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
