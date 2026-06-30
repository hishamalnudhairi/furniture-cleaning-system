<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * حالات الطلب الرسمي.
     */
    public const STATUS_NEW = 'new';
    public const STATUS_CLEANING = 'cleaning';
    public const STATUS_READY = 'ready';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * حالات السداد.
     */
    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PARTIAL = 'partial';
    public const PAYMENT_PAID = 'paid';

    protected $fillable = [
        'order_number',
        'customer_id',
        'service_request_id',
        'accountant_id',
        'status',
        'latitude',
        'longitude',
        'location_url',
        'subtotal',
        'discount',
        'tax_percentage',
        'tax_amount',
        'total',
        'paid_amount',
        'due_amount',
        'payment_status',
        'notes',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
            'delivered_at' => 'datetime',
        ];
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function hasBalance(): bool
    {
        return (float) $this->due_amount > 0;
    }

    /**
     * يعيد حساب القيم المالية للطلب:
     * - الإجمالي = الفرعي − الخصم + الضريبة
     * - المدفوع = مجموع المدفوعات المسجّلة
     * - المتبقي = الإجمالي − المدفوع
     * - حالة الدفع تُشتق تلقائيًا
     */
    public function recalcTotals(): void
    {
        $this->total = round((float) $this->subtotal - (float) $this->discount + (float) $this->tax_amount, 2);
        $this->paid_amount = round((float) $this->payments()->sum('amount'), 2);
        $this->due_amount = round($this->total - $this->paid_amount, 2);

        if ($this->paid_amount <= 0) {
            $this->payment_status = self::PAYMENT_UNPAID;
        } elseif ($this->paid_amount >= $this->total) {
            $this->payment_status = self::PAYMENT_PAID;
        } else {
            $this->payment_status = self::PAYMENT_PARTIAL;
        }
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function accountant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accountant_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function deliveryTasks(): HasMany
    {
        return $this->hasMany(DeliveryTask::class);
    }
}
