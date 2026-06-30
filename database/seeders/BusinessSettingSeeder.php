<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use Illuminate\Database\Seeder;

class BusinessSettingSeeder extends Seeder
{
    public function run(): void
    {
        // صف إعدادات وحيد بالقيم الافتراضية
        BusinessSetting::firstOrCreate([], [
            'project_name' => 'نظام مغسلة الكنب والزوالي والسجاد',
            'business_name' => 'مغسلة الكنب والسجاد',
            'business_phone' => '0500000000',
            'working_hours' => [
                'sat_thu' => '09:00 - 22:00',
                'fri' => '16:00 - 22:00',
            ],
            'primary_color' => '#0d9488',
            'secondary_color' => '#0f172a',
            'accent_color' => '#f59e0b',
            'tax_enabled' => true,
            'tax_percentage' => 15.00,
            'invoice_prefix' => 'INV-',
            'invoice_next_number' => 1,
            'invoice_show_logo' => true,
            'invoice_show_qr' => true,
            'thermal_enabled' => true,
            'thermal_paper_width' => 80,
            'thermal_font_size' => 12,
            'default_locale' => 'ar',
            'available_locales' => ['ar', 'en'],
            'driver_default_commission_type' => 'fixed',
            'driver_default_commission_value' => 10.00,
            'inventory_enabled' => true,
            'inventory_low_stock_threshold' => 5,
        ]);
    }
}
