<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryTask extends Model
{
    use HasFactory;

    /**
     * حالات مهمة التوصيل.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * أنواع المهمة.
     */
    public const TYPE_PICKUP = 'pickup';
    public const TYPE_DELIVERY = 'delivery';
    public const TYPE_PICKUP_AND_DELIVERY = 'pickup_and_delivery';

    protected $fillable = [
        'order_id',
        'driver_id',
        'type',
        'status',
        'scheduled_at',
        'completed_at',
        'customer_fee',
        'driver_fee',
        'amount_collected',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'customer_fee' => 'decimal:2',
            'driver_fee' => 'decimal:2',
            'amount_collected' => 'decimal:2',
        ];
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function driverPayments(): HasMany
    {
        return $this->hasMany(DriverPayment::class);
    }
}
