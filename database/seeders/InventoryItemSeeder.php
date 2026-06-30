<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'شامبو تنظيف', 'unit' => 'لتر', 'quantity' => 20, 'min_quantity' => 5],
            ['name' => 'منظف معطر', 'unit' => 'لتر', 'quantity' => 15, 'min_quantity' => 5],
            ['name' => 'مادة تعقيم', 'unit' => 'لتر', 'quantity' => 10, 'min_quantity' => 3],
            ['name' => 'أكياس تغليف', 'unit' => 'كيس', 'quantity' => 100, 'min_quantity' => 20],
            ['name' => 'قفازات', 'unit' => 'عبوة', 'quantity' => 30, 'min_quantity' => 10],
        ];

        foreach ($items as $item) {
            InventoryItem::updateOrCreate(
                ['name' => $item['name']],
                [
                    'unit' => $item['unit'],
                    'quantity' => $item['quantity'],
                    'min_quantity' => $item['min_quantity'],
                    'is_active' => true,
                ]
            );
        }
    }
}
