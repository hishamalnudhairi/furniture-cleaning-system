<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'unit',
        'quantity',
        'min_quantity',
        'cost_price',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'min_quantity' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * حالات المخزون.
     */
    public const STATE_OK = 'ok';
    public const STATE_LOW = 'low';
    public const STATE_OUT = 'out';

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * يحسب حالة المخزون تلقائيًا من الكمية وحد التنبيه.
     */
    public function stockState(): string
    {
        if ((float) $this->quantity <= 0) {
            return self::STATE_OUT;
        }

        if ((float) $this->quantity <= (float) $this->min_quantity) {
            return self::STATE_LOW;
        }

        return self::STATE_OK;
    }
}
