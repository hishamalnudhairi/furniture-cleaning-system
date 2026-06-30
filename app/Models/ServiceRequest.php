<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceRequest extends Model
{
    use HasFactory;

    /**
     * حالات الطلب الخارجي.
     */
    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'request_number',
        'customer_id',
        'service_id',
        'services_json',
        'customer_name',
        'customer_phone',
        'wilaya',
        'area',
        'address',
        'customer_type',
        'latitude',
        'longitude',
        'location_url',
        'location_notes',
        'service_method',
        'preferred_date',
        'preferred_period',
        'description',
        'notes',
        'status',
        'converted_order_id',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'services_json' => 'array',
            'preferred_date' => 'date',
        ];
    }

    /**
     * هل تم تحويل الطلب إلى طلب رسمي؟
     */
    public function isConverted(): bool
    {
        return ! is_null($this->converted_order_id);
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * هل يمكن تحويل الطلب إلى طلب رسمي؟
     * (غير ملغي وغير مُحوّل مسبقًا)
     */
    public function canBeConverted(): bool
    {
        return ! $this->isCancelled() && ! $this->isConverted();
    }

    /**
     * ملخص نصي مختصر للخدمات المطلوبة (للعرض في القائمة).
     */
    public function servicesSummary(): string
    {
        $locale = app()->getLocale();
        $names = [];

        foreach ((array) ($this->services_json ?? []) as $item) {
            if (($item['name'] ?? null) === 'other') {
                $names[] = __('Other service');

                continue;
            }

            $names[] = $locale === 'ar'
                ? ($item['name_ar'] ?? '')
                : ($item['name_en'] ?? $item['name_ar'] ?? '');
        }

        return implode('، ', array_filter($names));
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ServiceRequestImage::class);
    }

    /**
     * الطلب الرسمي الذي تحوّل إليه هذا الطلب الخارجي (إن وجد).
     */
    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }
}
