<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingsRequest;
use App\Models\ActivityLog;
use App\Models\BusinessSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * الحقول التي اسم الإدخال فيها = اسم العمود مباشرة.
     */
    private const DIRECT_FIELDS = [
        'shop_name_ar', 'shop_name_en', 'short_description_ar', 'short_description_en',
        'whatsapp', 'wilaya', 'area', 'address_ar', 'address_en', 'shop_location_url',
        'commercial_registration_number', 'activity_number', 'vat_number',
        'working_days', 'day_off', 'opening_time', 'closing_time',
        'morning_period_label_ar', 'morning_period_label_en',
        'afternoon_period_label_ar', 'afternoon_period_label_en',
        'evening_period_label_ar', 'evening_period_label_en',
        'out_of_hours_message_ar', 'out_of_hours_message_en',
        'primary_color', 'secondary_color', 'button_color', 'background_color', 'default_font',
        'tax_name', 'tax_percentage',
        'invoice_prefix', 'request_prefix', 'invoice_footer_ar', 'invoice_footer_en',
        'invoice_paper_type', 'thermal_paper_width', 'invoice_language_mode',
        'max_image_count', 'public_success_message_ar', 'public_success_message_en',
        'default_driver_payment_type', 'default_delivery_fee',
        'public_page_default_language', 'invoice_default_language',
        'currency_code', 'currency_symbol', 'timezone', 'date_format', 'decimal_places',
    ];

    /**
     * الحقول المنطقية (checkbox).
     */
    private const BOOLEAN_FIELDS = [
        'show_logo_on_public_page', 'show_banner_on_public_page',
        'tax_enabled', 'prices_include_tax', 'show_tax_on_invoice',
        'invoice_show_qr', 'invoice_show_logo', 'print_auto_open',
        'public_request_enabled', 'require_customer_map_location', 'allow_customer_image_uploads',
        'show_working_hours_on_public_page', 'show_shop_contact_on_public_page',
        'count_pickup_as_delivery', 'count_delivery_as_delivery', 'allow_driver_payments',
        'inventory_enabled', 'low_stock_alerts', 'allow_manual_inventory_adjustment',
        'english_enabled',
    ];

    public function edit(): View
    {
        return view('admin.settings.edit', ['settings' => BusinessSetting::current()]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        $settings = BusinessSetting::current();
        $validated = $request->validated();
        $data = [];

        // الحقول المباشرة
        foreach (self::DIRECT_FIELDS as $field) {
            if (array_key_exists($field, $validated)) {
                $data[$field] = $validated[$field];
            }
        }

        // حقول بأسماء مختلفة عن الأعمدة
        $data['business_phone'] = $validated['phone'] ?? $settings->business_phone;
        $data['business_email'] = $validated['email'] ?? $settings->business_email;
        $data['default_locale'] = $validated['default_language'] ?? $settings->default_locale;
        $data['inventory_low_stock_threshold'] = $validated['default_low_stock_quantity'] ?? $settings->inventory_low_stock_threshold;

        if (isset($validated['max_image_size_mb'])) {
            $data['max_image_size_kb'] = (int) round((float) $validated['max_image_size_mb'] * 1024);
        }

        // الحقول المنطقية (غير المؤشّرة = false)
        foreach (self::BOOLEAN_FIELDS as $field) {
            $data[$field] = $request->boolean($field);
        }

        // رفع الشعار والبوستر (اختياري)
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('settings', 'public');
        }
        if ($request->hasFile('banner')) {
            $data['banner_path'] = $request->file('banner')->store('settings', 'public');
        }

        $settings->update($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'settings.updated',
            'subject_type' => BusinessSetting::class,
            'subject_id' => $settings->getKey(),
            'description' => __('Settings updated'),
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('admin.settings.edit')->with('success', __('Settings saved successfully.'));
    }
}
