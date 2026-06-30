<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    /**
     * أنواع محاسبة السائق.
     */
    public const PAYMENT_PER_TASK = 'per_task';
    public const PAYMENT_PER_DAY = 'per_day';

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'payment_type',
        'default_delivery_fee',
        'license_number',
        'vehicle_info',
        'commission_type',
        'commission_value',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'commission_value' => 'decimal:2',
            'default_delivery_fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryTasks(): HasMany
    {
        return $this->hasMany(DeliveryTask::class);
    }

    public function driverPayments(): HasMany
    {
        return $this->hasMany(DriverPayment::class);
    }

    /**
     * عدد المهام المكتملة.
     */
    public function completedTasksCount(): int
    {
        return $this->deliveryTasks()->where('status', DeliveryTask::STATUS_COMPLETED)->count();
    }

    /**
     * إجمالي مستحقات المهام المكتملة (تُستبعد الملغية والفاشلة).
     */
    public function totalDue(): float
    {
        return (float) $this->deliveryTasks()
            ->where('status', DeliveryTask::STATUS_COMPLETED)
            ->sum('driver_fee');
    }

    /**
     * إجمالي ما دُفع للسائق.
     */
    public function totalPaid(): float
    {
        return (float) $this->driverPayments()->sum('amount');
    }

    /**
     * المتبقي للسائق.
     */
    public function remainingDue(): float
    {
        return round($this->totalDue() - $this->totalPaid(), 2);
    }
}
