<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // بيانات المحل
            'shop_name_ar' => ['nullable', 'string', 'max:150'],
            'shop_name_en' => ['nullable', 'string', 'max:150'],
            'short_description_ar' => ['nullable', 'string', 'max:1000'],
            'short_description_en' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'wilaya' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:100'],
            'address_ar' => ['nullable', 'string', 'max:500'],
            'address_en' => ['nullable', 'string', 'max:500'],
            'shop_location_url' => ['nullable', 'string', 'max:500'],
            'commercial_registration_number' => ['nullable', 'string', 'max:100'],
            'activity_number' => ['nullable', 'string', 'max:100'],
            'vat_number' => ['nullable', 'string', 'max:100'],

            // أوقات العمل
            'working_days' => ['nullable', 'string', 'max:150'],
            'day_off' => ['nullable', 'string', 'max:150'],
            'opening_time' => ['nullable', 'string', 'max:20'],
            'closing_time' => ['nullable', 'string', 'max:20'],
            'morning_period_label_ar' => ['nullable', 'string', 'max:50'],
            'morning_period_label_en' => ['nullable', 'string', 'max:50'],
            'afternoon_period_label_ar' => ['nullable', 'string', 'max:50'],
            'afternoon_period_label_en' => ['nullable', 'string', 'max:50'],
            'evening_period_label_ar' => ['nullable', 'string', 'max:50'],
            'evening_period_label_en' => ['nullable', 'string', 'max:50'],
            'out_of_hours_message_ar' => ['nullable', 'string', 'max:500'],
            'out_of_hours_message_en' => ['nullable', 'string', 'max:500'],

            // الهوية البصرية
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'primary_color' => ['nullable', 'string', 'max:30'],
            'secondary_color' => ['nullable', 'string', 'max:30'],
            'button_color' => ['nullable', 'string', 'max:30'],
            'background_color' => ['nullable', 'string', 'max:30'],
            'default_font' => ['nullable', 'string', 'max:100'],

            // الضريبة
            'tax_name' => ['nullable', 'string', 'max:100'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],

            // الفاتورة والطباعة
            'invoice_prefix' => ['nullable', 'string', 'max:20'],
            'request_prefix' => ['nullable', 'string', 'max:20'],
            'invoice_footer_ar' => ['nullable', 'string', 'max:1000'],
            'invoice_footer_en' => ['nullable', 'string', 'max:1000'],
            'invoice_paper_type' => ['nullable', 'in:a4,thermal'],
            'thermal_paper_width' => ['nullable', 'in:58,80'],
            'invoice_language_mode' => ['nullable', 'in:ar,en,customer_language,system_language'],

            // صفحة طلب العميل
            'max_image_count' => ['nullable', 'integer', 'min:1', 'max:20'],
            'max_image_size_mb' => ['nullable', 'numeric', 'min:0.1', 'max:20'],
            'public_success_message_ar' => ['nullable', 'string', 'max:500'],
            'public_success_message_en' => ['nullable', 'string', 'max:500'],

            // السائقون
            'default_driver_payment_type' => ['nullable', 'in:per_task,per_day'],
            'default_delivery_fee' => ['nullable', 'numeric', 'min:0'],

            // المخزون
            'default_low_stock_quantity' => ['nullable', 'numeric', 'min:0'],

            // اللغة
            'default_language' => ['nullable', 'in:ar,en'],
            'public_page_default_language' => ['nullable', 'in:ar,en'],
            'invoice_default_language' => ['nullable', 'in:ar,en'],

            // عام
            'currency_code' => ['nullable', 'string', 'max:10'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:60'],
            'date_format' => ['nullable', 'string', 'max:30'],
            'decimal_places' => ['nullable', 'integer', 'min:0', 'max:4'],
        ];
    }
}
