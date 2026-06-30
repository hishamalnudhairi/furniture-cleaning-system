<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['ar' => 'تنظيف كنبات', 'en' => 'Sofa Cleaning'],
            ['ar' => 'تنظيف زوالي', 'en' => 'Rug Cleaning'],
            ['ar' => 'تنظيف سجاد', 'en' => 'Carpet Cleaning'],
            ['ar' => 'تنظيف مراتب', 'en' => 'Mattress Cleaning'],
            ['ar' => 'تنظيف كراسي', 'en' => 'Chair Cleaning'],
            ['ar' => 'تنظيف ستائر', 'en' => 'Curtain Cleaning'],
            ['ar' => 'تعقيم', 'en' => 'Sanitization'],
            ['ar' => 'خدمة أخرى', 'en' => 'Other Service'],
        ];

        foreach ($services as $index => $service) {
            Service::updateOrCreate(
                ['name_ar' => $service['ar']],
                [
                    'name_en' => $service['en'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
